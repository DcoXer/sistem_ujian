<div class="space-y-6">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-center gap-2">
            <button wire:click="openSchoolYearModal" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah Tahun Ajaran</button>
            <button wire:click="openCreateClassModal" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Tambah Rombel</button>
            <button wire:click="openSyncModal" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Tambah / Import Siswa</button>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Daftar Tahun Ajaran</h3>

        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full min-w-[680px] text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr><th class="px-3 py-2 font-medium">Tahun Ajaran</th><th class="px-3 py-2 font-medium">Mulai</th><th class="px-3 py-2 font-medium">Selesai</th><th class="px-3 py-2 font-medium">Status</th><th class="px-3 py-2 font-medium text-center">Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach ($schoolYears as $year)
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2 font-medium text-slate-900">{{ $year->name }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $year->start_date ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $year->end_date ?? '-' }}</td>
                            <td class="px-3 py-2"><span class="rounded px-2 py-1 text-[10px] uppercase {{ $year->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $year->is_active ? 'aktif' : 'nonaktif' }}</span></td>
                            <td class="px-3 py-2 text-right">
                                @if (! $year->is_active)
                                    <button wire:click="activateSchoolYear({{ $year->id }})" data-confirm-message="Set tahun ajaran ini sebagai aktif?" class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Set Aktif</button>
                                @else
                                    <span class="text-xs text-slate-500">Dipakai default ujian</span>
                                @endif
                            </td>
                        </tr>
                        {{-- Semester rows --}}
                        @foreach ($year->semesters as $semester)
                            <tr class="border-t border-slate-100 bg-slate-50/50">
                                <td class="py-1.5 pl-8 pr-3 text-xs text-slate-600">
                                    <span class="mr-1 text-slate-400">↳</span>{{ $semester->name }}
                                </td>
                                <td class="px-3 py-1.5 text-xs text-slate-500">{{ $semester->start_date?->format('d M Y') ?? '-' }}</td>
                                <td class="px-3 py-1.5 text-xs text-slate-500">{{ $semester->end_date?->format('d M Y') ?? '-' }}</td>
                                <td class="px-3 py-1.5">
                                    <span class="rounded px-2 py-0.5 text-[10px] uppercase {{ $semester->is_active ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $semester->is_active ? 'aktif' : '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-1.5 text-right">
                                    @if (! $semester->is_active)
                                        <button wire:click="activateSemester({{ $semester->id }})" data-confirm-message="Set semester ini sebagai aktif?" class="rounded border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 hover:bg-indigo-100">Set Aktif</button>
                                    @else
                                        <span class="text-[10px] text-indigo-500">Semester berjalan</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        {{-- Add semester buttons --}}
                        <tr class="border-t border-dashed border-slate-100">
                            <td colspan="5" class="py-1.5 pl-8 pr-3">
                                <div class="flex flex-wrap gap-2">
                                    @if (! $year->semesters->contains('semester_number', 1))
                                        <button wire:click="createSemester({{ $year->id }}, 1, null, null)" class="rounded border border-slate-300 px-2 py-0.5 text-[10px] font-medium text-slate-600 hover:bg-slate-50">+ Semester 1</button>
                                    @endif
                                    @if (! $year->semesters->contains('semester_number', 2))
                                        <button wire:click="createSemester({{ $year->id }}, 2, null, null)" class="rounded border border-slate-300 px-2 py-0.5 text-[10px] font-medium text-slate-600 hover:bg-slate-50">+ Semester 2</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[980px] text-sm">
            <thead class="bg-slate-50 text-slate-600 text-center">
                <tr>
                    <th class="px-2 py-1 font-medium">Rombel</th>
                    <th class="px-2 py-1 font-medium">Tingkat</th>
                    <th class="px-2 py-1 font-medium">Tahun Ajaran</th>
                    <th class="px-2 py-1 font-medium">Siswa</th>
                    <th class="px-2 py-1 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $class)
                    <tr class="border-t border-slate-100 text-center">
                        <td class="px-2 py-1 font-medium text-slate-900">{{ $class->name }}</td>
                        <td class="px-2 py-1 text-slate-700">{{ $class->grade_level ?? '-' }}</td>
                        <td class="px-2 py-1 text-slate-700">{{ $class->schoolYear?->name ?? '-' }}</td>
                        <td class="px-2 py-1 text-slate-700">{{ $class->students_count }}</td>
                        <td class="px-2 py-1 whitespace-nowrap">
                            <button wire:click="openEditClassModal({{ $class->id }})" class="rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</button>
                            <button wire:click="openSyncModal({{ $class->id }})" class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">Sync Siswa</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data rombel.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $classes->links() }}

    @if ($showSchoolYearModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeSchoolYearModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <h3 class="mb-4 text-xl font-bold text-slate-900">Tambah Tahun Ajaran</h3>
                    <form wire:submit="createSchoolYear" class="space-y-3">
                        <input wire:model="schoolYearName" placeholder="Contoh: 2025/2026" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        <div class="grid gap-3 md:grid-cols-2">
                            <input type="date" wire:model="schoolYearStart" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <input type="date" wire:model="schoolYearEnd" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        @error('schoolYearName') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        @error('schoolYearStart') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        @error('schoolYearEnd') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" wire:click="closeSchoolYearModal">Batal</button>
                            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showCreateClassModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeCreateClassModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <h3 class="mb-4 text-xl font-bold text-slate-900">Tambah Rombel</h3>
                    <form wire:submit="createClass" class="space-y-3">
                        <input wire:model="className" placeholder="Nama kelas (contoh: 6A)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        <input type="number" wire:model="classGrade" min="1" max="12" placeholder="Tingkat" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <select wire:model="classSchoolYearId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Tahun ajaran</option>
                            @foreach ($schoolYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                        @error('className') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        @error('classGrade') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        @error('classSchoolYearId') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" wire:click="closeCreateClassModal">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showEditClassModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeEditClassModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <h3 class="mb-4 text-xl font-bold text-slate-900">Edit Rombel</h3>
                    <form wire:submit="updateClass" class="space-y-3">
                        <input wire:model="editClassName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        <input type="number" wire:model="editClassGrade" min="1" max="12" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        {{-- <input wire:model="editClassMajor" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"> --}}
                        <select wire:model="editClassSchoolYearId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Tahun ajaran</option>
                            @foreach ($schoolYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" wire:click="closeEditClassModal">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showSyncModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeSyncModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Sync Siswa</h3>
                    <p class="mb-1 text-xs text-slate-500">Format lama: <code>NIS|Nama Siswa</code></p>
                    <p class="mb-3 text-xs text-slate-500">Format baru: <code>NISN|Nama Lengkap|NIK|Tempat Lahir|Tanggal Lahir|Rombel|Tingkat|Nama Wali</code></p>
                    <div class="mb-3 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-500">Download template:</span>
                        <a href="{{ route('admin.classes.students.template', ['format' => 'xlsx']) }}" class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 font-semibold text-indigo-700 hover:bg-indigo-100">XLSX</a>
                        <a href="{{ route('admin.classes.students.template', ['format' => 'csv']) }}" class="rounded border border-slate-300 bg-slate-100 px-2 py-1 font-semibold text-slate-700 hover:bg-slate-200">CSV</a>
                    </div>
                    <form wire:submit="syncStudents" class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Pilih Rombel</label>
                            <select wire:model="syncClassId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Pilih kelas</option>
                                @foreach ($classOptions as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->schoolYear?->name ?? '-' }})</option>
                                @endforeach
                            </select>
                            @error('syncClassId') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Upload File CSV/XLSX (opsional)</label>
                            <input type="file" wire:model="studentsFile" accept=".csv,.txt,.xls,.xlsx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('studentsFile') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <p class="text-[11px] text-slate-500">Kalau file diisi, sistem pakai file. Kalau tidak, sistem pakai input teks di bawah.</p>
                        <textarea wire:model="studentsRaw" rows="8" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('studentsRaw') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" wire:click="closeSyncModal">Batal</button>
                            <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Import & Sync</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
