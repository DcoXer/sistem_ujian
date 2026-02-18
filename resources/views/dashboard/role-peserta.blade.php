<section id="mulai-ujian" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Mulai Ujian</h2>
    @if ($activeExam)
        <p class="mt-2 text-sm text-slate-600">Ujian aktif saat ini:</p>
        <p class="mt-1 text-base font-semibold text-slate-900">{{ $activeExam->title }}</p>
        <a href="{{ route('peserta.exams.index') }}" class="mt-4 inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Mulai / Lanjut Ujian
        </a>
    @else
        @if (!empty($activeExamExpiredMessage))
            <p class="mt-2 text-sm text-amber-700">{{ $activeExamExpiredMessage }}</p>
            @if (!empty($upcomingExam))
                <p class="mt-2 text-xs text-slate-500">Ujian terdekat: {{ $upcomingExam->title }} ({{ $upcomingExam->start_at?->format('d M Y H:i') }})</p>
            @endif
        @else
            <p class="mt-2 text-sm text-amber-700">Belum ada ujian aktif. Tunggu info jadwal selanjutnya.</p>
        @endif
    @endif
</section>

<section id="jawab-soal" class="grid gap-4 lg:grid-cols-2">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Sesi Pengerjaan</h3>
        @if ($myInProgressAttempt)
            <p class="mt-2 text-sm text-slate-600">Anda masih punya attempt aktif. Lanjutkan dari jawaban terakhir.</p>
            <a href="{{ route('peserta.exams.show', $myInProgressAttempt->id) }}" class="mt-4 inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Lanjut Kerjakan
            </a>
        @else
            <p class="mt-2 text-sm text-slate-600">Belum ada attempt aktif untuk saat ini.</p>
            <a href="{{ route('peserta.exams.index') }}" class="mt-4 inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                Buka Daftar Ujian
            </a>
        @endif
    </article>

    <article id="nilai-saya" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="text-base font-semibold text-slate-900">Nilai Saya</h3>
            @if ($myLatestSubmittedAttempt)
                <a href="{{ route('peserta.exams.result', $myLatestSubmittedAttempt->id) }}" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                    Hasil Terakhir
                </a>
            @endif
        </div>
        <div class="space-y-2">
            @forelse ($myScores as $score)
                <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <span class="text-slate-800">{{ $score->match_title }}</span>
                    <span class="font-semibold text-slate-900">{{ (int) $score->score }} pts</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada nilai untuk akun ini.</p>
            @endforelse
        </div>
    </article>
</section>
