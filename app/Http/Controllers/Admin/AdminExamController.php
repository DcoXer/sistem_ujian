<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\ExamContentCacheService;
use App\Services\ExamLifecycleService;
use App\Services\SecurityAuditService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminExamController extends Controller
{
    public function __construct(
        protected ExamLifecycleService $examLifecycleService,
        protected ExamContentCacheService $examContentCacheService,
        protected SecurityAuditService $securityAuditService
    ) {}

    public function index(): View
    {
        return view('admin.exams.index');
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.exams.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Exam::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'authoring_start_at' => ['required', 'date'],
            'authoring_end_at' => ['required', 'date', 'after_or_equal:authoring_start_at', 'before_or_equal:start_at'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'teacher_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', User::ROLE_TEACHER)),
            ],
            'author_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', User::ROLE_TEACHER)),
            ],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
            'target_grade_level' => ['nullable', 'integer', 'min:1', 'max:12'],
            'school_year_id' => ['nullable', 'integer', Rule::exists('school_years', 'id')],
        ]);

        $data['teacher_id'] = (int) ($data['teacher_id'] ?? $data['author_id'] ?? 0) ?: null;
        unset($data['author_id']);
        $data['class_id'] = null;

        if (empty($data['school_year_id'])) {
            $data['school_year_id'] = SchoolYear::query()->where('is_active', true)->value('id');
        }

        if (! $data['teacher_id']) {
            return back()->withErrors(['teacher_id' => 'Teacher wajib dipilih.'])->withInput();
        }

        $exam = Exam::create($data + [
            'created_by' => $request->user()->id,
            'status' => Exam::STATUS_DRAFT,
            'author_id' => $data['teacher_id'],
        ]);

        $this->securityAuditService->log($request, 'exam_created', $exam, [
            'title' => $exam->title,
            'teacher_id' => $exam->teacher_id,
            'subject_id' => $exam->subject_id,
            'class_id' => $exam->class_id,
            'target_grade_level' => $exam->target_grade_level,
            'school_year_id' => $exam->school_year_id,
            'start_at' => optional($exam->start_at)?->toIso8601String(),
            'end_at' => optional($exam->end_at)?->toIso8601String(),
        ]);

        return redirect()->route('admin.exams.show', $exam)->with('status', 'exam-created');
    }

    public function show(Exam $exam): View
    {
        $exam->load([
            'teacher:id,name,email',
            'subject:id,code,name',
            'schoolClass:id,name',
            'schoolYear:id,name',
            'questions.options',
            'attempts.user',
        ]);
        $questionsCount = $exam->questions->count();

        $lifecycle = [
            'created' => true,
            'published' => $exam->status !== Exam::STATUS_DRAFT,
            'has_attempt' => $exam->attempts->isNotEmpty(),
            'locked_for_edit' => $exam->status !== Exam::STATUS_DRAFT,
            'authoring_closed' => $exam->isAuthoringWindowClosed(),
            'can_publish' => $exam->status === Exam::STATUS_DRAFT
                && $questionsCount > 0
                && $exam->isAuthoringWindowClosed(),
        ];

        $auditReport = null;
        if ($exam->status === Exam::STATUS_FINISHED && Schema::hasTable('exam_attempt_audits')) {
            $attempts = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->with([
                    'user:id,name,email',
                    'audits' => fn ($query) => $query->with('actor:id,name,role')->latest(),
                ])
                ->get();

            $timeline = $attempts
                ->flatMap(function (ExamAttempt $attempt) {
                    return $attempt->audits->map(function ($audit) use ($attempt) {
                        return [
                            'action' => $audit->action,
                            'reason' => $audit->reason,
                            'created_at' => $audit->created_at,
                            'actor_name' => $audit->actor?->name ?? 'System',
                            'actor_role' => $audit->actor?->role ?? '-',
                            'participant_name' => $attempt->user?->name ?? ('Peserta #'.$attempt->user_id),
                            'participant_email' => $attempt->user?->email ?? '-',
                            'meta' => $audit->meta ?? [],
                        ];
                    });
                })
                ->sortByDesc('created_at')
                ->values();

            $auditReport = [
                'total_attempts' => $attempts->count(),
                'force_submit_count' => $timeline->where('action', 'force_submit')->count(),
                'reopen_count' => $timeline->where('action', 'reopen_attempt')->count(),
                'manual_score_count' => $timeline->where('action', 'manual_score')->count(),
                'issue_count' => $timeline->where('action', 'mark_issue')->count(),
                'timeline' => $timeline,
            ];
        }

        return view('admin.exams.show', compact('exam', 'lifecycle', 'questionsCount', 'auditReport'));
    }

    public function publish(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorize('publish', $exam);

        try {
            $this->examLifecycleService->publishDraftExam($exam);
        } catch (AuthorizationException $exception) {
            return back()->withErrors(['publish' => $exception->getMessage()]);
        }
        $this->examContentCacheService->invalidateExamContent((int) $exam->id);

        $this->securityAuditService->log($request, 'exam_published', $exam, [
            'status' => $exam->fresh()?->status,
        ]);

        return back()->with('status', 'exam-published');
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorize('update-meta', $exam);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'authoring_start_at' => ['required', 'date'],
            'authoring_end_at' => ['required', 'date', 'after_or_equal:authoring_start_at', 'before_or_equal:start_at'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'semester_id' => ['nullable', 'integer', Rule::exists('semesters', 'id')],
            'exam_type' => ['nullable', Rule::in(Exam::EXAM_TYPES)],
        ]);

        $exam->update($data);

        $this->securityAuditService->log($request, 'exam_updated', $exam, [
            'title' => $exam->title,
        ]);

        return back()->with('status', 'exam-updated');
    }

    public function bulkCreate(Request $request): RedirectResponse
    {
        $this->authorize('create', Exam::class);

        $data = $request->validate([
            'semester_id' => ['required', 'integer', Rule::exists('semesters', 'id')],
            'exam_type' => ['required', Rule::in(Exam::EXAM_TYPES)],
            'target_grade_level' => ['required', 'integer', 'min:1', 'max:12'],
            'authoring_start_at' => ['required', 'date'],
            'authoring_end_at' => ['required', 'date', 'after_or_equal:authoring_start_at'],
            'start_at' => ['required', 'date', 'after:authoring_end_at'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
        ]);

        $gradeLevel = (int) $data['target_grade_level'];
        $hasAssignments = \App\Models\TeacherSubject::query()
            ->where('grade_level', $gradeLevel)
            ->whereNull('class_id')
            ->exists();

        if (! $hasAssignments) {
            return back()->withErrors([
                'bulk_create' => 'Tidak ada assignment guru ditemukan untuk tingkat kelas '.$gradeLevel.'. Pastikan assignment guru sudah diset per tingkat kelas.',
            ])->withInput();
        }

        $result = $this->examLifecycleService->bulkCreateFromAssignments($data, $request, $this->securityAuditService);
        $created = $result['created'];
        $skipped = $result['skipped'];

        $message = "Berhasil membuat {$created} ujian.";
        if ($skipped > 0) {
            $message .= " {$skipped} ujian dilewati karena sudah ada.";
        }

        return redirect()->route('admin.exams.index')->with('status', $message);
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorize('delete', $exam);

        if ($exam->status !== Exam::STATUS_DRAFT) {
            return back()->withErrors(['delete' => 'Exam hanya bisa dihapus saat status draft.']);
        }

        $examSnapshot = [
            'title' => $exam->title,
            'status' => $exam->status,
        ];
        $this->examContentCacheService->invalidateExamContent((int) $exam->id);
        $exam->delete();

        $this->securityAuditService->log($request, 'exam_deleted', null, $examSnapshot);

        return redirect()->route('admin.exams.index')->with('status', 'exam-deleted');
    }
}
