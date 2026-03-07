<div class="space-y-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            {{-- Input Pencarian --}}
            <div class="grid gap-3 sm:grid-cols-[1fr]">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau email..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($scopeRole === \App\Models\User::ROLE_STUDENT)
                    <select wire:model.live="exportClassId" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Semua kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                @endif
                <select wire:model.live="exportFormat" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Format</option>
                    <option value="xlsx">XLSX</option>
                    <option value="csv">CSV</option>
                </select>
                @php
                    $canExport = $scopeRole !== \App\Models\User::ROLE_STUDENT || $exportClassId !== '';
                    $exportUrl = route('admin.users.export', array_filter([
                        'role' => $scopeRole,
                        'class_id' => $exportClassId ?: null,
                        'format' => $exportFormat ?: 'xlsx',
                    ]));
                @endphp
                <a href="{{ $canExport ? $exportUrl : '#' }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 {{ $canExport ? '' : 'pointer-events-none opacity-50' }}">Export Akun</a>
                @if ($scopeRole !== \App\Models\User::ROLE_STUDENT)
                    <button wire:click="openCreateModal" type="button" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Tambah User</button>
                @endif
            </div>
        </div>
        @error('export') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[980px] text-xs">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    @if ($scopeRole === \App\Models\User::ROLE_STUDENT)
                        <th class="px-4 py-3 font-medium">Detail Siswa</th>
                    @endif
                    <th class="px-4 py-3 font-medium">Terdaftar</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-t border-slate-100">
                        <td class="px-2 py-1 font-medium text-slate-900">{{ $user->name }}</td>
                        <td class="px-2 py-1 text-slate-600">{{ $user->email }}</td>
                        <td class="px-2 py-1"><span class="rounded px-2 py-1 text-xs uppercase bg-slate-100 text-slate-700">{{ $user->role }}</span></td>
                        @if ($scopeRole === \App\Models\User::ROLE_STUDENT)
                            <td class="px-2 py-1 text-slate-600 text-xs">NISN: {{ $user->nis ?? '-' }}<br>Kelas: {{ $user->schoolClass?->name ?? '-' }}</td>
                        @endif
                        <td class="px-2 py-1 text-slate-500">{{ $user->created_at?->format('d M Y') }}</td>
                        <td class="px-2 py-1 whitespace-nowrap">
                            @if ($user->role !== \App\Models\User::ROLE_STUDENT)
                                <button wire:click="openEditModal({{ $user->id }})" type="button" class="rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</button>
                            @endif
                            <button wire:click="deleteUser({{ $user->id }})" data-confirm-message="User ini akan dihapus permanen. Lanjutkan?" type="button" class="rounded border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-left text-slate-500">Belum ada data user.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}

    @if ($showModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">{{ $modalMode === 'edit' ? 'Edit User' : 'Tambah User' }}</h3>
                        <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100" wire:click="closeModal">Tutup</button>
                    </div>
                    <form wire:submit="saveUser" class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                            <input wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" wire:model="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Role</label>
                            <input type="text" value="{{ strtoupper($scopeRole) }}" class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-700" readonly>
                            @error('role') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">{{ $modalMode === 'edit' ? 'Password Baru (opsional)' : 'Password' }}</label>
                                <input type="password" wire:model="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                                <input type="password" wire:model="password_confirmation" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" wire:click="closeModal">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ $modalMode === 'edit' ? 'Update User' : 'Simpan User' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
