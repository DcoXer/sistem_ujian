<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Ujian Berlangsung</h2>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-4 px-4 py-6" data-attempt-id="{{ $attempt->id }}" data-answer-url="{{ route('peserta.exams.answer', $attempt) }}" data-timer-url="{{ route('peserta.exams.timer', $attempt) }}">
        <div>
            <x-back-button :href="route('peserta.exams.index')" />
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $attempt->exam->title }}</h3>
                    <p class="mt-1 text-sm text-gray-600">Status: <span id="attempt-status" class="font-semibold">{{ $attempt->status }}</span></p>
                    <p class="mt-1 text-sm text-gray-600"><span id="question-step-label" class="font-semibold">Soal 1</span> dari {{ $attempt->exam->questions->count() }}</p>
                </div>
                <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm">
                    Sisa Waktu: <span id="timer-display" class="font-bold text-indigo-700">--:--</span>
                </div>
            </div>
            <p id="autosave-status" class="mt-3 text-xs text-gray-500">Autosave aktif.</p>
        </div>

        @foreach ($attempt->exam->questions as $question)
            @php
                $myAnswer = $attempt->answers->firstWhere('exam_question_id', $question->id);
            @endphp
            <div
                class="rounded-xl border border-gray-200 bg-white p-5 {{ $loop->first ? '' : 'hidden' }}"
                data-question-card
                data-question-id="{{ $question->id }}"
                data-question-order="{{ $question->order }}"
            >
                <p class="font-semibold text-gray-900">#{{ $question->order }}. {{ $question->question_text }}</p>
                <div class="mt-3 space-y-2">
                    @foreach ($question->options as $option)
                        <label class="flex items-center gap-2 rounded border border-gray-200 px-3 py-2 text-sm">
                            <input
                                type="radio"
                                name="question_{{ $question->id }}"
                                value="{{ $option->id }}"
                                data-option-input
                                data-question-id="{{ $question->id }}"
                                {{ (int) ($myAnswer->exam_option_id ?? 0) === (int) $option->id ? 'checked' : '' }}
                            >
                            <span>{{ $option->option_text }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <form id="submit-form" method="POST" action="{{ route('peserta.exams.submit', $attempt) }}">
            @csrf
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div id="submit-warning" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"></div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <button type="button" id="prev-question" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                        Sebelumnya
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" id="next-question" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Next Soal
                        </button>
                        <button id="submit-button" class="hidden rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Submit Jawaban
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.querySelector('[data-attempt-id]');
            if (!root) return;

            const answerUrl = root.dataset.answerUrl;
            const timerUrl = root.dataset.timerUrl;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const autosaveStatus = document.getElementById('autosave-status');
            const timerDisplay = document.getElementById('timer-display');
            const attemptStatus = document.getElementById('attempt-status');
            const submitForm = document.getElementById('submit-form');
            const questionCards = Array.from(document.querySelectorAll('[data-question-card]'));
            const prevButton = document.getElementById('prev-question');
            const nextButton = document.getElementById('next-question');
            const submitButton = document.getElementById('submit-button');
            const submitWarning = document.getElementById('submit-warning');
            const questionStepLabel = document.getElementById('question-step-label');

            let remainingSeconds = null;
            let hasExpired = false;
            let currentQuestionIndex = 0;
            const totalQuestions = questionCards.length;

            const formatTime = (totalSeconds) => {
                const safe = Math.max(0, Math.floor(Number(totalSeconds || 0)));
                const mins = Math.floor(safe / 60).toString().padStart(2, '0');
                const secs = (safe % 60).toString().padStart(2, '0');
                return `${mins}:${secs}`;
            };

            const updateTimerUi = () => {
                if (remainingSeconds === null) return;
                timerDisplay.textContent = formatTime(remainingSeconds);
                if (remainingSeconds <= 0 && !hasExpired) {
                    hasExpired = true;
                    autosaveStatus.textContent = 'Waktu habis. Jawaban sedang dikunci oleh sistem, tunggu sinkronisasi...';
                    document.querySelectorAll('[data-option-input]').forEach((input) => {
                        input.disabled = true;
                    });
                    submitButton?.setAttribute('disabled', 'disabled');
                    submitButton?.classList.add('cursor-not-allowed', 'bg-slate-400');
                    submitButton?.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                }
            };

            const getUnansweredQuestionOrders = () => {
                const unanswered = [];
                questionCards.forEach((card) => {
                    const checked = card.querySelector('input[data-option-input]:checked');
                    if (!checked) {
                        unanswered.push(Number(card.dataset.questionOrder));
                    }
                });
                return unanswered;
            };

            const renderQuestionStep = () => {
                if (!totalQuestions) return;

                questionCards.forEach((card, index) => {
                    card.classList.toggle('hidden', index !== currentQuestionIndex);
                });

                if (questionStepLabel) {
                    questionStepLabel.textContent = `Soal ${currentQuestionIndex + 1}`;
                }

                if (prevButton) {
                    prevButton.disabled = currentQuestionIndex === 0;
                    prevButton.classList.toggle('opacity-50', currentQuestionIndex === 0);
                    prevButton.classList.toggle('cursor-not-allowed', currentQuestionIndex === 0);
                }

                const isLastQuestion = currentQuestionIndex === totalQuestions - 1;
                if (nextButton) {
                    nextButton.classList.toggle('hidden', isLastQuestion);
                }
                if (submitButton) {
                    submitButton.classList.toggle('hidden', !isLastQuestion);
                }
            };

            const refreshTimer = async () => {
                try {
                    const res = await fetch(timerUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const json = await res.json();
                    remainingSeconds = Math.max(0, Math.floor(Number(json.remaining_seconds || 0)));
                    attemptStatus.textContent = json.status || '-';
                    if (json.expired || (json.status && json.status !== 'active')) {
                        hasExpired = true;
                    }
                    updateTimerUi();
                } catch (_) {
                    // Ignore transient timer errors.
                }
            };

            setInterval(() => {
                if (remainingSeconds !== null && remainingSeconds > 0) {
                    remainingSeconds -= 1;
                    updateTimerUi();
                }
            }, 1000);

            setInterval(refreshTimer, 15000);
            refreshTimer();

            const saveAnswer = async (questionId, optionId) => {
                if (hasExpired) {
                    autosaveStatus.textContent = 'Autosave dihentikan karena waktu sudah habis.';
                    return;
                }

                autosaveStatus.textContent = 'Menyimpan jawaban...';
                const formData = new FormData();
                formData.append('question_id', questionId);
                formData.append('option_id', optionId);

                try {
                    const res = await fetch(answerUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (res.ok) {
                        autosaveStatus.textContent = 'Jawaban tersimpan otomatis.';
                    } else {
                        autosaveStatus.textContent = 'Gagal autosave. Coba pilih jawaban lagi.';
                    }
                } catch (_) {
                    autosaveStatus.textContent = 'Koneksi gagal saat autosave.';
                }
            };

            document.querySelectorAll('[data-option-input]').forEach((input) => {
                input.addEventListener('change', () => {
                    saveAnswer(input.dataset.questionId, input.value);
                });
            });

            prevButton?.addEventListener('click', () => {
                if (currentQuestionIndex > 0) {
                    currentQuestionIndex -= 1;
                    renderQuestionStep();
                    submitWarning?.classList.add('hidden');
                }
            });

            nextButton?.addEventListener('click', () => {
                if (currentQuestionIndex < totalQuestions - 1) {
                    currentQuestionIndex += 1;
                    renderQuestionStep();
                    submitWarning?.classList.add('hidden');
                }
            });

            submitForm.addEventListener('submit', (event) => {
                const unanswered = getUnansweredQuestionOrders();
                if (unanswered.length > 0) {
                    event.preventDefault();
                    const firstMissingOrder = unanswered[0];
                    const missingIndex = questionCards.findIndex((card) => Number(card.dataset.questionOrder) === firstMissingOrder);
                    if (missingIndex >= 0) {
                        currentQuestionIndex = missingIndex;
                        renderQuestionStep();
                    }
                    if (submitWarning) {
                        submitWarning.textContent = `Masih ada soal yang belum diisi: nomor ${unanswered.join(', ')}.`;
                        submitWarning.classList.remove('hidden');
                    }
                }
            });

            renderQuestionStep();
        });
    </script>
</x-app-layout>
