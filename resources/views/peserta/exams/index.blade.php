<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Ujian</h2>
    </x-slot>

    <div id="peserta-exam-list" class="mx-auto max-w-6xl px-4 py-6" data-realtime-state-url="{{ route('peserta.exams.realtime-state') }}">
        <div class="mb-4">
            <x-back-button :href="route('dashboard')" />
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($exams as $exam)
                @php
                    $isNotStartedYet = $exam->ui_action === \App\Support\ExamUiAction::START_DISABLED && $exam->ui_state === \App\Support\ExamUiState::NOT_STARTED;
                    $isEnded = in_array($exam->ui_action, [\App\Support\ExamUiAction::START_DISABLED, \App\Support\ExamUiAction::CONTINUE_DISABLED], true) && $exam->ui_state === \App\Support\ExamUiState::FINISHED;
                    $cannotStart = $exam->ui_action === \App\Support\ExamUiAction::START_DISABLED;
                @endphp
                <div
                    class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                    data-exam-card
                    data-exam-id="{{ $exam->id }}"
                    data-start-at-ts="{{ $exam->start_at?->getTimestamp() }}"
                    data-end-at-ts="{{ $exam->end_at?->getTimestamp() }}"
                    data-start-display="{{ $exam->start_at?->format('d M Y H:i') }}"
                    data-end-display="{{ $exam->end_at?->format('d M Y H:i') }}"
                    data-attempt-status="{{ $exam->my_attempt_status ?? '' }}"
                    data-current-action="{{ $exam->ui_action }}"
                >
                    <h3 class="text-lg font-semibold text-gray-900">{{ $exam->title }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</p>
                    <p class="mt-2 text-xs uppercase text-gray-500">Status: <span data-attempt-status-label>{{ $exam->my_attempt_status ?? 'belum mulai' }}</span></p>

                    <div class="mt-4">
                        @if ($exam->my_attempt_id)
                            <a
                                href="{{ route('peserta.exams.result', $exam->my_attempt_id) }}"
                                data-result-link
                                class="{{ $exam->ui_action === \App\Support\ExamUiAction::RESULT ? '' : 'hidden' }} rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                            >
                                Lihat Hasil
                            </a>

                            <a
                                href="{{ route('peserta.exams.show', $exam->my_attempt_id) }}"
                                data-continue-link
                                class="{{ $exam->ui_action === \App\Support\ExamUiAction::CONTINUE_ENABLED ? '' : 'hidden' }} rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                            >
                                Lanjut Ujian
                            </a>

                            <button
                                type="button"
                                disabled
                                data-continue-disabled
                                class="{{ in_array($exam->ui_action, [\App\Support\ExamUiAction::CONTINUE_DISABLED, \App\Support\ExamUiAction::WAITING_RESULT], true) ? '' : 'hidden' }} cursor-not-allowed rounded-lg bg-slate-400 px-4 py-2 text-sm font-semibold text-white"
                            >
                                {{ $exam->ui_action === \App\Support\ExamUiAction::WAITING_RESULT ? 'Menunggu Hasil' : 'Lanjut Ujian' }}
                            </button>

                            <p class="mt-2 text-xs {{ in_array($exam->ui_action, [\App\Support\ExamUiAction::CONTINUE_DISABLED, \App\Support\ExamUiAction::WAITING_RESULT], true) && $exam->ui_message ? '' : 'hidden' }} {{ ($exam->ui_message_tone ?? '') === 'amber' ? 'text-amber-700' : 'text-rose-700' }}" data-continue-expired-msg>{{ $exam->ui_message }}</p>

                        @else
                            <form method="POST" action="{{ route('peserta.exams.start', $exam) }}">
                                @csrf
                                <button
                                    type="submit"
                                    data-start-button
                                    {{ $cannotStart ? 'disabled' : '' }}
                                    class="rounded-lg px-4 py-2 text-sm font-semibold text-white {{ $cannotStart ? 'cursor-not-allowed bg-slate-400' : 'bg-indigo-600 hover:bg-indigo-700' }}"
                                >
                                    Start Exam
                                </button>
                            </form>
                            <p class="mt-2 text-xs {{ $exam->ui_message ? '' : 'hidden' }} {{ $isNotStartedYet ? 'text-amber-700' : ($isEnded ? 'text-rose-700' : 'text-slate-600') }}" data-start-message>{{ $exam->ui_message }}</p>
                        @endif

                        @if ($exam->my_attempt_id && in_array($exam->my_attempt_status, ['submitted', 'finished'], true))
                            <p class="mt-2 hidden text-xs text-slate-600" data-start-message></p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada ujian yang dipublish.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
