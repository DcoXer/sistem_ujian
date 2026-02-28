<?php

namespace App\Services;

use App\Exceptions\OptimisticLockException;
use App\Exceptions\StateConflictException;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ExamParticipationService
{
    /**
     * Participant attempt orchestration service.
     * Dependency direction:
     * - may depend on scoring primitives.
     * - must not depend on operator/lifecycle orchestration services.
     */
    public function __construct(
        protected ExamScoringService $scoringService
    ) {
    }

    /**
     * @throws AuthorizationException
     */
    public function startExam(User $user, Exam $exam): ExamAttempt
    {
        Gate::forUser($user)->authorize('start', $exam);

        return DB::transaction(function () use ($user, $exam) {
            $attempt = ExamAttempt::lockForUpdate()
                ->where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->first();

            if ($attempt && in_array($attempt->status, [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED], true)) {
                throw new StateConflictException('Ujian sudah pernah disubmit.');
            }

            if ($attempt) {
                return $attempt;
            }

            return ExamAttempt::create([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
                'status' => ExamAttempt::STATUS_ACTIVE,
                'started_at' => now(),
            ]);
        });
    }

    public function saveAnswer(
        User $user,
        ExamAttempt $attempt,
        int $questionId,
        ?int $optionId,
        ?string $answerText = null,
        ?int $expectedVersion = null
    ): ExamAnswer
    {
        $this->assertEditableAttempt($user, $attempt);
        $question = $this->resolveQuestionForAttempt($attempt, $questionId);
        $validatedOptionId = $this->resolveOptionForQuestion($question->id, $optionId);
        $version = max(0, (int) ($expectedVersion ?? 0));

        $affected = ExamAnswer::query()
            ->where('exam_attempt_id', $attempt->id)
            ->where('exam_question_id', $question->id)
            ->where('lock_version', $version)
            ->update([
                'exam_option_id' => $validatedOptionId,
                'answer_text' => $answerText,
                'locked_at' => null,
                'lock_version' => $version + 1,
                'updated_at' => now(),
            ]);

        if ($affected === 1) {
            return ExamAnswer::query()
                ->where('exam_attempt_id', $attempt->id)
                ->where('exam_question_id', $question->id)
                ->firstOrFail();
        }

        if ($version === 0) {
            try {
                return ExamAnswer::create([
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $question->id,
                    'exam_option_id' => $validatedOptionId,
                    'answer_text' => $answerText,
                    'locked_at' => null,
                    'lock_version' => 1,
                ]);
            } catch (QueryException $exception) {
                $latest = ExamAnswer::query()
                    ->where('exam_attempt_id', $attempt->id)
                    ->where('exam_question_id', $question->id)
                    ->first();

                if ($latest) {
                    throw new OptimisticLockException(
                        currentVersion: (int) $latest->lock_version,
                        currentOptionId: $latest->exam_option_id ? (int) $latest->exam_option_id : null
                    );
                }

                throw $exception;
            }
        }

        $latest = ExamAnswer::query()
            ->where('exam_attempt_id', $attempt->id)
            ->where('exam_question_id', $question->id)
            ->first();

        throw new OptimisticLockException(
            currentVersion: $latest ? (int) $latest->lock_version : null,
            currentOptionId: $latest?->exam_option_id ? (int) $latest->exam_option_id : null
        );
    }

    public function submitExam(User $user, ExamAttempt $attempt): ExamAttempt
    {
        $this->assertEditableAttempt($user, $attempt);

        $submitted = DB::transaction(function () use ($attempt) {
            $attempt = ExamAttempt::lockForUpdate()->findOrFail($attempt->id);

            if (! $attempt->canTransitionTo(ExamAttempt::STATUS_SUBMITTED) || $attempt->submitted_at !== null) {
                throw new StateConflictException('Ujian sudah disubmit.');
            }

            $attempt->update([
                'status' => ExamAttempt::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'answers_locked_at' => now(),
            ]);

            ExamAnswer::where('exam_attempt_id', $attempt->id)
                ->update(['locked_at' => now()]);

            return $attempt;
        });

        return $this->scoringService->scoreAttempt($submitted);
    }

    public function expireAttempt(ExamAttempt $attempt): ExamAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $attempt = ExamAttempt::with('exam')
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            if (in_array($attempt->status, [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED], true)) {
                return $attempt;
            }

            if ($attempt->status !== ExamAttempt::STATUS_ACTIVE) {
                return $attempt;
            }

            if (! $this->isAttemptExpired($attempt)) {
                return $attempt;
            }

            $attempt->update([
                'status' => ExamAttempt::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'answers_locked_at' => now(),
            ]);

            ExamAnswer::where('exam_attempt_id', $attempt->id)
                ->update(['locked_at' => now()]);

            return $this->scoringService->scoreAttempt($attempt);
        });
    }

    public function isAttemptExpired(ExamAttempt $attempt): bool
    {
        return $this->getAttemptExpirationReason($attempt) !== null;
    }

    public function getAttemptExpirationReason(ExamAttempt $attempt): ?string
    {
        $attempt->loadMissing('exam');
        $exam = $attempt->exam;
        if (! $exam) {
            return 'missing_exam';
        }

        $now = now();
        if ($exam->end_at && $now->greaterThan($exam->end_at)) {
            return 'exam_window_ended';
        }

        if (! $attempt->started_at) {
            return 'invalid_started_at';
        }

        $startedAt = $attempt->started_at;
        $deadline = $startedAt->copy()->addMinutes((int) $exam->duration_minutes);
        if ($now->greaterThan($deadline)) {
            return 'duration_elapsed';
        }

        $idleTimeout = (int) config('security.attempt_idle_timeout_minutes', 0);
        if (
            $idleTimeout > 0
            && $attempt->updated_at
            && $attempt->updated_at->lte($now->copy()->subMinutes($idleTimeout))
        ) {
            return 'idle_timeout';
        }

        return null;
    }

    public function resolvePesertaDashboardState(User $user, ?Exam $activeExam): array
    {
        $activeExamExpiredMessage = null;
        $myInProgressAttempt = null;
        $myLatestSubmittedAttempt = null;

        if ($activeExam) {
            $activeAttempt = ExamAttempt::query()
                ->where('exam_id', $activeExam->id)
                ->where('user_id', $user->id)
                ->first();

            if ($activeAttempt && $this->isAttemptExpired($activeAttempt)) {
                $this->expireAttempt($activeAttempt);
                $activeExamExpiredMessage = 'Ujian berikutnya akan dimulai dalam waktu dekat.';
                $activeExam = null;
            }
        }

        $myInProgressAttempt = ExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('status', ExamAttempt::STATUS_ACTIVE)
            ->latest('started_at')
            ->first();

        if ($myInProgressAttempt && $this->isAttemptExpired($myInProgressAttempt)) {
            $myInProgressAttempt = $this->expireAttempt($myInProgressAttempt);
            $myLatestSubmittedAttempt = ExamAttempt::query()
                ->where('user_id', $user->id)
                ->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED])
                ->whereHas('exam', fn ($query) => $query->where('status', Exam::STATUS_FINISHED))
                ->latest('submitted_at')
                ->first();
            $myInProgressAttempt = null;
        } else {
            $myLatestSubmittedAttempt = ExamAttempt::query()
                ->where('user_id', $user->id)
                ->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED])
                ->whereHas('exam', fn ($query) => $query->where('status', Exam::STATUS_FINISHED))
                ->latest('submitted_at')
                ->first();
        }

        return [
            'activeExam' => $activeExam,
            'activeExamExpiredMessage' => $activeExamExpiredMessage,
            'myInProgressAttempt' => $myInProgressAttempt,
            'myLatestSubmittedAttempt' => $myLatestSubmittedAttempt,
        ];
    }

    protected function assertEditableAttempt(User $user, ExamAttempt $attempt): void
    {
        if ((int) $attempt->user_id !== (int) $user->id || $user->role !== User::ROLE_PESERTA) {
            throw new AuthorizationException('Akses ditolak.');
        }

        if ($attempt->status !== ExamAttempt::STATUS_ACTIVE || $attempt->answers_locked_at !== null) {
            throw new StateConflictException('Jawaban sudah terkunci.');
        }

        $attempt->loadMissing('exam');
        if ($attempt->exam->status !== Exam::STATUS_RUNNING) {
            throw new StateConflictException('Ujian belum tersedia.');
        }

        if ($this->isAttemptExpired($attempt)) {
            throw new StateConflictException('Waktu pengerjaan kamu sudah habis.');
        }
    }

    protected function resolveQuestionForAttempt(ExamAttempt $attempt, int $questionId): ExamQuestion
    {
        $question = ExamQuestion::query()
            ->whereKey($questionId)
            ->where('exam_id', $attempt->exam_id)
            ->first();

        if (! $question) {
            throw new StateConflictException('Soal tidak valid untuk ujian ini.');
        }

        return $question;
    }

    protected function resolveOptionForQuestion(int $questionId, ?int $optionId): ?int
    {
        if ($optionId === null) {
            return null;
        }

        $isValid = ExamOption::query()
            ->whereKey($optionId)
            ->where('exam_question_id', $questionId)
            ->exists();

        if (! $isValid) {
            throw new StateConflictException('Opsi jawaban tidak valid untuk soal ini.');
        }

        return $optionId;
    }
}
