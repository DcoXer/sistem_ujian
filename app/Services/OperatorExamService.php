<?php

namespace App\Services;

use App\Exceptions\StateConflictException;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAudit;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OperatorExamService
{
    /**
     * Operator attempt orchestration service.
     * Dependency direction:
     * - may depend on scoring primitives.
     * - must not depend on participant/lifecycle orchestration services.
     */
    public function __construct(
        protected ExamScoringService $scoringService
    ) {
    }

    /**
     * @throws AuthorizationException
     */
    public function manualScore(User $actor, ExamAttempt $attempt, int $score, string $reason, array $context = []): ExamAttempt
    {
        Gate::forUser($actor)->authorize('manualScore', $attempt);

        return DB::transaction(function () use ($actor, $attempt, $score, $reason) {
            $attempt = ExamAttempt::query()
                ->with(['exam', 'answers'])
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            $this->assertHasPrivilegedRole($actor);

            if ($attempt->scoring_processed_at !== null) {
                throw new StateConflictException('Skor otomatis tidak boleh diubah manual.');
            }

            if (! $this->attemptHasEssayStyleAnswer($attempt)) {
                throw new StateConflictException('Manual scoring hanya untuk kasus essay/khusus.');
            }

            $previousScore = $attempt->score;

            if ($attempt->status === ExamAttempt::STATUS_SUBMITTED && $attempt->canTransitionTo(ExamAttempt::STATUS_FINISHED)) {
                $attempt->status = ExamAttempt::STATUS_FINISHED;
            }

            $attempt->score = $score;
            $attempt->scoring_processed_at = now();
            $attempt->save();

            $this->writeAudit($attempt, $actor, 'manual_score', $reason, [
                'previous_score' => $previousScore,
                'new_score' => $score,
            ], $context);

            return $attempt->fresh(['exam', 'answers']);
        });
    }

    /**
     * @throws AuthorizationException
     */
    public function forceSubmit(User $actor, ExamAttempt $attempt, string $reason, array $context = []): ExamAttempt
    {
        return DB::transaction(function () use ($actor, $attempt, $reason, $context) {
            $attempt = ExamAttempt::query()
                ->with('exam')
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            $this->assertHasPrivilegedRole($actor);

            if ($attempt->status !== ExamAttempt::STATUS_ACTIVE) {
                throw new StateConflictException('Hanya attempt aktif yang bisa di-force submit.');
            }

            if (! in_array($attempt->exam?->status, [Exam::STATUS_RUNNING, Exam::STATUS_FINISHED], true)) {
                throw new StateConflictException('Exam belum berjalan.');
            }

            $attempt->update([
                'status' => ExamAttempt::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'answers_locked_at' => now(),
            ]);

            ExamAnswer::query()
                ->where('exam_attempt_id', $attempt->id)
                ->update(['locked_at' => now()]);

            $scored = $this->scoringService->scoreAttempt($attempt);

            $this->writeAudit($attempt, $actor, 'force_submit', $reason, [], $context);

            return $scored;
        });
    }

    /**
     * @throws AuthorizationException
     */
    public function reopenAttempt(User $actor, ExamAttempt $attempt, string $reason, array $context = []): ExamAttempt
    {
        return DB::transaction(function () use ($actor, $attempt, $reason, $context) {
            $attempt = ExamAttempt::query()
                ->with('exam')
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            $this->assertHasPrivilegedRole($actor);

            if (! in_array($attempt->status, [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED], true)) {
                throw new StateConflictException('Attempt belum bisa di-reopen.');
            }

            $exam = $attempt->exam;
            if (! $exam || $exam->status !== Exam::STATUS_RUNNING) {
                throw new StateConflictException('Exam tidak dalam status running.');
            }

            $now = now();
            if ($now->lt($exam->start_at) || $now->gt($exam->end_at)) {
                throw new StateConflictException('Re-open hanya boleh saat exam masih aktif.');
            }

            $attempt->update([
                'status' => ExamAttempt::STATUS_ACTIVE,
                'submitted_at' => null,
                'answers_locked_at' => null,
                'scoring_processed_at' => null,
                'score' => null,
            ]);

            ExamAnswer::query()
                ->where('exam_attempt_id', $attempt->id)
                ->update(['locked_at' => null]);

            $this->writeAudit($attempt, $actor, 'reopen_attempt', $reason, [], $context);

            return $attempt->fresh(['exam', 'answers']);
        });
    }

    /**
     * @throws AuthorizationException
     */
    public function markTechnicalIssue(User $actor, ExamAttempt $attempt, string $reason, array $context = []): void
    {
        $attempt->loadMissing('exam');
        $this->assertHasPrivilegedRole($actor);

        $this->writeAudit($attempt, $actor, 'mark_issue', $reason, [
            'attempt_status' => $attempt->status,
            'exam_status' => $attempt->exam?->status,
        ], $context);
    }

    protected function attemptHasEssayStyleAnswer(ExamAttempt $attempt): bool
    {
        foreach ($attempt->answers as $answer) {
            if (is_string($answer->answer_text) && trim($answer->answer_text) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws AuthorizationException
     */
    protected function assertHasPrivilegedRole(User $actor): void
    {
        if (! in_array($actor->role, [User::ROLE_ADMIN, User::ROLE_OPERATOR], true)) {
            throw new AuthorizationException('Aksi ini hanya untuk operator/admin.');
        }
    }

    protected function writeAudit(ExamAttempt $attempt, User $actor, string $action, ?string $reason = null, array $meta = [], array $context = []): void
    {
        ExamAttemptAudit::query()->create([
            'exam_attempt_id' => $attempt->id,
            'actor_user_id' => $actor->id,
            'action' => $action,
            'reason' => $reason,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
            'meta' => $meta ?: null,
        ]);
    }
}
