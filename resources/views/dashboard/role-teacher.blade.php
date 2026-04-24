{{-- Dashboard Guru --}}
<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white">
                <x-icon name="academic-cap" class="h-6 w-6" />
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Panel Guru</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Selamat datang, {{ Auth::user()->name }}</h2>
                <p class="mt-1 text-sm text-slate-600">Buat dan kelola soal ujian yang ditugaskan kepadamu di sini.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 sm:shrink-0">
            <a href="{{ route('teacher.exams.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                <x-icon name="document" class="h-4 w-4" />
                Kelola Soal
            </a>
            @can('view-homeroom-results')
                <a href="{{ route('teacher.homeroom.results.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                    <x-icon name="list" class="h-4 w-4" />
                    Nilai Wali Kelas
                </a>
            @endcan
        </div>
    </div>
</section>

<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center gap-2">
        <x-icon name="clock" class="h-5 w-5 text-amber-500" />
        <h3 class="text-base font-semibold text-slate-900">Ujian yang Perlu Dikerjakan</h3>
    </div>

    @if ($latestExams->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center">
            <x-icon name="check-circle" class="mx-auto h-8 w-8 text-slate-300" />
            <p class="mt-2 text-sm text-slate-500">Tidak ada ujian yang perlu dikerjakan saat ini.</p>
        </div>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestExams as $exam)
                @php
                    $isDraft = $exam->status === 'draft';
                    $isRunning = $exam->status === 'running';
                    $isFinished = $exam->status === 'finished';
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
                <article class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-900">{{ $exam->title }}</p>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    @if ($exam->start_at)
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <x-icon name="clock" class="h-3.5 w-3.5" />
                            <span>{{ $exam->start_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-4">
            <a href="{{ route('teacher.exams.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                Lihat semua ujian →
            </a>
        </div>
    @endif
</section>

<div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
    <div class="flex items-start gap-3">
        <x-icon name="info" class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" />
        <div class="text-sm text-blue-800">
            <p class="font-semibold">Cara kerja pembuatan soal:</p>
            <ol class="mt-1 list-decimal space-y-1 pl-4">
                <li>Admin membuat ujian dan menentukan jadwal pembuatan soal (authoring window).</li>
                <li>Kamu hanya bisa membuat/mengedit soal dalam rentang waktu tersebut.</li>
                <li>Setelah waktu habis, soal dikunci dan ujian berjalan otomatis.</li>
            </ol>
        </div>
    </div>
</div>
