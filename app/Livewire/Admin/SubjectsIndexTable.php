<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithCrudNotifications;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\TeacherSubject;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SubjectsIndexTable extends Component
{
    use WithCrudNotifications;
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public string $modalMode = 'create';
    public ?int $editingSubjectId = null;

    public string $code = '';
    public string $name = '';
    public bool $is_active = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal(int $subjectId): void
    {
        $subject = Subject::query()->findOrFail($subjectId);
        $this->resetForm();
        $this->modalMode = 'edit';
        $this->editingSubjectId = $subject->id;
        $this->code = (string) $subject->code;
        $this->name = (string) $subject->name;
        $this->is_active = (bool) $subject->is_active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function saveSubject(): void
    {
        $isEdit = $this->modalMode === 'edit' && $this->editingSubjectId !== null;
        $rules = [
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:128'],
            'is_active' => ['boolean'],
        ];

        if ($isEdit) {
            $rules['code'][] = Rule::unique('subjects', 'code')->ignore($this->editingSubjectId);
            $rules['name'][] = Rule::unique('subjects', 'name')->ignore($this->editingSubjectId);
        } else {
            $rules['code'][] = Rule::unique('subjects', 'code');
            $rules['name'][] = Rule::unique('subjects', 'name');
        }

        $data = $this->validateWithFriendlyMessage($rules);

        if ($isEdit) {
            Subject::query()->whereKey($this->editingSubjectId)->update($data);
            session()->flash('status', 'subject-updated');
            $this->notifySuccess('Data mata pelajaran berhasil diperbarui.');
        } else {
            Subject::query()->create($data);
            session()->flash('status', 'subject-created');
            $this->notifySuccess('Mata pelajaran baru berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function toggleActive(int $subjectId): void
    {
        $subject = Subject::query()->findOrFail($subjectId);
        $subject->update(['is_active' => ! $subject->is_active]);
        session()->flash('status', 'subject-status-updated');
        $this->notifySuccess('Status mata pelajaran berhasil diubah.');
    }

    public function deleteSubject(int $subjectId): void
    {
        $subject = Subject::query()->findOrFail($subjectId);
        $usedByExams = Exam::query()->where('subject_id', $subjectId)->exists();
        $usedByAssignment = TeacherSubject::query()->where('subject_id', $subjectId)->exists();

        if ($usedByExams || $usedByAssignment) {
            $this->addError('delete', 'Mapel tidak bisa dihapus karena sudah dipakai di exam/assignment.');
            $this->notifyError('Mapel tidak bisa dihapus karena sudah dipakai pada ujian atau assignment.');
            return;
        }

        $subject->delete();
        session()->flash('status', 'subject-deleted');
        $this->notifySuccess('Mata pelajaran berhasil dihapus.');
        $this->resetPage();
    }

    protected function resetForm(): void
    {
        $this->editingSubjectId = null;
        $this->code = '';
        $this->name = '';
        $this->is_active = true;
    }

    public function render()
    {
        $subjects = Subject::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->withCount(['exams'])
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.subjects-index-table', [
            'subjects' => $subjects,
        ]);
    }
}
