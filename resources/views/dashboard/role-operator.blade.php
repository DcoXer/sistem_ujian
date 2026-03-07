<section id="ujian-aktif" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Monitoring Ujian</h2>
            <p class="text-sm text-slate-500">Pantau peserta dan lakukan aksi teknis dari panel operator.</p>
        </div>
        <a href="{{ route('operator.exams.index') }}" class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-slate-100 text-slate-800 hover:text-slate-600 hover:bg-slate-200">
            Buka Monitoring
        </a>
    </div>

    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
        @if ($activeExam)
            <p class="text-sm text-slate-600">Ujian aktif saat ini</p>
            <p class="mt-1 text-base font-semibold text-slate-900">{{ $activeExam->title }}</p>
            <a href="{{ route('operator.exams.show', $activeExam->id) }}" class="mt-3 inline-flex rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                Lihat Detail Ujian
            </a>
        @else
            <p class="text-sm text-amber-700">Belum ada ujian aktif saat ini.</p>
        @endif
    </div>
</section>

<section id="daftar-kelas" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="mb-3 text-base font-semibold text-slate-900">Daftar Kelas</h3>
    <p class="mb-4 text-sm text-slate-500">Klik tombol Buka untuk masuk ke halaman siswa per kelas dalam format tabel Livewire.</p>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full min-w-[680px] text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Kelas</th>
                    <th class="px-4 py-3 font-medium">Tingkat</th>
                    <th class="px-4 py-3 font-medium">Jumlah Siswa</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($operatorClasses as $class)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $class->name }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $class->grade_level ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $class->students_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('operator.classes.students.index', $class->id) }}" class="rounded border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                Buka
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-5 text-center text-slate-500">Belum ada data kelas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
