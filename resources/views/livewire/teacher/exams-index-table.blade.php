<div class="space-y-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-slate-600">Halo <b>{{ Auth::user()->name }}</b>, panel ini untuk kelola soal ujian dari assignment admin.</p>
        <p class="mt-2 text-xs text-slate-500 italic">Teacher hanya bisa buat/edit soal di rentang waktu authoring.</p>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[920px] text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Judul</th>
                    <th class="px-4 py-3 font-medium">Jadwal</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Soal</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $exam->title }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            <p>{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</p>
                            <p class="mt-1 text-xs text-slate-500">Authoring: {{ $exam->authoring_start_at?->format('d M Y H:i') ?? '-' }} - {{ $exam->authoring_end_at?->format('d M Y H:i') ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs uppercase {{ $exam->status === 'draft' ? 'bg-amber-100 text-amber-800' : ($exam->status === 'running' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">{{ $exam->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $exam->questions_count }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php($canAuthorManage = $exam->status === 'draft' && $exam->isWithinAuthoringWindow())
                            @php($canAuthorViewFinished = $exam->status === 'finished')
                            @if ($canAuthorManage)
                                <a href="{{ route('teacher.exams.show', $exam) }}" class="rounded border border-indigo-200 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Buat Soal</a>
                            @elseif ($canAuthorViewFinished)
                                <a href="{{ route('teacher.exams.show', $exam) }}" class="rounded border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Lihat Soal</a>
                            @else
                                <button type="button" disabled class="cursor-not-allowed rounded border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-400">Terkunci</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data exam.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $exams->links() }}
</div>

