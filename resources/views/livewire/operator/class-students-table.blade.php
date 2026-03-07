<div class="space-y-4">
    <div class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_320px]">
        <div>
            <label for="operator-student-search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari siswa</label>
            <input
                id="operator-student-search"
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Nama, email, NIS, atau NISN"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
        </div>
        <div>
            <label for="operator-exam-filter" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Pilih ujian</label>
            <select
                id="operator-exam-filter"
                wire:model.live="examId"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
                @forelse ($availableExams as $exam)
                    <option value="{{ $exam->id }}">
                        {{ $exam->title }} - {{ $exam->subject?->name ?? 'Tanpa Mapel' }}
                    </option>
                @empty
                    <option value="">Belum ada ujian berjalan untuk kelas ini</option>
                @endforelse
            </select>
            @if ($examId)
                <a href="{{ route('operator.exams.show', $examId) }}" class="mt-2 inline-flex rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                    Buka Monitor Ujian
                </a>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1120px] text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-3 py-2 font-medium">Nama</th>
                    <th class="px-3 py-2 font-medium">NIS / NISN</th>
                    <th class="px-3 py-2 font-medium">Email</th>
                    <th class="px-3 py-2 font-medium">Status</th>
                    <th class="px-3 py-2 font-medium">Skor</th>
                    <th class="px-3 py-2 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    @php($attempt = $attemptsByUserId->get($student->id))
                    <tr class="border-t border-slate-100 align-top">
                        <td class="px-3 py-2 font-medium text-slate-900">{{ $student->name }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $student->email }}</td>
                        <td class="px-3 py-2">
                            <span class="rounded px-2 py-1 text-[10px] font-semibold uppercase {{ $attempt ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $attempt?->status ?? 'belum mulai' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-slate-700">{{ $attempt?->score ?? '-' }}</td>
                        <td class="px-3 py-2">
                            @if (! $examId)
                                <span class="text-xs text-slate-500">Pilih ujian dulu.</span>
                            @elseif (! $attempt)
                                <span class="text-xs text-slate-500">Belum ada attempt.</span>
                            @else
                                <div class="grid gap-2">
                                    @if ($attempt->status === \App\Models\ExamAttempt::STATUS_ACTIVE)
                                        <form method="POST" action="{{ route('operator.exams.force-submit', $attempt->id) }}" class="grid grid-cols-[1fr_auto] gap-2">
                                            @csrf
                                            <input type="text" name="reason" class="rounded border border-slate-300 px-2 py-1.5 text-xs" placeholder="Alasan force submit" required>
                                            <button class="rounded bg-amber-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">Force</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('operator.exams.reopen', $attempt->id) }}" class="grid grid-cols-[1fr_auto] gap-2">
                                            @csrf
                                            <input type="text" name="reason" class="rounded border border-slate-300 px-2 py-1.5 text-xs" placeholder="Alasan reopen" required>
                                            <button class="rounded bg-indigo-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">Reopen</button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('operator.exams.mark-issue', $attempt->id) }}" class="grid grid-cols-[1fr_auto] gap-2">
                                        @csrf
                                        <input type="text" name="reason" class="rounded border border-slate-300 px-2 py-1.5 text-xs" placeholder="Kendala teknis" required>
                                        <button class="rounded bg-slate-700 px-2 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">Issue</button>
                                    </form>

                                    <form method="POST" action="{{ route('operator.exams.manual-score', $attempt->id) }}" class="grid grid-cols-[72px_1fr_auto] gap-2">
                                        @csrf
                                        <input type="hidden" name="intent" value="manual_essay_scoring">
                                        <input type="number" name="score" min="0" class="rounded border border-slate-300 px-2 py-1.5 text-xs" placeholder="Nilai" required>
                                        <input type="text" name="reason" class="rounded border border-slate-300 px-2 py-1.5 text-xs" placeholder="Alasan manual score" required>
                                        <button class="rounded bg-emerald-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Skor</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-slate-500">Belum ada siswa pada kelas ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

