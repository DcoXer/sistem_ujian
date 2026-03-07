<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithCrudNotifications;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;

class UsersIndexTable extends Component
{
    use WithCrudNotifications;
    use WithPagination;

    public string $scopeRole = User::ROLE_STUDENT;
    public string $search = '';
    public string $exportClassId = '';
    public string $exportFormat = 'xlsx';

    public bool $showModal = false;
    public string $modalMode = 'create';
    public ?int $editingUserId = null;

    public string $name = '';
    public string $email = '';
    public string $role = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingExportClassId(): void
    {
        $this->resetPage();
    }

    public function mount(string $scopeRole = User::ROLE_STUDENT): void
    {
        if (! in_array($scopeRole, [User::ROLE_STUDENT, User::ROLE_TEACHER, User::ROLE_OPERATOR], true)) {
            abort(404);
        }

        $this->scopeRole = $scopeRole;
        if ($this->scopeRole !== User::ROLE_STUDENT) {
            $this->exportClassId = '';
        }
    }

    public function openCreateModal(): void
    {
        if ($this->scopeRole === User::ROLE_STUDENT) {
            $this->addError('role', 'Akun siswa dibuat dari modul rombel.');
            $this->notifyError('Akun siswa dibuat dari menu rombel, bukan dari menu ini.');
            return;
        }

        $this->resetForm();
        $this->modalMode = 'create';
        $this->role = $this->scopeRole;
        $this->showModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        if ($user->role === User::ROLE_STUDENT) {
            $this->addError('role', 'Akun siswa dikelola dari modul rombel.');
            $this->notifyError('Akun siswa dikelola dari menu rombel.');
            return;
        }
        if ($user->role !== $this->scopeRole) {
            $this->addError('role', 'Akun ini tidak berada di kategori halaman saat ini.');
            $this->notifyError('Akun ini tidak ada di kategori yang sedang dibuka.');
            return;
        }

        $this->resetForm();
        $this->modalMode = 'edit';
        $this->editingUserId = $user->id;
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->role = (string) $user->role;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function saveUser(): void
    {
        $isEdit = $this->modalMode === 'edit' && $this->editingUserId !== null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'role' => ['required', Rule::in([$this->scopeRole])],
        ];

        if ($isEdit) {
            $rules['email'][] = Rule::unique(User::class)->ignore($this->editingUserId);
            $rules['password'] = ['nullable', 'confirmed', Password::defaults()];
        } else {
            $rules['email'][] = Rule::unique(User::class);
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $data = $this->validateWithFriendlyMessage($rules);

        if ($isEdit) {
            $user = User::query()->findOrFail($this->editingUserId);
            if (
                $user->role === User::ROLE_ADMIN
                && $data['role'] !== User::ROLE_ADMIN
                && User::query()->where('role', User::ROLE_ADMIN)->count() <= 1
            ) {
                $this->addError('role', 'Minimal harus ada 1 admin aktif.');
                $this->notifyError('Gagal menyimpan. Minimal harus ada 1 admin aktif.');
                return;
            }

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $user->update($data);
            session()->flash('status', 'user-updated');
            $this->notifySuccess('Data user berhasil diperbarui.');
        } else {
            User::query()->create($data);
            session()->flash('status', 'user-created');
            $this->notifySuccess('User baru berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function deleteUser(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        if ($user->role !== $this->scopeRole) {
            $this->addError('delete', 'User tidak berada di kategori halaman ini.');
            $this->notifyError('User ini tidak ada di kategori yang sedang dibuka.');
            return;
        }
        if ((int) auth()->id() === (int) $user->id) {
            $this->addError('delete', 'Akun sendiri tidak boleh dihapus.');
            $this->notifyError('Akun yang sedang dipakai tidak bisa dihapus.');
            return;
        }

        if ($user->role === User::ROLE_ADMIN && User::query()->where('role', User::ROLE_ADMIN)->count() <= 1) {
            $this->addError('delete', 'Minimal harus ada 1 admin aktif.');
            $this->notifyError('Gagal hapus user. Sistem harus punya minimal 1 admin.');
            return;
        }

        $user->delete();
        session()->flash('status', 'user-deleted');
        $this->notifySuccess('User berhasil dihapus.');
        $this->resetPage();
    }

    protected function resetForm(): void
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->role = '';
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function render()
    {
        $lookupTtl = now()->addMinutes(5);

        $users = User::query()
            ->with('schoolClass:id,name')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->where('role', $this->scopeRole)
            ->when(
                $this->scopeRole === User::ROLE_STUDENT && $this->exportClassId !== '',
                fn ($query) => $query->where('class_id', (int) $this->exportClassId)
            )
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.users-index-table', [
            'users' => $users,
            'scopeRole' => $this->scopeRole,
            'classes' => Cache::remember('lw:users:classes:v1', $lookupTtl, fn () => SchoolClass::query()
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get(['id', 'name', 'grade_level'])),
        ]);
    }
}
