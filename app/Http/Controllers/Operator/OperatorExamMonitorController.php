<?php

namespace App\Http\Controllers\Operator;

use App\Exceptions\StateConflictException;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamLifecycleService;
use App\Services\OperatorExamService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OperatorExamMonitorController extends Controller
{
    public function __construct(
        protected ExamLifecycleService $examLifecycleService,
        protected OperatorExamService $operatorExamService
    ) {
    }

    public function index(): View
    {
        Gate::authorize('viewResults', Exam::class);
        return view('operator.exams.index');
    }

    public function show(Exam $exam): View
    {
        Gate::authorize('viewResults', Exam::class);

        $attempts = ExamAttempt::with(['user', 'audits' => fn ($q) => $q->latest()->limit(1)])
            ->where('exam_id', $exam->id)
            ->latest('started_at')
            ->get();

        $now = now();
        $attempts = $attempts->map(function (ExamAttempt $attempt) use ($exam, $now) {
            $deadlineByDuration = optional($attempt->started_at)->copy()?->addMinutes((int) $exam->duration_minutes);
            $deadline = $deadlineByDuration && $exam->end_at
                ? ($deadlineByDuration->lt($exam->end_at) ? $deadlineByDuration : $exam->end_at)
                : ($deadlineByDuration ?? $exam->end_at);

            $attempt->remaining_seconds = $attempt->status === ExamAttempt::STATUS_ACTIVE && $deadline
                ? max(0, $now->diffInSeconds($deadline, false))
                : 0;

            $attempt->is_online = $attempt->status === ExamAttempt::STATUS_ACTIVE
                && $attempt->updated_at
                && $attempt->updated_at->gte($now->copy()->subMinutes(2));

            return $attempt;
        });

        return view('operator.exams.show', compact('exam', 'attempts'));
    }

    public function manualScore(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:8', 'max:500'],
        ]);

        try {
            $this->operatorExamService->manualScore(
                $request->user(),
                $attempt,
                (int) $data['score'],
                $data['reason'],
                [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );
        } catch (StateConflictException $exception) {
            abort(409, $exception->getMessage());
        } catch (AuthorizationException $exception) {
            return back()->withErrors(['manual_score' => $exception->getMessage()]);
        }

        return back()->with('status', 'manual-score-updated');
    }

    public function forceSubmit(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:8', 'max:500'],
        ]);

        try {
            $this->operatorExamService->forceSubmit($request->user(), $attempt, $data['reason'], [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (StateConflictException $exception) {
            abort(409, $exception->getMessage());
        } catch (AuthorizationException $exception) {
            return back()->withErrors(['force_submit' => $exception->getMessage()]);
        }

        return back()->with('status', 'attempt-force-submitted');
    }

    public function reopen(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:8', 'max:500'],
        ]);

        try {
            $this->operatorExamService->reopenAttempt($request->user(), $attempt, $data['reason'], [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (StateConflictException $exception) {
            abort(409, $exception->getMessage());
        } catch (AuthorizationException $exception) {
            return back()->withErrors(['reopen_attempt' => $exception->getMessage()]);
        }

        return back()->with('status', 'attempt-reopened');
    }

    public function markIssue(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:8', 'max:500'],
        ]);

        try {
            $this->operatorExamService->markTechnicalIssue($request->user(), $attempt, $data['reason'], [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (StateConflictException $exception) {
            abort(409, $exception->getMessage());
        } catch (AuthorizationException $exception) {
            return back()->withErrors(['mark_issue' => $exception->getMessage()]);
        }

        return back()->with('status', 'attempt-issue-logged');
    }
}
