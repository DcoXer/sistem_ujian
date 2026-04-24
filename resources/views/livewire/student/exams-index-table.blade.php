<div wire:poll.10s class="space-y-4">
    @error('start')
        <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <x-icon name="exclamation" class="h-5 w-5 shrink-0" />
            {{ $message }}
        </div>
    @enderror

    {{-- Panduan Singkat --}}
    <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
        <x-icon name="info" class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" />
        <p class="text-sm text-blue-800">Daftar ujian diperbarui otomatis setiap 10 detik. Klik <strong>Mulai Ujian</strong> saat ujian sudah dibuka.</p>
    </div>

    @if ($exams->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white py-12 text-center">
            <x-icon name="document" class="mx-auto h-10 w-10 text-slate-300" />
            <p class="mt-3 text-sm font-medium text-slate-500">Belum ada ujian yang tersedia.</p>
            <p class="mt-1 text-xs text-slate-400">Ujian akan muncul di sini setelah dibuka oleh guru.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($exams as $exam)
                @php
                    $isNotStartedYet = $exam->ui_action === \App\Support\ExamUiAction::START_DISABLED && $exam->ui_state === \App\Support\ExamUiState::NOT_STARTED;
                    $isEnded = in_array($exam->ui_action, [\App\Support\ExamUiAction::START_DISABLED, \App\Support\ExamUiAction::CONTINUE_DISABLED], true) && $exam->ui_state === \App\Support\ExamUiState::FINISHED;
                    $cannotStart = $exam->ui_action === \App\Support\ExamUiAction::START_DISABLED;

                    $attemptStatusLabel = match($exam->my_attempt_status ?? '') {
                        'in_progress' => 'Sedang dikerjakan',
                        'submitted' => 'Sudah dikumpulkan',
                        'expired' => 'Waktu habis',
                        default => 'Belum dimulai',
                    };
                    $attemptStatusClass = match($exam->my_attempt_status ?? '') {
                        'in_progress' => 'bg-amber-100 text-amber-800',
                        'submitted' => 'bg-emerald-100 text-emerald-800',
                        'expired' => 'bg-rose-100 text-rose-700',
                        default => 'bg-slate-100 text-slate-600',
                    };
                @endphp

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-slate-900">{{ $exam->title }}</h3>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $attemptStatusClass }}">{{ $attemptStatusLabel }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span class="flex items-center gap-1">
                                    <x-icon name="clock" class="h-3.5 w-3.5" />
                                    Mulai: {{ $exam->start_at?->format('d M Y, H:i') ?? '-' }} WIB
                                </span>
                                <span>Selesai: {{ $exam->end_at?->format('d M Y, H:i') ?? '-' }} WIB</span>
                            </div>
                            @if ($exam->ui_message)
                                <p class="mt-2 flex items-center gap-1.5 text-xs {{ $isNotStartedYet ? 'text-amber-700' : ($isEnded ? 'text-rose-600' : 'text-slate-500') }}">
                                    <x-icon name="info" class="h-3.5 w-3.5 shrink-0" />
                                    {{ $exam->ui_message }}
                                </p>
                            @endif
                        </div>

                        <div class="shrink-0">
                            @if ($exam->my_attempt_id)
                                @if ($exam->ui_action === \App\Support\ExamUiAction::RESULT)
                                    <a href="{{ route('student.exams.result', $exam->my_attempt_id) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                        <x-icon name="trophy" class="h-4 w-4" />
                                        Lihat Nilai
                                    </a>
                                @elseif ($exam->ui_action === \App\Support\ExamUiAction::CONTINUE_ENABLED)
                                    <a href="{{ route('student.exams.show', $exam->my_attempt_id) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                        <x-icon name="arrow-right" class="h-4 w-4" />
                                        Lanjut Kerjakan
                                    </a>
                                @else
                                    <button type="button" disabled class="inline-flex cursor-not-allowed items-center gap-2 rounded-xl bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-500">
                                        <x-icon name="lock" class="h-4 w-4" />
                                        {{ $exam->ui_action === \App\Support\ExamUiAction::WAITING_RESULT ? 'Menunggu Hasil' : 'Tidak Bisa Dilanjutkan' }}
                                    </button>
                                @endif
                            @else
                                <button
                                    type="button"
                                    wire:click="startExam({{ $exam->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="startExam"
                                    {{ $cannotStart ? 'disabled' : '' }}
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white {{ $cannotStart ? 'cursor-not-allowed bg-slate-300 text-slate-500' : 'bg-indigo-600 hover:bg-indigo-700' }}"
                                >
                                    @if ($cannotStart)
                                        <x-icon name="lock" class="h-4 w-4" />
                                        {{ $isEnded ? 'Sudah Berakhir' : 'Belum Dibuka' }}
                                    @else
                                        <x-icon name="play" class="h-4 w-4" />
                                        <span wire:loading.remove wire:target="startExam">Mulai Ujian</span>
                                        <span wire:loading wire:target="startExam">Memuat...</span>
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
