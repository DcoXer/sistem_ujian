<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithCrudNotifications;
use App\Models\HomeroomTeacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AssignmentsIndexTable extends Component
{
    use WithCrudNotifications;
    use WithPagination;

    // Teacher-subject assignment fields
    public string $assignment_type = 'grade'; // 'grade' or 'class'

    public string $teacher_id = '';

    public string $subject_id = '';

    public string $class_id = '';

    public string $grade_level = '';

    public string $homeroom_teacher_id = '';

    public string $homeroom_class_id = '';

    public function updatingPage(): void
    {
        $this->resetValidation();
    }

    public function createTeacherSubject(): void
    {
        if ($this->assignment_type === 'grade') {
            $data = $this->validateWithFriendlyMessage([
                'teacher_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', User::ROLE_TEACHER))],
                'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
                'grade_level' => ['required', 'integer', 'min:1', 'max:12'],
            ]);
            $data['class_id'] = null;

            $exists = TeacherSubject::query()
                ->where('teacher_id', $data['teacher_id'])
                ->where('subject_id', $data['subject_id'])
                ->where('grade_level', $data['grade_level'])
                ->whereNull('class_id')
                ->exists();

            if ($exists) {
                $this->addError('teacher_id', 'Assignment guru-mapel-tingkat sudah ada.');
                $this->notifyError('Assignment ini sudah ada.');

                return;
            }
        } else {
            $data = $this->validateWithFriendlyMessage([
                'teacher_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', User::ROLE_TEACHER))],
                'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
                'class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            ]);
            $data['grade_level'] = null;

            $exists = TeacherSubject::query()
                ->where('teacher_id', $data['teacher_id'])
                ->where('subject_id', $data['subject_id'])
                ->where('class_id', $data['class_id'])
                ->exists();

            if ($exists) {
                $this->addError('teacher_id', 'Assignment mapel-guru-kelas sudah ada.');
                $this->notifyError('Assignment ini sudah ada.');

                return;
            }
        }

        TeacherSubject::query()->create($data);
        $this->reset(['teacher_id', 'subject_id', 'class_id', 'grade_level']);
        session()->flash('status', 'teacher-subject-assigned');
        $this->notifySuccess('Assignment guru mapel berhasil ditambahkan.');
    }

    public function deleteTeacherSubject(int $id): void
    {
        TeacherSubject::query()->findOrFail($id)->delete();
        session()->flash('status', 'teacher-subject-removed');
        $this->notifySuccess('Assignment guru mapel berhasil dihapus.');
    }

    public function createHomeroom(): void
    {
        $data = $this->validateWithFriendlyMessage([
            'homeroom_teacher_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', User::ROLE_TEACHER))],
            'homeroom_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
        ]);

        HomeroomTeacher::query()->updateOrCreate(
            ['class_id' => $data['homeroom_class_id']],
            ['teacher_id' => $data['homeroom_teacher_id']]
        );

        $this->reset(['homeroom_teacher_id', 'homeroom_class_id']);
        session()->flash('status', 'homeroom-assigned');
        $this->notifySuccess('Wali kelas berhasil ditetapkan.');
    }

    public function deleteHomeroom(int $id): void
    {
        HomeroomTeacher::query()->findOrFail($id)->delete();
        session()->flash('status', 'homeroom-removed');
        $this->notifySuccess('Assignment wali kelas berhasil dihapus.');
    }

    public function render()
    {
        $lookupTtl = now()->addMinutes(5);
        $teachers = Cache::remember('lw:assignments:teachers:v1', $lookupTtl, fn () => User::query()
            ->where('role', User::ROLE_TEACHER)
            ->orderBy('name')
            ->get(['id', 'name', 'email']));
        $subjects = Cache::remember('lw:assignments:subjects:v1', $lookupTtl, fn () => Subject::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']));
        $classes = Cache::remember('lw:assignments:classes:v1', $lookupTtl, fn () => SchoolClass::query()
            ->with('schoolYear:id,name')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level', 'school_year_id']));

        return view('livewire.admin.assignments-index-table', [
            'teachers' => $teachers,
            'subjects' => $subjects,
            'classes' => $classes,
            'teacherSubjects' => TeacherSubject::query()
                ->with(['teacher:id,name', 'subject:id,code,name', 'schoolClass:id,name'])
                ->latest()
                ->paginate(20, ['*'], 'teacher_subjects_page'),
            'homerooms' => HomeroomTeacher::query()
                ->with(['teacher:id,name', 'schoolClass:id,name'])
                ->latest()
                ->paginate(20, ['*'], 'homerooms_page'),
        ]);
    }
}
