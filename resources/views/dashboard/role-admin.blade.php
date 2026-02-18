<section
    id="kelola-ujian"
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    data-admin-dashboard-realtime-url="{{ route('admin.dashboard.realtime') }}"
>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Kelola Ujian</h2>
            <p class="text-sm text-slate-500">Buat draft ujian, review soal dari author, lalu publish.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Lihat Semua</a>
        </div>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-admin-latest-exams>
        @forelse ($latestExams as $exam)
            <article class="rounded-xl border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-semibold text-slate-900">{{ $exam->title }}</p>
                    <span class="rounded bg-slate-100 px-2 py-1 text-[10px] font-semibold uppercase text-slate-700">{{ $exam->status }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</p>
            </article>
        @empty
            <p class="text-sm text-slate-500" data-admin-latest-exams-empty>Belum ada data ujian.</p>
        @endforelse
    </div>
</section>

<section id="kelola-operator" class="grid gap-4 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900">Operator Aktif</h3>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">Kelola User</a>
        </div>
        <div class="space-y-2">
            @forelse ($operators as $operator)
                <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $operator->name }}</p>
                        <p class="text-xs text-slate-500">{{ $operator->email }}</p>
                    </div>
                    <span class="rounded bg-emerald-50 px-2 py-1 text-[10px] font-semibold uppercase text-emerald-700">Aktif</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada operator.</p>
            @endforelse
        </div>
    </div>

    <div id="laporan-ujian" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-base font-semibold text-slate-900">Top Nilai Peserta</h3>
        <div class="space-y-2">
            @forelse ($myScores as $score)
                <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <span class="text-slate-800">{{ $score->match_title }}</span>
                    <span class="font-semibold text-slate-900">{{ (int) $score->score }} pts</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada data nilai.</p>
            @endforelse
        </div>
    </div>
</section>
