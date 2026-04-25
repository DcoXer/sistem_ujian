<div class="space-y-6">

    {{-- Header & Actions --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white">
                    <x-icon name="users" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Manajemen Akademik</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Rombel & Siswa</h2>
                    <p class="mt-1 text-sm text-slate-500">Kelola tahun ajaran, semester, rombel, dan import data siswa.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 sm:shrink-0">
                <button wire:click="openSchoolYearModal" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tahun Ajaran
                </button>
                <button wire:click="openCreateClassModal" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Rombel
                </button>
                <button wire:click="openSyncModal" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    <x-icon name="upload" class="h-4 w-4" />
                    Import Siswa
                </button>
            </div>
        </div>
    </section>

    {{-- Tahun Ajaran & Semester --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-2">
            <x-icon name="calendar" class="h-5 w-5 text-indigo-500" />
            <h3 class="text-base font-semibold text-slate-900">Tahun Ajaran & Semester</h3>
        </div>

        @if ($schoolYears->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center">
                <x-icon name="calendar" class="mx-auto h-8 w-8 text-slate-300" />
                <p class="mt-2 text-sm text-slate-500">Belum ada tahun ajaran. Tambah dulu.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($schoolYears as $year)
                    <div class="rounded-xl border {{ $year->is_active ? 'border-indigo-200 bg-indigo-50/40' : 'border-slate-200 bg-slate-50/40' }}">
                        {{-- Tahun Ajaran Row --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $year->is_active ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500' }}">
                                    <x-icon name="academic-cap" class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $year->name }}</p>
                                    @if ($year->start_date || $year->end_date)
                                        <p class="text-xs text-slate-500">{{ $year->start_date ?? '-' }} s/d {{ $year->end_date ?? '-' }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($year->is_active)
                                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Nonaktif</span>
                                    <button wire:click="activateSchoolYear({{ $year->id }})" data-confirm-message="Set tahun ajaran ini sebagai aktif?" class="rounded-lg border border-indigo-200 bg-white px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">
                                        Set Aktif
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Semesters --}}
                        @if ($year->semesters->isNotEmpty())
                            <div class="border-t border-slate-200/60 px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($year->semesters as $semester)
                                        <div class="flex items-center gap-2 rounded-lg border {{ $semester->is_active ? 'border-indigo-200 bg-indigo-50' : 'border-slate-200 bg-white' }} px-3 py-1.5">
                                            <span class="text-xs font-medium {{ $semester->is_active ? 'text-indigo-700' : 'text-slate-600' }}">{{ $semester->name }}</span>
                                            @if ($semester->is_active)
                                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">Berjalan</span>
                                            @else
                                                <button wire:click="activateSemester({{ $semester->id }})" data-confirm-message="Set semester ini sebagai aktif?" class="text-[10px] font-semibold text-indigo-600 hover:underline">
                                                    Set Aktif
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Add Semester Buttons --}}
                        @if (! $year->semesters->contains('semester_number', 1) || ! $year->semesters->contains('semester_number', 2))
                            <div class="border-t border-dashed border-slate-200/60 px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    @if (! $year->semesters->contains('semester_number', 1))
                                        <button wire:click="createSemester({{ $year->id }}, 1, null, null)" class="rounded-lg border border-dashed border-slate-300 px-3 py-1 text-xs font-medium text-slate-500 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600">
                                            + Semester 1
                                        </button>
                                    @endif
                                    @if (! $year->semesters->contains('semester_number', 2))
                                        <button wire:click="createSemester({{ $year->id }}, 2, null, null)" class="rounded-lg border border-dashed border-slate-300 px-3 py-1 text-xs font-medium text-slate-500 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600">
                                            + Semester 2
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Daftar Rombel --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div class="flex items-center gap-2">
                <x-icon name="list" class="h-5 w-5 text-slate-500" />
                <h3 class="text-base font-semibold text-slate-900">Daftar Rombel</h3>
            </div>
        </div>

        @if ($classes->isEmpty())
            <div class="py-12 text-center">
                <x-icon name="users" class="mx-auto h-10 w-10 text-slate-200" />
                <p class="mt-3 text-sm font-medium text-slate-500">Belum ada rombel.</p>
                <p class="mt-1 text-xs text-slate-400">Tambah rombel dengan tombol di atas.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Nama Rombel</th>
                            <th class="px-5 py-3">Tingkat</th>
                            <th class="px-5 py-3">Tahun Ajaran</th>
                            <th class="px-5 py-3 text-center">Siswa</th>
                            <th class="px-5 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($classes as $class)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900">{{ $class->name }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    @if ($class->grade_level)
                                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">Kelas {{ $class->grade_level }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $class->schoolYear?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">{{ $class->students_count }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <button wire:click="openEditClassModal({{ $class->id }})" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</button>
                                        <button wire:click="openSyncModal({{ $class->id }})" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">Sync Siswa</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">
                {{ $classes->links() }}
            </div>
        @endif
    </section>

    {{-- Modal: Tambah Tahun Ajaran --}}
    @if ($showSchoolYearModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeSchoolYearModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Tambah Tahun Ajaran</h3>
                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-500 hover:bg-slate-100" wire:click="closeSchoolYearModal">Tutup</button>
                    </div>
                    <form wire:submit="createSchoolYear" class="space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Tahun Ajaran</label>
                            <input wire:model="schoolYearName" placeholder="Contoh: 2025/2026" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            @error('schoolYearName') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Mulai</label>
                                <input type="date" wire:model="schoolYearStart" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @error('schoolYearStart') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Selesai</label>
                                <input type="date" wire:model="schoolYearEnd" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @error('schoolYearEnd') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" wire:click="closeSchoolYearModal">Batal</button>
                            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Tambah Rombel --}}
    @if ($showCreateClassModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeCreateClassModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Tambah Rombel</h3>
                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-500 hover:bg-slate-100" wire:click="closeCreateClassModal">Tutup</button>
                    </div>
                    <form wire:submit="createClass" class="space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Rombel</label>
                            <input wire:model="className" placeholder="Contoh: 6A" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            @error('className') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tingkat Kelas</label>
                            <input type="number" wire:model="classGrade" min="1" max="12" placeholder="Contoh: 6" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('classGrade') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tahun Ajaran</label>
                            <select wire:model="classSchoolYearId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Pilih tahun ajaran</option>
                                @foreach ($schoolYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                            @error('classSchoolYearId') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" wire:click="closeCreateClassModal">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Edit Rombel --}}
    @if ($showEditClassModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeEditClassModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Edit Rombel</h3>
                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-500 hover:bg-slate-100" wire:click="closeEditClassModal">Tutup</button>
                    </div>
                    <form wire:submit="updateClass" class="space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Rombel</label>
                            <input wire:model="editClassName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tingkat Kelas</label>
                            <input type="number" wire:model="editClassGrade" min="1" max="12" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tahun Ajaran</label>
                            <select wire:model="editClassSchoolYearId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Pilih tahun ajaran</option>
                                @foreach ($schoolYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" wire:click="closeEditClassModal">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Sync Siswa --}}
    @if ($showSyncModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeSyncModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-1 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Import & Sync Siswa</h3>
                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-500 hover:bg-slate-100" wire:click="closeSyncModal">Tutup</button>
                    </div>
                    <p class="mb-4 text-xs text-slate-500">Format lama: <code class="rounded bg-slate-100 px-1">NIS|Nama Siswa</code> &bull; Format baru: <code class="rounded bg-slate-100 px-1">NISN|Nama|NIK|Tempat|TglLahir|Rombel|Tingkat|NamaWali</code></p>

                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Download template:</span>
                        <a href="{{ route('admin.classes.students.template', ['format' => 'xlsx']) }}" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">XLSX</a>
                        <a href="{{ route('admin.classes.students.template', ['format' => 'csv']) }}" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">CSV</a>
                    </div>

                    <form wire:submit="syncStudents" class="space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Pilih Rombel</label>
                            <select wire:model="syncClassId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Pilih kelas</option>
                                @foreach ($classOptions as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->schoolYear?->name ?? '-' }})</option>
                                @endforeach
                            </select>
                            @error('syncClassId') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Upload File <span class="font-normal text-slate-400">(CSV/XLSX, opsional)</span></label>
                            <input type="file" wire:model="studentsFile" accept=".csv,.txt,.xls,.xlsx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('studentsFile') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Atau Input Manual</label>
                            <p class="mb-1 text-xs text-slate-400">Kalau file diisi, teks di bawah diabaikan.</p>
                            <textarea wire:model="studentsRaw" rows="6" placeholder="Isi data siswa di sini..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('studentsRaw') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" wire:click="closeSyncModal">Batal</button>
                            <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Import & Sync</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
