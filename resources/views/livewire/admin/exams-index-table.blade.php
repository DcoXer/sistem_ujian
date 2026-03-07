<div class="space-y-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul ujian..." class="w-full sm:max-w-md rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button wire:click="openCreateModal" type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Buat Ujian</button>
    </div>

    @error('publish') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
    @error('delete') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1100px] text-sm">
            <thead class="bg-slate-50 text-center text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-bold">Judul</th>
                    <th class="px-4 py-3 font-bold">Guru</th>
                    <th class="px-4 py-3 font-bold">Jadwal Ujian</th>
                    <th class="px-4 py-3 font-bold">Status</th>
                    <th class="px-4 py-3 font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $exam->title }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $exam->teacher?->name ?? '-' }}
                            <p class="text-xs text-slate-500"> Kelas {{ $exam->target_grade_level ?? '-' }} | {{ $exam->schoolYear?->name ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs">
                            Mulai Ujian : <span class="text-green-500"><br>{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</span><br>
                            Pembuatan Soal : <span class="text-red-500"><br>{{ $exam->authoring_start_at?->format('d M Y H:i') ?? '-' }} - {{ $exam->authoring_end_at?->format('d M Y H:i') ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3"><span class="rounded px-2 py-1 text-xs uppercase bg-slate-100 text-slate-700">{{ $exam->status }}</span></td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('admin.exams.show', $exam) }}" class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Detail</a>
                            @if ($exam->status === 'draft')
                                <button wire:click="deleteExam({{ $exam->id }})" data-confirm-message="Hapus exam draft ini?" type="button" class="rounded border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">Hapus</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">Belum ada exam.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $exams->links() }}

    @if ($showCreateModal)
        <div class="fixed inset-0 z-[105]">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeCreateModal"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="flex h-[calc(100vh-2rem)] w-full max-w-2xl flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex shrink-0 items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Buat Ujian</h3>
                        <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100" wire:click="closeCreateModal">Tutup</button>
                    </div>
                    <form wire:submit="createExam" class="flex-1 space-y-4 overflow-y-auto pr-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Judul</label>
                            <input wire:model="title" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Mulai</label><input type="datetime-local" wire:model="start_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('start_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Selesai</label><input type="datetime-local" wire:model="end_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('end_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Mulai Authoring</label><input type="datetime-local" wire:model="authoring_start_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('authoring_start_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Selesai Authoring</label><input type="datetime-local" wire:model="authoring_end_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('authoring_end_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        </div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-700">Durasi (menit)</label><input type="number" min="1" wire:model="duration_minutes" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>@error('duration_minutes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-700">Teacher</label><select wire:model="teacher_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required><option value="">Pilih teacher</option>@foreach ($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>@endforeach</select>@error('teacher_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Mapel</label><select wire:model="subject_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Umum</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>@endforeach</select></div>
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Tingkat</label><select wire:model="target_grade_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Semua tingkat</option>@foreach ($gradeLevels as $gradeLevel)<option value="{{ $gradeLevel }}">Tingkat {{ $gradeLevel }}</option>@endforeach</select></div>
                            <div><label class="mb-1 block text-sm font-medium text-gray-700">Tahun Ajaran</label><select wire:model="school_year_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Aktif</option>@foreach ($schoolYears as $schoolYear)<option value="{{ $schoolYear->id }}">{{ $schoolYear->name }}{{ $schoolYear->is_active ? ' (aktif)' : '' }}</option>@endforeach</select></div>
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
</div>
