<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-bold text-slate-900">Detail Ujian</h2>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
        <div>
            <x-back-button :href="route('admin.exams.index')" />
        </div>

        {{-- Exam Header Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <h3 class="text-lg font-extrabold text-slate-900">{{ $exam->title }}</h3>

                    @if ($exam->exam_type)
                        <span class="mt-1 inline-block rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ $exam->exam_type }}</span>
                    @endif

                    <div class="mt-3 space-y-1">
                        @if ($exam->start_at)
                            <div class="flex items-center gap-1.5 text-sm text-slate-600">
                                <x-icon name="clock" class="h-4 w-4 shrink-0 text-slate-400" />
                                <span>{{ $exam->start_at->format('d M Y H:i') }} &ndash; {{ $exam->end_at?->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                        <p class="text-xs text-slate-500">Guru: <span class="font-semibold text-slate-700">{{ $exam->teacher?->name ?? '-' }}</span></p>
                        <p class="text-xs text-slate-500">Mata Pelajaran: <span class="font-semibold text-slate-700">{{ $exam->subject?->name ?? '-' }}</span></p>
                        <p class="text-xs text-slate-500">Kelas Target: <span class="font-semibold text-slate-700">{{ $exam->schoolClass?->name ?? ($exam->target_grade_level ? 'Kelas '.$exam->target_grade_level.' (semua rombel)' : '-') }}</span></p>
                        <p class="text-xs text-slate-500">Tahun Ajaran: <span class="font-semibold text-slate-700">{{ $exam->schoolYear?->name ?? '-' }}</span></p>
                        <p class="text-xs text-slate-500">
                            Authoring window:
                            <span class="font-semibold text-slate-700">{{ $exam->authoring_start_at?->format('d M Y H:i') ?? '-' }} &ndash; {{ $exam->authoring_end_at?->format('d M Y H:i') ?? '-' }}</span>
                        </p>
                    </div>

                    @if ($exam->status === 'draft' && ! $lifecycle['authoring_closed'])
                        <p class="mt-2 text-xs text-amber-700">Publish dibuka setelah window authoring selesai.</p>
                    @endif
                </div>

                <div class="flex items-center gap-3 md:shrink-0">
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
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>

                    @if ($exam->status === 'draft')
                        <form method="POST" action="{{ route('admin.exams.publish', $exam) }}">
                            @csrf
                            <button
                                {{ $lifecycle['can_publish'] ? '' : 'disabled' }}
                                class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400"
                            >
                                Publish
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Lifecycle Steps --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @php
                $steps = [
                    ['label' => 'Create', 'done' => true, 'status' => 'Done'],
                    ['label' => 'Pembuatan Soal', 'done' => $questionsCount > 0, 'status' => $questionsCount > 0 ? 'Soal tersedia' : 'Menunggu guru'],
                    ['label' => 'Publish', 'done' => $lifecycle['published'], 'status' => $lifecycle['published'] ? 'Done' : 'Pending'],
                    ['label' => 'Attempt', 'done' => $lifecycle['has_attempt'], 'status' => $lifecycle['has_attempt'] ? 'Started' : 'None yet'],
                    ['label' => 'Result', 'done' => false, 'status' => 'Lihat laporan'],
                ];
            @endphp
            @foreach ($steps as $i => $step)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Step {{ $i + 1 }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $step['label'] }}</p>
                    <p class="mt-0.5 text-xs {{ $step['done'] ? 'text-emerald-700' : 'text-amber-600' }}">{{ $step['status'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Audit Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-2">
                <x-icon name="info" class="h-5 w-5 text-slate-400" />
                <h4 class="font-semibold text-slate-900">Laporan Audit Hasil Ujian</h4>
            </div>
            <p class="mt-2 text-sm text-slate-600">Laporan audit akan muncul setelah ujian selesai.</p>
            <p class="mt-1 text-xs text-slate-500">Total soal tersimpan: <span class="font-semibold text-slate-700">{{ $questionsCount }}</span></p>
        </div>

        @if ($exam->status === 'finished')
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h4 class="font-semibold text-slate-900">Laporan Audit Operator</h4>
                        <p class="mt-1 text-sm text-slate-500">Memantau aksi operator seperti re-open attempt, force submit, dan manual scoring beserta alasannya.</p>
                    </div>
                    <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Exam Selesai</span>
                </div>

                @if (!empty($auditReport))
                    <div class="mb-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-slate-500">Total Attempt</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $auditReport['total_attempts'] }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-amber-700">Force Submit</p>
                            <p class="mt-1 text-lg font-bold text-amber-800">{{ $auditReport['force_submit_count'] }}</p>
                        </div>
                        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-indigo-700">Re-open</p>
                            <p class="mt-1 text-lg font-bold text-indigo-800">{{ $auditReport['reopen_count'] }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-emerald-700">Manual Score</p>
                            <p class="mt-1 text-lg font-bold text-emerald-800">{{ $auditReport['manual_score_count'] }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-slate-500">Issue Log</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $auditReport['issue_count'] }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @php
                            $actionLabels = [
                                'force_submit' => 'Force Submit',
                                'reopen_attempt' => 'Re-open Attempt',
                                'manual_score' => 'Manual Score',
                                'mark_issue' => 'Issue Log',
                            ];
                        @endphp
                        @forelse ($auditReport['timeline'] as $audit)
                            <article class="rounded-xl border border-slate-200 p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                        {{ $actionLabels[$audit['action']] ?? strtoupper($audit['action']) }}
                                    </span>
                                    <span class="text-xs text-slate-500">{{ $audit['created_at']?->format('d M Y H:i:s') }}</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-700">
                                    Peserta: <span class="font-semibold text-slate-900">{{ $audit['participant_name'] }}</span>
                                    <span class="text-xs text-slate-500">({{ $audit['participant_email'] }})</span>
                                </p>
                                <p class="mt-1 text-sm text-slate-700">
                                    Actor: <span class="font-semibold text-slate-900">{{ $audit['actor_name'] }}</span>
                                    <span class="text-xs uppercase text-slate-500">({{ $audit['actor_role'] }})</span>
                                </p>
                                <p class="mt-1 text-sm text-slate-700">Alasan: <span class="font-medium">{{ $audit['reason'] ?: '-' }}</span></p>
                                @if (!empty($audit['meta']))
                                    <div class="mt-2 overflow-x-auto rounded-lg bg-slate-50 p-2">
                                        <p class="break-all text-xs text-slate-500">Meta: {{ json_encode($audit['meta']) }}</p>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-slate-500">Tidak ada aksi operator tercatat untuk exam ini.</p>
                        @endforelse
                    </div>
                @else
                    <p class="text-sm text-slate-500">Data audit belum tersedia (tabel audit tidak ditemukan atau belum ada data).</p>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
