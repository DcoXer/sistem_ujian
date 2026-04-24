<div class="space-y-4">
    <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
        <x-icon name="info" class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" />
        <div class="text-sm text-blue-800">
            <p>Halo <strong>{{ Auth::user()->name }}</strong>, berikut daftar ujian yang ditugaskan kepadamu.</p>
            <p class="mt-1 text-xs text-blue-700">Kamu hanya bisa membuat/mengedit soal dalam <strong>rentang waktu authoring</strong> yang ditentukan admin. Di luar waktu itu, soal terkunci.</p>
        </div>
    </div>

    @if ($exams->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white py-12 text-center">
            <x-icon name="document" class="mx-auto h-10 w-10 text-slate-300" />
            <p class="mt-3 text-sm font-medium text-slate-500">Belum ada ujian yang ditugaskan kepadamu.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($exams as $exam)
                @php
                    $canAuthorManage = $exam->status === 'draft' && $exam->isWithinAuthoringWindow();
                    $canAuthorViewFinished = $exam->status === 'finished';
                    $statusLabel = match($exam->status) {
                        'draft' => 'Persiapan',
                        'running' => 'Sedang Berlangsung',
                        'finished' => 'Selesai',
                        default => $exam->status,
                    };
                    $statusClass = match($exam->status) {
                        'draft' => 'bg-amber-100 text-amber-800',
                        'running' => 'bg-emerald-100 text-emerald-800',
                        'finished' => 'bg-slate-100 text-slate-600',
                        default => 'bg-slate-100 text-slate-600',
                    };
                @endphp

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-slate-900">{{ $exam->title }}</h3>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs text-slate-600">{{ $exam->questions_count }} soal</span>
                            </div>

                            <div class="mt-2 space-y-1">
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <x-icon name="clock" class="h-3.5 w-3.5" />
                                        Ujian: {{ $exam->start_at?->format('d M Y, H:i') ?? '-' }} – {{ $exam->end_at?->format('d M Y, H:i') ?? '-' }} WIB
                                    </span>
                                </div>
                                @if ($exam->authoring_start_at || $exam->authoring_end_at)
                                    <div class="flex flex-wrap items-center gap-1 text-xs {{ $canAuthorManage ? 'text-amber-700' : 'text-slate-400' }}">
                                        <x-icon name="pencil" class="h-3.5 w-3.5" />
                                        Batas buat soal: {{ $exam->authoring_start_at?->format('d M Y, H:i') ?? '-' }} – {{ $exam->authoring_end_at?->format('d M Y, H:i') ?? '-' }} WIB
                                        @if ($canAuthorManage)
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-800">Aktif sekarang</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="shrink-0">
                            @if ($canAuthorManage)
                                <a href="{{ route('teacher.exams.show', $exam) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                    <x-icon name="pencil" class="h-4 w-4" />
                                    Buat / Edit Soal
                                </a>
                            @elseif ($canAuthorViewFinished)
                                <a href="{{ route('teacher.exams.show', $exam) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <x-icon name="document" class="h-4 w-4" />
                                    Lihat Soal
                                </a>
                            @else
                                <button type="button" disabled class="inline-flex cursor-not-allowed items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400">
                                    <x-icon name="lock" class="h-4 w-4" />
                                    Soal Terkunci
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div>
            {{ $exams->links() }}
        </div>
    @endif
</div>
