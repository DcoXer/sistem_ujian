<div wire:poll.15s class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full min-w-[920px] text-sm">
        <thead class="bg-slate-50 text-left text-slate-600">
            <tr>
                <th class="px-4 py-3 font-medium">Ujian</th>
                <th class="px-4 py-3 font-medium">Jadwal</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 font-medium">Mulai</th>
                <th class="px-4 py-3 font-medium">Mengerjakan</th>
                <th class="px-4 py-3 font-medium">Submit</th>
                <th class="px-4 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($exams as $exam)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $exam->title }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded px-2 py-1 text-[10px] font-semibold uppercase {{ $exam->phase === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ $exam->phase === 'active' ? 'aktif' : 'akan mulai' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $exam->participants_started }}</td>
                    <td class="px-4 py-3 text-amber-700">{{ $exam->participants_in_progress }}</td>
                    <td class="px-4 py-3 text-emerald-700">{{ $exam->participants_submitted }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('operator.exams.show', $exam) }}" class="rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">Belum ada ujian aktif.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

