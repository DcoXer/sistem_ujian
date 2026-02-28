<?php

namespace App\Http\Controllers\Peserta;

use App\Exceptions\StateConflictException;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamParticipationService;
use App\Services\ExamLifecycleService;
use App\Services\SecurityAuditService;
use App\Support\ExamUiAction;
use App\Support\ExamUiState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PesertaExamController extends Controller
{
    public function __construct(
        protected ExamParticipationService $participationService,
        protected ExamLifecycleService $examLifecycleService,
        protected SecurityAuditService $securityAuditService
    ) {
    }

    public function index(Request $request): View
    {
        $this->examLifecycleService->closeExpiredExams();

        $userId = $request->user()->id;

        $exams = Exam::query()
            ->where(function ($query) use ($userId) {
                $query->where('status', Exam::STATUS_RUNNING)
                    ->orWhere(function ($finishedQuery) use ($userId) {
                        $finishedQuery->where('status', Exam::STATUS_FINISHED)
                            ->whereHas('attempts', fn ($attemptQuery) => $attemptQuery->where('user_id', $userId));
                    });
            })
            ->with(['attempts' => fn ($q) => $q->where('user_id', $userId)])
            ->latest('start_at')
            ->get();

        $exams = $this->decorateExamCards($exams);

        return view('peserta.exams.index', compact('exams'));
    }

    public function realtimeState(Request $request): JsonResponse
    {
        $this->examLifecycleService->closeExpiredExams();

        $userId = $request->user()->id;
        $exams = Exam::query()
            ->where(function ($query) use ($userId) {
                $query->where('status', Exam::STATUS_RUNNING)
                    ->orWhere(function ($finishedQuery) use ($userId) {
                        $finishedQuery->where('status', Exam::STATUS_FINISHED)
                            ->whereHas('attempts', fn ($attemptQuery) => $attemptQuery->where('user_id', $userId));
                    });
            })
            ->with(['attempts' => fn ($q) => $q->where('user_id', $userId)])
            ->latest('start_at')
            ->get();

        $exams = $this->decorateExamCards($exams);

        return response()->json([
            'server_now_ms' => now()->getTimestamp() * 1000,
            'exams' => $exams->map(fn (Exam $exam) => [
                'id' => $exam->id,
                'state' => $exam->ui_state,
                'action' => $exam->ui_action,
                'message' => $exam->ui_message,
                'message_tone' => $exam->ui_message_tone,
                'attempt_status' => $exam->my_attempt_status,
            ])->values(),
        ]);
    }

    public function start(Request $request, Exam $exam): RedirectResponse
    {
        try {
            $attempt = $this->participationService->startExam($request->user(), $exam);
        } catch (StateConflictException $exception) {
            abort(409, $exception->getMessage());
        }

        $this->securityAuditService->log($request, 'attempt_started', $attempt, [
            'exam_id' => $exam->id,
        ]);

        return redirect()->route('peserta.exams.show', $attempt);
    }

    public function show(ExamAttempt $attempt): View
    {
        $this->authorize('view', $attempt);
        $attempt->load(['exam.questions.options', 'answers']);

        return view('peserta.exams.show', compact('attempt'));
    }

    public function saveAnswer(Request $request, ExamAttempt $attempt): JsonResponse|Response|RedirectResponse
    {
        $this->authorize('answer', $attempt);

        $data = $request->validate([
            'question_id' => ['required', 'integer', 'min:1'],
            'option_id' => ['nullable', 'integer', 'min:1'],
            'answer_text' => ['nullable', 'string'],
            'answer_version' => ['nullable', 'date'],
        ]);

        try {
            $answer = $this->participationService->saveAnswer(
                $request->user(),
                $attempt,
                (int) $data['question_id'],
                isset($data['option_id']) ? (int) $data['option_id'] : null,
                $data['answer_text'] ?? null,
                $data['answer_version'] ?? null
            );
        } catch (StateConflictException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }

            abort(409, $exception->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'answer-saved',
                'answer_version' => $answer->updated_at?->toIso8601String(),
            ]);
        }

        return back()->with('status', 'answer-saved');
    }

    public function submit(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $this->authorize('view', $attempt);

        try {
            $attempt = $this->participationService->submitExam($request->user(), $attempt);
        } catch (StateConflictException $exception) {
            abort(409, $exception->getMessage());
        }

        $this->securityAuditService->log($request, 'attempt_submitted', $attempt, [
            'exam_id' => $attempt->exam_id,
            'status' => $attempt->status,
        ]);

        $attempt->loadMissing('exam');
        if ($attempt->exam?->status !== Exam::STATUS_FINISHED) {
            return redirect()->route('peserta.exams.index')->with('status', 'exam-submitted');
        }

        return redirect()->route('peserta.exams.result', $attempt)->with('status', 'exam-submitted');
    }

    public function result(ExamAttempt $attempt): View|RedirectResponse
    {
        $this->authorize('viewResult', $attempt);
        $attempt->load('exam');

        if (
            request()->user()?->role === User::ROLE_PESERTA
            && $attempt->exam?->status !== Exam::STATUS_FINISHED
        ) {
            return redirect()
                ->route('peserta.exams.index')
                ->with('error', 'Hasil belum tersedia. Tunggu sampai ujian selesai.');
        }

        return view('peserta.exams.result', compact('attempt'));
    }

    public function timer(ExamAttempt $attempt): JsonResponse
    {
        $this->authorize('view', $attempt);

        $attempt->loadMissing('exam');

        $duration = (int) $attempt->exam->duration_minutes * 60;
        if (! $attempt->started_at) {
            return response()->json([
                'message' => 'Attempt belum memiliki started_at yang valid.',
                'status' => $attempt->status,
            ], 409);
        }
        $startedAt = $attempt->started_at;
        $deadline = $startedAt->copy()->addSeconds($duration);
        $remaining = (int) max(0, floor(now()->diffInSeconds($deadline, false)));

        return response()->json([
            'remaining_seconds' => $attempt->status === ExamAttempt::STATUS_ACTIVE ? $remaining : 0,
            'status' => $attempt->status,
            'expired' => $remaining <= 0 || $attempt->status !== ExamAttempt::STATUS_ACTIVE,
            'deadline_at' => $deadline->toIso8601String(),
        ]);
    }

    protected function decorateExamCards(Collection $exams): Collection
    {
        return $exams->map(function (Exam $exam) {
            $attempt = $exam->attempts->first();

            $exam->my_attempt_status = $attempt?->status;
            $exam->my_attempt_id = $attempt?->id;

            $uiState = $this->resolveExamUiState($exam, $attempt);
            $exam->ui_state = $uiState['state'];
            $exam->ui_action = $uiState['action'];
            $exam->ui_message = $uiState['message'];
            $exam->ui_message_tone = $uiState['message_tone'];

            return $exam;
        });
    }

    protected function resolveExamUiState(Exam $exam, ?ExamAttempt $attempt): array
    {
        $now = now();
        $state = $exam->status === Exam::STATUS_FINISHED ? ExamUiState::FINISHED : ExamUiState::RUNNING;
        if ($exam->status === Exam::STATUS_RUNNING && $exam->start_at && $now->lt($exam->start_at)) {
            $state = ExamUiState::NOT_STARTED;
        }

        if (in_array($attempt?->status, [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED], true)) {
            if ($exam->status === Exam::STATUS_FINISHED) {
                return [
                    'state' => $state,
                    'action' => ExamUiAction::RESULT,
                    'message' => '',
                    'message_tone' => '',
                ];
            }

            return [
                'state' => $state,
                'action' => ExamUiAction::WAITING_RESULT,
                'message' => 'Jawaban sudah terkirim. Hasil akan tampil setelah ujian selesai.',
                'message_tone' => 'amber',
            ];
        }

        if ($attempt?->status === ExamAttempt::STATUS_ACTIVE) {
            if ($exam->status === Exam::STATUS_RUNNING) {
                return [
                    'state' => $state,
                    'action' => ExamUiAction::CONTINUE_ENABLED,
                    'message' => '',
                    'message_tone' => '',
                ];
            }

            return [
                'state' => $state,
                'action' => ExamUiAction::CONTINUE_DISABLED,
                'message' => 'Waktu ujian sudah berakhir pada '.$exam->end_at?->format('d M Y H:i').'.',
                'message_tone' => 'rose',
            ];
        }

        if ($state === ExamUiState::NOT_STARTED) {
            return [
                'state' => $state,
                'action' => ExamUiAction::START_DISABLED,
                'message' => 'Ujian baru bisa dimulai pada '.$exam->start_at?->format('d M Y H:i').'.',
                'message_tone' => 'amber',
            ];
        }

        if ($exam->status === Exam::STATUS_FINISHED) {
            return [
                'state' => $state,
                'action' => ExamUiAction::START_DISABLED,
                'message' => 'Waktu ujian sudah berakhir pada '.$exam->end_at?->format('d M Y H:i').'.',
                'message_tone' => 'rose',
            ];
        }

        return [
            'state' => $state,
            'action' => ExamUiAction::START_ENABLED,
            'message' => '',
            'message_tone' => '',
        ];
    }
}
