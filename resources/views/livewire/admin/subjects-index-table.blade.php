<div class="space-y-6">

    {{-- Header & Actions --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white">
                    <x-icon name="book" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Manajemen Kurikulum</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Mata Pelajaran</h2>
                    <p class="mt-1 text-sm text-slate-500">Kelola daftar mata pelajaran yang tersedia untuk ujian.</p>
                </div>
            </div>
            <div class="sm:shrink-0">
                <button wire:click="openCreateModal" type="button" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Mapel
                </button>
            </div>
        </div>
    </section>

    {{-- Filter Bar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <x-icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode atau nama mapel..." class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400">
        </div>
        <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400 sm:w-44">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
        </select>
    </div>

    @error('delete') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror

    {{-- Subjects Table --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if ($subjects->isEmpty())
            <div class="py-16 text-center">
                <x-icon name="book" class="mx-auto h-10 w-10 text-slate-200" />
                <p class="mt-3 text-sm font-medium text-slate-500">Belum ada mata pelajaran.</p>
                <p class="mt-1 text-xs text-slate-400">Tambah mapel dengan tombol di atas.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Nama Mata Pelajaran</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-center">Dipakai Ujian</th>
                            <th class="px-5 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($subjects as $subject)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-5 py-3">
                                    <span class="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-bold tracking-wide text-amber-700">{{ $subject->code }}</span>
                                </td>
                                <td class="px-5 py-3 font-medium text-slate-900">{{ $subject->name }}</td>
                                <td class="px-5 py-3">
                                    @if ($subject->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">{{ $subject->exams_count }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <button wire:click="openEditModal({{ $subject->id }})" type="button" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</button>
                                        <button wire:click="toggleActive({{ $subject->id }})" type="button" class="rounded-lg border {{ $subject->is_active ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} px-3 py-1 text-xs font-semibold">
                                            {{ $subject->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                        <button wire:click="deleteSubject({{ $subject->id }})" data-confirm-message="Hapus mata pelajaran ini?" type="button" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">
                {{ $subjects->links() }}
            </div>
        @endif
    </section>

    {{-- Modal: Tambah / Edit Mapel --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">{{ $modalMode === 'edit' ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' }}</h3>
                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-500 hover:bg-slate-100" wire:click="closeModal">Tutup</button>
                    </div>

                    <form wire:submit="saveSubject" class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kode Mapel</label>
                            <input wire:model="code" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase tracking-wide" placeholder="Contoh: MTK" required>
                            @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Mata Pelajaran</label>
                            <input wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Contoh: Matematika" required>
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 hover:bg-slate-50">
                            <input type="checkbox" wire:model="is_active" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <p class="text-sm font-medium text-slate-700">Mapel Aktif</p>
                                <p class="text-xs text-slate-400">Mapel aktif bisa dipilih saat buat ujian.</p>
                            </div>
                        </label>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" wire:click="closeModal">Batal</button>
                            <button class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">{{ $modalMode === 'edit' ? 'Update' : 'Simpan' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
