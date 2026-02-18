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

<section id="daftar-peserta" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="mb-3 text-base font-semibold text-slate-900">Daftar Peserta</h3>
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($pesertaUsers as $peserta)
            <article class="rounded-lg border border-slate-200 p-3">
                <p class="text-sm font-medium text-slate-900">{{ $peserta->name }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $peserta->email }}</p>
            </article>
        @empty
            <p class="text-sm text-slate-500">Belum ada peserta.</p>
        @endforelse
    </div>
</section>


