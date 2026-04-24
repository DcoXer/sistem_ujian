{{-- Dashboard Siswa --}}
@if ($activeExam)
    <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
                <x-icon name="play" class="h-6 w-6" />
            </div>
            <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Ujian Sedang Berlangsung</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $activeExam->title }}</h2>
                <p class="mt-1 text-sm text-slate-600">Ujian ini sudah dibuka. Segera kerjakan sebelum waktu habis.</p>
                <a href="{{ route('student.exams.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    <x-icon name="arrow-right" class="h-4 w-4" />
                    Kerjakan Sekarang
                </a>
            </div>
        </div>
    </section>
@elseif ($myInProgressAttempt)
    <section class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white">
                <x-icon name="document" class="h-6 w-6" />
            </div>
            <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Ujian Belum Selesai</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Ada ujian yang masih berlangsung</h2>
                <p class="mt-1 text-sm text-slate-600">Kamu belum menyelesaikan ujian. Lanjutkan dari jawaban terakhir.</p>
                <a href="{{ route('student.exams.show', $myInProgressAttempt->id) }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                    <x-icon name="arrow-right" class="h-4 w-4" />
                    Lanjut Mengerjakan
                </a>
            </div>
        </div>
    </section>
@else
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-slate-500">
                <x-icon name="clock" class="h-6 w-6" />
            </div>
            <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status Ujian</p>
                @if (!empty($activeExamExpiredMessage))
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Waktu ujian sudah habis</h2>
                    <p class="mt-1 text-sm text-amber-700">{{ $activeExamExpiredMessage }}</p>
                    @if (!empty($upcomingExam))
                        <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs text-slate-500">Ujian berikutnya:</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $upcomingExam->title }}</p>
                            <p class="text-xs text-slate-500">{{ $upcomingExam->start_at?->format('d M Y, H:i') }} WIB</p>
                        </div>
                    @endif
                @else
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Tidak ada ujian aktif</h2>
                    <p class="mt-1 text-sm text-slate-600">Belum ada ujian yang dibuka saat ini. Tunggu informasi jadwal dari guru.</p>
                @endif
                <a href="{{ route('student.exams.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <x-icon name="list" class="h-4 w-4" />
                    Lihat Semua Ujian
                </a>
            </div>
        </div>
    </section>
@endif

<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <x-icon name="trophy" class="h-5 w-5 text-amber-500" />
            <h3 class="text-base font-semibold text-slate-900">Nilai Terakhirku</h3>
        </div>
        @if ($myLatestSubmittedAttempt)
            <a href="{{ route('student.exams.result', $myLatestSubmittedAttempt->id) }}" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                Detail Hasil
            </a>
        @endif
    </div>

    @if ($myScores->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center">
            <x-icon name="document" class="mx-auto h-8 w-8 text-slate-300" />
            <p class="mt-2 text-sm text-slate-500">Belum ada nilai. Kerjakan ujian dulu ya!</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($myScores as $score)
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-800">{{ $score->match_title }}</span>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-700">{{ (int) $score->score }}</span>
                </div>
            @endforeach
        </div>
    @endif
</section>
