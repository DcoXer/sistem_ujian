<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Detail Exam</h2>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
        <div>
            <x-back-button :href="route('admin.exams.index')" />
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <h3 class="text-lg font-extrabold text-gray-900">{{ $exam->title }}</h3>
                    <p class="mt-1 text-sm font-semibold text-gray-600">{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</p>
                    <p class="mt-1 text-xs text-slate-500">Teacher soal: <br>
                        <b>{{ $exam->teacher?->name ?? '-' }}</b>
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Mata Pelajaran: <b>{{ $exam->subject?->name ?? '-' }}</b></p>
                    <p class="mt-1 text-xs text-slate-500">Kelas Target: <b>{{ $exam->schoolClass?->name ?? '-' }}</b></p>
                    <p class="mt-1 text-xs text-slate-500">Tahun Ajaran: <b>{{ $exam->schoolYear?->name ?? '-' }}</b></p>
                    <p class="mt-1 text-xs text-slate-500">Batas waktu pembuatan soal:<br> 
                        <b>{{ $exam->authoring_start_at?->format('d M Y H:i') ?? '-' }} - {{ $exam->authoring_end_at?->format('d M Y H:i') ?? '-' }}</b>
                    </p>
                    @if ($exam->status === 'draft' && ! $lifecycle['authoring_closed'])
                        <p class="mt-1 text-xs text-amber-700">Publish dibuka setelah window authoring selesai.</p>
                    @endif
                </div>
                <div class="flex items-center gap-3 md:shrink-0">
                    <span class="rounded bg-gray-100 px-2 py-1 text-xs uppercase text-gray-700">{{ $exam->status }}</span>
                    @if ($exam->status === 'draft')
                        <form method="POST" action="{{ route('admin.exams.publish', $exam) }}">
                            @csrf
                            <button {{ $lifecycle['can_publish'] ? '' : 'disabled' }} class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-gray-300">
                                Publish
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-lg border border-gray-200 bg-white p-3">
                <p class="text-xs uppercase text-gray-500">Step 1</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">Create</p>
                <p class="text-xs text-emerald-700">Done</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-3">
                <p class="text-xs uppercase text-gray-500">Step 2</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">Pembuatan Soal</p>
                <p class="text-xs {{ $questionsCount > 0 ? 'text-emerald-700' : 'text-amber-700' }}">{{ $questionsCount > 0 ? 'Soal tersedia' : 'Menunggu teacher' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-3">
                <p class="text-xs uppercase text-gray-500">Step 3</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">Publish</p>
                <p class="text-xs {{ $lifecycle['published'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $lifecycle['published'] ? 'Done' : 'Pending' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-3">
                <p class="text-xs uppercase text-gray-500">Step 4</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">Attempt</p>
                <p class="text-xs {{ $lifecycle['has_attempt'] ? 'text-emerald-700' : 'text-gray-500' }}">{{ $lifecycle['has_attempt'] ? 'Started' : 'None yet' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-3">
                <p class="text-xs uppercase text-gray-500">Step 5</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">Result</p>
                <p class="text-xs text-gray-500">View in report</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h4 class="font-semibold text-slate-900">Laporan Audit Hasil Ujian</h4>
            <p class="mt-2 text-sm text-slate-600">
                Laporan audit akan muncul setelah ujian selesai.
            </p>
            <p class="mt-2 text-xs text-slate-500">Total soal tersimpan: {{ $questionsCount }}.</p>
        </div>

        @if ($exam->status === 'finished')
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h4 class="font-semibold text-slate-900">Laporan Audit Operator</h4>
                        <p class="mt-1 text-sm text-slate-500">Memantau aksi operator seperti re-open attempt, force submit, dan manual scoring beserta alasannya.</p>
                    </div>
                    <span class="w-fit rounded bg-slate-100 px-2 py-1 text-xs font-semibold uppercase text-slate-700">Exam Finished</span>
                </div>

                @if (!empty($auditReport))
                    <div class="mb-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-slate-500">Total Attempt</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $auditReport['total_attempts'] }}</p>
                        </div>
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-amber-700">Force Submit</p>
                            <p class="mt-1 text-lg font-bold text-amber-800">{{ $auditReport['force_submit_count'] }}</p>
                        </div>
                        <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-indigo-700">Re-open</p>
                            <p class="mt-1 text-lg font-bold text-indigo-800">{{ $auditReport['reopen_count'] }}</p>
                        </div>
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-emerald-700">Manual Score</p>
                            <p class="mt-1 text-lg font-bold text-emerald-800">{{ $auditReport['manual_score_count'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
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
                            <article class="rounded-lg border border-slate-200 p-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-semibold uppercase text-slate-700">
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
                                    <div class="mt-2 overflow-x-auto rounded bg-slate-50 p-2">
                                        <p class="text-xs text-slate-500 break-all">Meta: {{ json_encode($audit['meta']) }}</p>
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
