<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithCrudNotifications;
use App\Models\Exam;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamLifecycleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ExamsIndexTable extends Component
{
    use WithCrudNotifications;
    use WithPagination;

    public string $search = '';

    public string $filterSemesterId = '';

    public string $filterExamType = '';

    // Single exam create modal
    public bool $showCreateModal = false;

    public string $title = '';

    public string $start_at = '';

    public string $end_at = '';

    public string $authoring_start_at = '';

    public string $authoring_end_at = '';

    public int|string $duration_minutes = 60;

    public string $teacher_id = '';

    public string $subject_id = '';

    public string $target_grade_level = '';

    public string $school_year_id = '';

    public string $semester_id = '';

    public string $exam_type = '';

    // Bulk create modal
    public bool $showBulkCreateModal = false;

    public string $bulk_semester_id = '';

    public string $bulk_exam_type = Exam::TYPE_UTS;

    public string $bulk_grade_level = '';

    public string $bulk_authoring_start_at = '';

    public string $bulk_authoring_end_at = '';

    public string $bulk_start_at = '';

    public string $bulk_end_at = '';

    public int|string $bulk_duration_minutes = 90;

    public function mount(): void
    {
        $activeSchoolYearId = (string) (SchoolYear::query()->where('is_active', true)->value('id') ?? '');
        $this->school_year_id = $activeSchoolYearId;

        $activeSemesterId = (string) (Semester::query()->where('is_active', true)->value('id') ?? '');
        $this->semester_id = $activeSemesterId;
        $this->bulk_semester_id = $activeSemesterId;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSemesterId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterExamType(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
        $this->reset([
            'title', 'start_at', 'end_at', 'authoring_start_at', 'authoring_end_at', 'duration_minutes',
            'teacher_id', 'subject_id', 'target_grade_level', 'exam_type',
        ]);
        $this->duration_minutes = 60;
        $this->school_year_id = (string) (SchoolYear::query()->where('is_active', true)->value('id') ?? '');
        $this->semester_id = (string) (Semester::query()->where('is_active', true)->value('id') ?? '');
    }

    public function openBulkCreateModal(): void
    {
        $this->showBulkCreateModal = true;
    }

    public function closeBulkCreateModal(): void
    {
        $this->showBulkCreateModal = false;
        $this->resetValidation();
        $this->reset([
            'bulk_grade_level', 'bulk_authoring_start_at', 'bulk_authoring_end_at',
            'bulk_start_at', 'bulk_end_at',
        ]);
        $this->bulk_exam_type = Exam::TYPE_UTS;
        $this->bulk_duration_minutes = 90;
        $this->bulk_semester_id = (string) (Semester::query()->where('is_active', true)->value('id') ?? '');
    }

    public function createExam(): void
    {
        $data = $this->validateWithFriendlyMessage([
            'title' => ['required', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'authoring_start_at' => ['required', 'date'],
            'authoring_end_at' => ['required', 'date', 'after_or_equal:authoring_start_at', 'before_or_equal:start_at'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'teacher_id' => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', User::ROLE_TEACHER))],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'target_grade_level' => ['nullable', 'integer', 'min:1', 'max:12'],
            'school_year_id' => ['nullable', 'integer', Rule::exists('school_years', 'id')],
            'semester_id' => ['nullable', 'integer', Rule::exists('semesters', 'id')],
            'exam_type' => ['nullable', Rule::in(Exam::EXAM_TYPES)],
        ]);

        if (empty($data['school_year_id'])) {
            $data['school_year_id'] = SchoolYear::query()->where('is_active', true)->value('id');
        }

        Exam::query()->create($data + [
            'created_by' => auth()->id(),
            'status' => Exam::STATUS_DRAFT,
            'teacher_id' => (int) $data['teacher_id'],
            'author_id' => (int) $data['teacher_id'],
            'class_id' => null,
        ]);

        session()->flash('status', 'exam-created');
        $this->notifySuccess('Ujian draft berhasil dibuat.');
        $this->closeCreateModal();
    }

    public function publishExam(int $examId): void
    {
        $exam = Exam::query()->findOrFail($examId);
        try {
            app(ExamLifecycleService::class)->publishDraftExam($exam);
            session()->flash('status', 'exam-published');
            $this->notifySuccess('Ujian berhasil dipublish.');
        } catch (AuthorizationException $e) {
            $this->addError('publish', $e->getMessage());
            $this->notifyError('Ujian belum bisa dipublish. '.$e->getMessage());
        }
    }

    public function deleteExam(int $examId): void
    {
        $exam = Exam::query()->findOrFail($examId);
        if ($exam->status !== Exam::STATUS_DRAFT) {
            $this->addError('delete', 'Exam hanya bisa dihapus saat status draft.');
            $this->notifyError('Ujian hanya bisa dihapus saat masih draft.');

            return;
        }

        $exam->delete();
        session()->flash('status', 'exam-deleted');
        $this->notifySuccess('Ujian draft berhasil dihapus.');
        $this->resetPage();
    }

    public function render()
    {
        $lookupTtl = now()->addMinutes(5);
        $exams = Exam::query()
            ->with(['teacher:id,name,email', 'subject:id,code,name', 'semester:id,name'])
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->filterSemesterId !== '', fn ($q) => $q->where('semester_id', (int) $this->filterSemesterId))
            ->when($this->filterExamType !== '', fn ($q) => $q->where('exam_type', $this->filterExamType))
            ->latest()
            ->paginate(10);

        $teachers = Cache::remember('lw:exams:teachers:v1', $lookupTtl, fn () => User::query()
            ->where('role', User::ROLE_TEACHER)
            ->orderBy('name')
            ->get(['id', 'name', 'email']));
        $subjects = Cache::remember('lw:exams:subjects:v1', $lookupTtl, fn () => Subject::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']));
        $schoolYears = Cache::remember('lw:exams:school-years:v1', $lookupTtl, fn () => SchoolYear::query()
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'is_active']));
        $semesters = Cache::remember('lw:exams:semesters:v1', $lookupTtl, fn () => Semester::query()
            ->with('schoolYear:id,name')
            ->orderByDesc('is_active')
            ->orderBy('school_year_id')
            ->orderBy('semester_number')
            ->get(['id', 'name', 'school_year_id', 'semester_number', 'is_active']));

        $gradeLevels = collect(range(1, 6));

        return view('livewire.admin.exams-index-table', [
            'exams' => $exams,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'schoolYears' => $schoolYears,
            'semesters' => $semesters,
            'gradeLevels' => $gradeLevels,
            'examTypes' => Exam::EXAM_TYPES,
        ]);
    }
}
