{{-- Dashboard Admin --}}
<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white">
                <x-icon name="monitor" class="h-6 w-6" />
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Panel Admin</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Selamat datang, {{ Auth::user()->name }}</h2>
                <p class="mt-1 text-sm text-slate-600">Buat dan kelola ujian, assignment guru, serta data siswa di sini.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 sm:shrink-0">
            <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                <x-icon name="document" class="h-4 w-4" />
                Kelola Ujian
            </a>
            <a href="{{ route('admin.assignments.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                <x-icon name="users" class="h-4 w-4" />
                Assignment Guru
            </a>
        </div>
    </div>
</section>

<section
    id="kelola-ujian"
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    data-admin-dashboard-realtime-url="{{ route('admin.dashboard.realtime') }}"
>
    <div class="mb-4 flex items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <x-icon name="document" class="h-5 w-5 text-indigo-500" />
            <h3 class="text-base font-semibold text-slate-900">Ujian Terkini</h3>
        </div>
        <a href="{{ route('admin.exams.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Lihat semua →</a>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-admin-latest-exams>
        @forelse ($latestExams as $exam)
            @php
                $statusLabel = match($exam->status) {
                    'draft' => 'Draft',
                    'running' => 'Berlangsung',
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
                <a href="{{ route('admin.exams.show', $exam) }}" class="mt-auto text-xs font-semibold text-indigo-600 hover:text-indigo-700">Detail →</a>
            </article>
        @empty
            <div class="col-span-3 rounded-xl border border-dashed border-slate-200 py-8 text-center" data-admin-latest-exams-empty>
                <x-icon name="document" class="mx-auto h-8 w-8 text-slate-300" />
                <p class="mt-2 text-sm text-slate-500">Belum ada data ujian.</p>
            </div>
        @endforelse
    </div>
</section>

<section id="kelola-operator" class="grid gap-4 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <x-icon name="users" class="h-5 w-5 text-emerald-500" />
                <h3 class="text-base font-semibold text-slate-900">Operator Aktif</h3>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">Kelola User</a>
        </div>
        <div class="space-y-2">
            @forelse ($operators as $operator)
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $operator->name }}</p>
                        <p class="text-xs text-slate-500">{{ $operator->email }}</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center">
                    <x-icon name="users" class="mx-auto h-8 w-8 text-slate-300" />
                    <p class="mt-2 text-sm text-slate-500">Belum ada operator.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div id="laporan-ujian" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-2">
            <x-icon name="trophy" class="h-5 w-5 text-amber-500" />
            <h3 class="text-base font-semibold text-slate-900">Top Nilai Peserta</h3>
        </div>
        <div class="space-y-2">
            @forelse ($myScores as $score)
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-800">{{ $score->match_title }}</span>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-bold text-indigo-700">{{ (int) $score->score }}</span>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center">
                    <x-icon name="trophy" class="mx-auto h-8 w-8 text-slate-300" />
                    <p class="mt-2 text-sm text-slate-500">Belum ada data nilai.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
