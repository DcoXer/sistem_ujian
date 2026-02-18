<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Manajemen User</h1>
    </x-slot>

    @php
        $hasUserFormErrors = $errors->has('name') || $errors->has('email') || $errors->has('role') || $errors->has('password');
        $modalMode = $modal ?? '';
    @endphp

    <div class="space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 sm:grid-cols-[1fr_auto_auto]">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nama atau email..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    >
                    <select name="role" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Semua role</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="author" {{ $role === 'author' ? 'selected' : '' }}>Author</option>
                        <option value="operator" {{ $role === 'operator' ? 'selected' : '' }}>Operator</option>
                        <option value="peserta" {{ $role === 'peserta' ? 'selected' : '' }}>Peserta</option>
                    </select>
                    <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter/Cari</button>
                </form>
                <button id="open-user-create" type="button" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-700">
                    Tambah User
                </button>
            </div>
        </div>

        <div class="space-y-3 sm:hidden">
            @forelse ($users as $user)
                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-slate-600">{{ $user->email }}</p>
                        </div>
                        <span class="rounded px-2 py-1 text-[10px] uppercase {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : ($user->role === 'author' ? 'bg-violet-100 text-violet-700' : ($user->role === 'operator' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')) }}">
                            {{ $user->role }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Terdaftar: {{ $user->created_at?->format('d M Y') }}</p>
                    <div class="mt-3 flex items-center gap-2">
                        <button
                            type="button"
                            class="open-user-edit rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->name }}"
                            data-user-email="{{ $user->email }}"
                            data-user-role="{{ $user->role }}"
                        >
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm data-confirm-title="Hapus User" data-confirm-message="User ini akan dihapus permanen. Lanjutkan?">
                            @csrf
                            @method('DELETE')
                            <button class="rounded border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Hapus</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">Belum ada data user.</div>
            @endforelse
        </div>

        <div class="-mx-4 hidden px-4 sm:block">
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm [scrollbar-gutter:stable]">
                <table class="w-full min-w-[980px] text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Terdaftar</th>
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded px-2 py-1 text-xs uppercase {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : ($user->role === 'author' ? 'bg-violet-100 text-violet-700' : ($user->role === 'operator' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')) }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $user->created_at?->format('d M Y') }}</td>
                            <td class="min-w-[190px] whitespace-nowrap px-4 py-3 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        class="open-user-edit rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-role="{{ $user->role }}"
                                    >
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm data-confirm-title="Hapus User" data-confirm-message="User ini akan dihapus permanen. Lanjutkan?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data user.</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>

    <div id="user-modal" class="fixed inset-0 z-[105] hidden">
        <div class="user-modal-backdrop absolute inset-0 bg-slate-900/60"></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 id="user-modal-title" class="text-xl font-bold text-slate-900">Tambah User</h3>
                    <button type="button" class="close-user-modal rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100">Tutup</button>
                </div>

                @if ($hasUserFormErrors)
                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        Periksa kembali input form user.
                    </div>
                @endif

                <form id="user-modal-form" method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                    @csrf
                    <input id="user-modal-method" type="hidden" name="_method" value="POST">
                    <input id="user-editing-id" type="hidden" name="editing_user_id" value="{{ old('editing_user_id') }}">

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                        <input id="user-name" name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input id="user-email" type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Role</label>
                        <select id="user-role" name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Pilih role</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="author" {{ old('role') === 'author' ? 'selected' : '' }}>Author</option>
                            <option value="operator" {{ old('role') === 'operator' ? 'selected' : '' }}>Operator</option>
                            <option value="peserta" {{ old('role') === 'peserta' ? 'selected' : '' }}>Peserta</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label id="user-password-label" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                            <input id="user-password" type="password" name="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('password')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label id="user-password-confirm-label" class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                            <input id="user-password-confirm" type="password" name="password_confirmation" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" class="close-user-modal rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Batal</button>
                        <button id="user-modal-submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('user-modal');
            if (!modal) return;

            const modalTitle = document.getElementById('user-modal-title');
            const modalForm = document.getElementById('user-modal-form');
            const methodInput = document.getElementById('user-modal-method');
            const editingIdInput = document.getElementById('user-editing-id');
            const openCreateBtn = document.getElementById('open-user-create');
            const closeButtons = modal.querySelectorAll('.close-user-modal');
            const backdrop = modal.querySelector('.user-modal-backdrop');

            const nameInput = document.getElementById('user-name');
            const emailInput = document.getElementById('user-email');
            const roleInput = document.getElementById('user-role');
            const passwordInput = document.getElementById('user-password');
            const passwordConfirmInput = document.getElementById('user-password-confirm');
            const passwordLabel = document.getElementById('user-password-label');
            const passwordConfirmLabel = document.getElementById('user-password-confirm-label');
            const submitBtn = document.getElementById('user-modal-submit');

            const storeUrl = @json(route('admin.users.store'));
            const updateBase = @json(url('/admin/users'));

            const openModal = () => modal.classList.remove('hidden');
            const closeModal = () => modal.classList.add('hidden');

            const setCreateMode = () => {
                modalTitle.textContent = 'Tambah User';
                modalForm.action = storeUrl;
                methodInput.value = 'POST';
                editingIdInput.value = '';
                submitBtn.textContent = 'Simpan User';

                nameInput.value = '';
                emailInput.value = '';
                roleInput.value = '';
                passwordInput.value = '';
                passwordConfirmInput.value = '';
                passwordInput.required = true;
                passwordConfirmInput.required = true;
                passwordLabel.textContent = 'Password';
                passwordConfirmLabel.textContent = 'Konfirmasi Password';
            };

            const setEditMode = (user) => {
                modalTitle.textContent = 'Edit User';
                modalForm.action = `${updateBase}/${user.id}`;
                methodInput.value = 'PUT';
                editingIdInput.value = user.id;
                submitBtn.textContent = 'Update User';

                nameInput.value = user.name || '';
                emailInput.value = user.email || '';
                roleInput.value = user.role || '';
                passwordInput.value = '';
                passwordConfirmInput.value = '';
                passwordInput.required = false;
                passwordConfirmInput.required = false;
                passwordLabel.textContent = 'Password Baru (opsional)';
                passwordConfirmLabel.textContent = 'Konfirmasi Password Baru';
            };

            openCreateBtn?.addEventListener('click', () => {
                setCreateMode();
                openModal();
            });

            document.querySelectorAll('.open-user-edit').forEach((btn) => {
                btn.addEventListener('click', () => {
                    setEditMode({
                        id: btn.dataset.userId,
                        name: btn.dataset.userName,
                        email: btn.dataset.userEmail,
                        role: btn.dataset.userRole,
                    });
                    openModal();
                });
            });

            closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
            backdrop?.addEventListener('click', closeModal);

            const hasErrors = @json($hasUserFormErrors);
            const oldEditingId = @json(old('editing_user_id'));
            const modalMode = @json($modalMode);
            const modalUser = @json($modalUser);

            if (hasErrors) {
                if (oldEditingId) {
                    setEditMode({
                        id: oldEditingId,
                        name: @json(old('name')),
                        email: @json(old('email')),
                        role: @json(old('role')),
                    });
                } else {
                    setCreateMode();
                }
                openModal();
                return;
            }

            if (modalMode === 'create') {
                setCreateMode();
                openModal();
            }

            if (modalMode === 'edit' && modalUser) {
                setEditMode({
                    id: modalUser.id,
                    name: modalUser.name,
                    email: modalUser.email,
                    role: modalUser.role,
                });
                openModal();
            }
        });
    </script>
</x-app-layout>
