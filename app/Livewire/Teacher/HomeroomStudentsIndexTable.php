<?php

namespace App\Livewire\Teacher;

use App\Livewire\Concerns\WithCrudNotifications;
use App\Models\HomeroomTeacher;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class HomeroomStudentsIndexTable extends Component
{
    use WithCrudNotifications;
    use WithPagination;

    public string $search = '';
    public string $classFilter = '';

    public bool $showEditModal = false;
    public ?int $editingStudentId = null;

    public string $name = '';
    public string $nis = '';
    public string $nisn = '';
    public string $nik = '';
    public string $birth_place = '';
    public string $birth_date = '';
    public string $guardian_name = '';
    public string $class_id = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function openEditModal(int $studentId): void
    {
        $assignedClassIds = $this->assignedClassIds();

        $student = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->whereIn('class_id', $assignedClassIds)
            ->findOrFail($studentId);

        $this->editingStudentId = $student->id;
        $this->name = (string) $student->name;
        $this->nis = (string) ($student->nis ?? '');
        $this->nisn = (string) ($student->nisn ?? '');
        $this->nik = (string) ($student->nik ?? '');
        $this->birth_place = (string) ($student->birth_place ?? '');
        $this->birth_date = (string) optional($student->birth_date)->format('Y-m-d');
        $this->guardian_name = (string) ($student->guardian_name ?? '');
        $this->class_id = (string) ($student->class_id ?? '');

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingStudentId = null;
        $this->reset([
            'name', 'nis', 'nisn', 'nik', 'birth_place', 'birth_date', 'guardian_name', 'class_id',
        ]);
        $this->resetValidation();
    }

    public function saveStudent(): void
    {
        if ($this->editingStudentId === null) {
            return;
        }

        $assignedClassIds = $this->assignedClassIds();
        $student = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->whereIn('class_id', $assignedClassIds)
            ->findOrFail($this->editingStudentId);

        $data = $this->validateWithFriendlyMessage([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:32', Rule::unique('users', 'nis')->ignore($student->id)],
            'nisn' => ['nullable', 'string', 'max:32', Rule::unique('users', 'nisn')->ignore($student->id)],
            'nik' => ['nullable', 'string', 'max:32', Rule::unique('users', 'nik')->ignore($student->id)],
            'birth_place' => ['nullable', 'string', 'max:128'],
            'birth_date' => ['nullable', 'date'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'class_id' => ['required', Rule::in(array_map('strval', $assignedClassIds))],
        ]);

        $student->update([
            'name' => $data['name'],
            'nis' => $data['nis'] ?: null,
            'nisn' => $data['nisn'] ?: null,
            'nik' => $data['nik'] ?: null,
            'birth_place' => $data['birth_place'] ?: null,
            'birth_date' => $data['birth_date'] ?: null,
            'guardian_name' => $data['guardian_name'] ?: null,
            'class_id' => (int) $data['class_id'],
        ]);

        session()->flash('status', 'student-updated-by-homeroom');
        $this->notifySuccess('Data siswa berhasil diperbarui.');
        $this->closeEditModal();
    }

    public function render()
    {
        $assignedClassIds = $this->assignedClassIds();
        $assignedClasses = SchoolClass::query()
            ->whereIn('id', $assignedClassIds)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        $students = User::query()
            ->with('schoolClass:id,name,grade_level')
            ->where('role', User::ROLE_STUDENT)
            ->whereIn('class_id', $assignedClassIds)
            ->when($this->classFilter !== '', fn ($q) => $q->where('class_id', (int) $this->classFilter))
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('nis', 'like', '%'.$this->search.'%')
                        ->orWhere('nisn', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.teacher.homeroom-students-index-table', [
            'students' => $students,
            'assignedClasses' => $assignedClasses,
        ]);
    }

    protected function assignedClassIds(): array
    {
        return HomeroomTeacher::query()
            ->where('teacher_id', (int) auth()->id())
            ->pluck('class_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }
}
