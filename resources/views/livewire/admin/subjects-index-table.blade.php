<div class="space-y-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode/nama mapel..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <button wire:click="openCreateModal" type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Tambah Mapel</button>
        </div>
    </div>

    @error('delete') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[860px] text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-2 py-1 font-medium">Kode</th>
                    <th class="px-2 py-1 font-medium">Nama</th>
                    <th class="px-2 py-1 font-medium">Status</th>
                    <th class="px-2 py-1 font-medium">Dipakai Ujian</th>
                    <th class="px-2 py-1 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subjects as $subject)
                    <tr class="border-t border-slate-100">
                        <td class="px-2 py-1 font-medium text-slate-900">{{ $subject->code }}</td>
                        <td class="px-2 py-1 text-slate-700">{{ $subject->name }}</td>
                        <td class="px-2 py-1">
                            <span class="rounded px-2 py-1 text-xs uppercase {{ $subject->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $subject->is_active ? 'aktif' : 'nonaktif' }}
                            </span>
                        </td>
                        <td class="px-2 py-1 text-slate-700">{{ $subject->exams_count }}</td>
                        <td class="px-2 py-1 whitespace-nowrap">
                            <button wire:click="openEditModal({{ $subject->id }})" type="button" class="rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</button>
                            <button wire:click="toggleActive({{ $subject->id }})" type="button" class="rounded border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                {{ $subject->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                            <button wire:click="deleteSubject({{ $subject->id }})" data-confirm-message="Hapus mapel ini?" type="button" class="rounded border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data mapel.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subjects->links() }}

    @if ($showModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">{{ $modalMode === 'edit' ? 'Edit Mapel' : 'Tambah Mapel' }}</h3>
                        <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100" wire:click="closeModal">Tutup</button>
                    </div>

                    <form wire:submit="saveSubject" class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kode</label>
                            <input wire:model="code" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Contoh: MTK" required>
                            @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Mata Pelajaran</label>
                            <input wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Contoh: Matematika" required>
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Mapel aktif
                        </label>

                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" wire:click="closeModal">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ $modalMode === 'edit' ? 'Update' : 'Simpan' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
