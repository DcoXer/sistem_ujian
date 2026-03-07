<div class="space-y-4">
    @if ($assignedClasses->isEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Belum ada assignment wali kelas untuk akun Anda.
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <form wire:submit="export" class="grid gap-3 sm:grid-cols-[1fr_auto]">
                <select wire:model="exportFormat" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="excel">Excel (Microsoft)</option>
                    <option value="pdf">PDF</option>
                </select>
                <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Download</button>
            </form>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
            Kelas wali: <span class="font-semibold">{{ $assignedClasses->count() }}</span> |
            Siswa terekam: <span class="font-semibold">{{ $summaryByStudent->count() }}</span> |
            Attempt nilai: <span class="font-semibold">{{ $rows->count() }}</span>
        </div>

        <div class="-mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Kelas</th>
                            <th class="px-4 py-3 font-medium">NIS</th>
                            <th class="px-4 py-3 font-medium">Siswa</th>
                            <th class="px-4 py-3 font-medium">Mapel</th>
                            <th class="px-4 py-3 font-medium">Ujian</th>
                            <th class="px-4 py-3 font-medium">Skor</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Submit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3 text-slate-700">{{ $row->class_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $row->student_nis ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $row->student_name }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $row->subject_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $row->exam_title }}</td>
                                <td class="px-4 py-3 text-slate-900">{{ $row->score !== null ? $row->score : '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded px-2 py-1 text-[10px] uppercase {{ $row->attempt_status === 'finished' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $row->attempt_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $row->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">Belum ada data hasil ujian untuk kelas wali Anda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

