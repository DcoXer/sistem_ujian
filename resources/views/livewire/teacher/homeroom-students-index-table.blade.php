<div class="space-y-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama/NIS/NISN..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            {{-- <select wire:model.live="classFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua kelas wali</option>
                @foreach ($assignedClasses as $class)
                    <option value="{{ $class->id }}">{{ $class->name }} (Tingkat {{ $class->grade_level ?? '-' }})</option>
                @endforeach
            </select> --}}
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1200px] text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Kelas</th>
                    <th class="px-4 py-3 font-medium">NIS</th>
                    <th class="px-4 py-3 font-medium">NISN</th>
                    <th class="px-4 py-3 font-medium">NIK</th>
                    <th class="px-4 py-3 font-medium">TTL</th>
                    <th class="px-4 py-3 font-medium">Nama Wali</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $student->name }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $student->schoolClass?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $student->nis ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $student->nisn ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $student->nik ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $student->birth_place ?? '-' }}{{ $student->birth_date ? ', '.$student->birth_date->format('d M Y') : '' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $student->guardian_name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <button wire:click="openEditModal({{ $student->id }})" class="rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">Belum ada data siswa pada kelas wali Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $students->links() }}

    @if ($showEditModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeEditModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Edit Data Siswa</h3>
                        <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100" wire:click="closeEditModal">Tutup</button>
                    </div>
                    <form wire:submit="saveStudent" class="space-y-4">
                        <div><label class="mb-1 block text-sm font-medium text-slate-700">Nama Lengkap</label><input wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>@error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div><label class="mb-1 block text-sm font-medium text-slate-700">NIS</label><input wire:model="nis" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('nis') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                            <div><label class="mb-1 block text-sm font-medium text-slate-700">NISN</label><input wire:model="nisn" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('nisn') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                            <div><label class="mb-1 block text-sm font-medium text-slate-700">NIK</label><input wire:model="nik" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('nik') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="mb-1 block text-sm font-medium text-slate-700">Tempat Lahir</label><input wire:model="birth_place" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('birth_place') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                            <div><label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Lahir</label><input type="date" wire:model="birth_date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('birth_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="mb-1 block text-sm font-medium text-slate-700">Nama Wali</label><input wire:model="guardian_name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('guardian_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                            <div><label class="mb-1 block text-sm font-medium text-slate-700">Rombel</label><select wire:model="class_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@foreach ($assignedClasses as $class)<option value="{{ $class->id }}">{{ $class->name }} (Tingkat {{ $class->grade_level ?? '-' }})</option>@endforeach</select>@error('class_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" wire:click="closeEditModal">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

