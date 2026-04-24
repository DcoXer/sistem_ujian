<div class="space-y-4">
    {{-- Toolbar --}}
    <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-2">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul ujian..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm sm:max-w-xs">
            <select wire:model.live="filterSemesterId" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Semester</option>
                @foreach ($semesters as $sem)
                    <option value="{{ $sem->id }}">{{ $sem->schoolYear?->name }} - {{ $sem->name }}{{ $sem->is_active ? ' ★' : '' }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterExamType" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Tipe</option>
                @foreach ($examTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button wire:click="openBulkCreateModal" type="button" class="rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">Buat UTS/UAS Massal</button>
            <button wire:click="openCreateModal" type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Buat Ujian</button>
        </div>
    </div>

    @error('publish') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
    @error('delete') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[900px] text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Judul</th>
                    <th class="px-4 py-3 font-medium">Tipe</th>
                    <th class="px-4 py-3 font-medium">Guru / Tingkat</th>
                    <th class="px-4 py-3 font-medium">Jadwal Ujian</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            {{ $exam->title }}
                            @if ($exam->semester)
                                <p class="text-xs text-slate-500">{{ $exam->semester->name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($exam->exam_type)
                                <span class="rounded bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ $exam->exam_type }}</span>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $exam->teacher?->name ?? '-' }}
                            <p class="text-xs text-slate-500">Kelas {{ $exam->target_grade_level ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">
                            <span class="text-emerald-600">{{ $exam->start_at?->format('d M Y H:i') }}</span>
                            <span class="text-slate-400"> s/d </span>
                            <span class="text-emerald-600">{{ $exam->end_at?->format('d M Y H:i') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span @class([
                                'rounded px-2 py-0.5 text-xs font-semibold uppercase',
                                'bg-slate-100 text-slate-600' => $exam->status === 'draft',
                                'bg-emerald-50 text-emerald-700' => $exam->status === 'running',
                                'bg-indigo-50 text-indigo-700' => $exam->status === 'finished',
                            ])>{{ $exam->status }}</span>
                        </td>
                        <td class="space-x-1 px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('admin.exams.show', $exam) }}" class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Detail</a>
                            @if ($exam->status === 'draft')
                                <button wire:click="deleteExam({{ $exam->id }})" data-confirm-message="Hapus exam draft ini?" type="button" class="rounded border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">Hapus</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada ujian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $exams->links() }}

    {{-- Modal: Buat Ujian Tunggal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeCreateModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="flex h-[calc(100vh-2rem)] w-full max-w-2xl flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex shrink-0 items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Buat Ujian</h3>
                        <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100" wire:click="closeCreateModal">Tutup</button>
                    </div>
                    <form wire:submit="createExam" class="flex-1 space-y-4 overflow-y-auto pr-1 [scrollbar-width:none]">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Judul</label>
                            <input wire:model="title" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Semester</label>
                                <select wire:model="semester_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">Tanpa semester</option>
                                    @foreach ($semesters as $sem)
                                        <option value="{{ $sem->id }}">{{ $sem->schoolYear?->name }} - {{ $sem->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Tipe Ujian</label>
                                <select wire:model="exam_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">Pilih tipe</option>
                                    @foreach ($examTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Mulai Ujian</label><input type="datetime-local" wire:model="start_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('start_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Selesai Ujian</label><input type="datetime-local" wire:model="end_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('end_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Mulai Input Soal</label><input type="datetime-local" wire:model="authoring_start_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('authoring_start_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Batas Input Soal</label><input type="datetime-local" wire:model="authoring_end_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('authoring_end_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        </div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-700">Durasi (menit)</label><input type="number" min="1" wire:model="duration_minutes" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('duration_minutes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Guru Pembuat Soal</label>
                            <select wire:model="teacher_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                <option value="">Pilih Guru</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Mapel</label><select wire:model="subject_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Pilih Mapel</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>@endforeach</select></div>
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Tingkat Kelas</label><select wire:model="target_grade_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Semua</option>@foreach ($gradeLevels as $g)<option value="{{ $g }}">Kelas {{ $g }}</option>@endforeach</select></div>
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Tahun Ajaran</label><select wire:model="school_year_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Aktif</option>@foreach ($schoolYears as $sy)<option value="{{ $sy->id }}">{{ $sy->name }}{{ $sy->is_active ? ' ★' : '' }}</option>@endforeach</select></div>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100" wire:click="closeCreateModal">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan Draft</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Bulk Create UTS/UAS (regular form — bukan Livewire submit) --}}
    @if ($showBulkCreateModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeBulkCreateModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="flex h-[calc(100vh-2rem)] w-full max-w-2xl flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex shrink-0 items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Buat UTS/UAS Massal</h3>
                            <p class="mt-0.5 text-xs text-slate-500">Sistem akan otomatis buat ujian per mapel berdasarkan assignment guru di tingkat kelas yang dipilih.</p>
                        </div>
                        <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100" wire:click="closeBulkCreateModal">Tutup</button>
                    </div>
                    <form
                        method="POST"
                        action="{{ route('admin.exams.bulk-create.store') }}"
                        class="flex-1 space-y-4 overflow-y-auto pr-1 [scrollbar-width:none]"
                    >
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Semester <span class="text-rose-500">*</span></label>
                                <select name="semester_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                    <option value="">Pilih Semester</option>
                                    @foreach ($semesters as $sem)
                                        <option value="{{ $sem->id }}" {{ $sem->is_active ? 'selected' : '' }}>{{ $sem->schoolYear?->name }} - {{ $sem->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Tipe Ujian <span class="text-rose-500">*</span></label>
                                <select name="exam_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                    @foreach ($examTypes as $type)
                                        <option value="{{ $type }}" {{ $type === 'UTS' ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Tingkat Kelas <span class="text-rose-500">*</span></label>
                            <select name="target_grade_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                <option value="">Pilih Tingkat</option>
                                @foreach ($gradeLevels as $g)
                                    <option value="{{ $g }}">Kelas {{ $g }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Semua rombel di tingkat ini akan otomatis memakai soal yang sama.</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <p class="mb-2 text-xs font-medium text-slate-600">Jadwal Input Soal (Guru)</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div><label class="mb-1 block text-xs text-gray-600">Mulai Input Soal</label><input type="datetime-local" name="authoring_start_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                                <div><label class="mb-1 block text-xs text-gray-600">Batas Input Soal</label><input type="datetime-local" name="authoring_end_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                            </div>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <p class="mb-2 text-xs font-medium text-slate-600">Jadwal Ujian Siswa</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div><label class="mb-1 block text-xs text-gray-600">Mulai Ujian</label><input type="datetime-local" name="start_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                                <div><label class="mb-1 block text-xs text-gray-600">Selesai Ujian</label><input type="datetime-local" name="end_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Durasi per Mapel (menit) <span class="text-rose-500">*</span></label>
                            <input type="number" name="duration_minutes" value="90" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        </div>
                        @if ($errors->has('bulk_create'))
                            <p class="text-xs text-rose-600">{{ $errors->first('bulk_create') }}</p>
                        @endif
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100" wire:click="closeBulkCreateModal">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Buat Semua Ujian</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
