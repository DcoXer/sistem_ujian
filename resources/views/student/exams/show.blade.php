<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Sedang Ujian</h2>
    </x-slot>

    <div
        class="mx-auto max-w-3xl space-y-4 px-4 py-6"
        data-attempt-id="{{ $attempt->id }}"
        data-answer-url="{{ route('student.exams.answer', $attempt) }}"
        data-timer-url="{{ route('student.exams.timer', $attempt) }}"
        data-timer-poll-interval-seconds="{{ max(5, (int) config('exam.timer_poll_interval_seconds', 30)) }}"
    >
        {{-- Header Ujian --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ $examContent['title'] }}</h3>
                    <p id="autosave-status" class="mt-1 text-xs text-slate-400">Autosimpan aktif.</p>
                </div>
                <div class="flex shrink-0 items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5">
                    <x-icon name="clock" class="h-5 w-5 text-rose-500" />
                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-wide text-rose-600">Sisa Waktu</p>
                        <p id="timer-display" class="text-xl font-bold text-rose-700">--:--</p>
                    </div>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="mt-4">
                <div class="mb-1.5 flex items-center justify-between text-xs text-slate-500">
                    <span id="question-step-label" class="font-semibold text-slate-700">Soal 1</span>
                    <span>dari {{ count($examContent['questions']) }} soal</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                    <div
                        id="progress-bar"
                        class="h-2 rounded-full bg-indigo-500 transition-all duration-300"
                        style="width: {{ count($examContent['questions']) > 0 ? round(1 / count($examContent['questions']) * 100) : 0 }}%"
                    ></div>
                </div>
            </div>
        </div>

        {{-- Kartu Soal --}}
        @foreach ($examContent['questions'] as $question)
            @php
                $myAnswer = $answersByQuestion->get($question['id']);
            @endphp
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm {{ $loop->first ? '' : 'hidden' }}"
                data-question-card
                data-question-id="{{ $question['id'] }}"
                data-question-order="{{ $question['order'] }}"
                data-answer-version="{{ $myAnswer?->lock_version ?? 0 }}"
            >
                <p class="text-base font-semibold text-slate-900">
                    <span class="mr-1 text-slate-400">#{{ $question['order'] }}.</span>
                    {{ $question['question_text'] }}
                </p>
                @if (! empty($question['question_image_url']))
                    <img src="{{ $question['question_image_url'] }}" alt="Gambar soal" class="mt-4 max-h-64 w-full rounded-xl border border-slate-200 object-contain">
                @endif
                <div class="mt-4 space-y-2.5">
                    @foreach ($question['options'] as $index => $option)
                        @php $optionLabel = chr(65 + $index); @endphp
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm transition-colors hover:border-indigo-300 hover:bg-indigo-50 has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50">
                            <input
                                type="radio"
                                name="question_{{ $question['id'] }}"
                                value="{{ $option['id'] }}"
                                data-option-input
                                data-question-id="{{ $question['id'] }}"
                                {{ (int) ($myAnswer->exam_option_id ?? 0) === (int) $option['id'] ? 'checked' : '' }}
                                class="accent-indigo-600"
                            >
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-300 text-xs font-bold text-slate-600">{{ $optionLabel }}</span>
                            <span class="text-slate-800">{{ $option['option_text'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Navigasi Soal --}}
        <form id="submit-form" method="POST" action="{{ route('student.exams.submit', $attempt) }}">
            @csrf
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div id="submit-warning" class="mb-3 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"></div>
                <div class="flex items-center justify-between gap-2">
                    <button
                        type="button"
                        id="prev-question"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        ← Sebelumnya
                    </button>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            id="next-question"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Selanjutnya →
                        </button>
                        <button
                            id="submit-button"
                            class="hidden inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"
                        >
                            <x-icon name="check" class="h-4 w-4" />
                            Kumpulkan Jawaban
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
            const timerPollIntervalSeconds = Math.max(5, Number(root.dataset.timerPollIntervalSeconds || 30));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const autosaveStatus = document.getElementById('autosave-status');
            const timerDisplay = document.getElementById('timer-display');
            const progressBar = document.getElementById('progress-bar');
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
            const answerVersions = {};

            questionCards.forEach((card) => {
                const questionId = card.dataset.questionId;
                if (!questionId) return;
                answerVersions[questionId] = card.dataset.answerVersion || null;
            });

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
                    autosaveStatus.textContent = 'Waktu habis. Jawaban sedang dikunci oleh sistem, harap tunggu...';
                    autosaveStatus.classList.remove('text-slate-400');
                    autosaveStatus.classList.add('text-rose-600', 'font-semibold');
                    document.querySelectorAll('[data-option-input]').forEach((input) => {
                        input.disabled = true;
                    });
                    submitButton?.setAttribute('disabled', 'disabled');
                    submitButton?.classList.add('cursor-not-allowed', 'opacity-50');
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

                if (progressBar) {
                    const percent = Math.round(((currentQuestionIndex + 1) / totalQuestions) * 100);
                    progressBar.style.width = `${percent}%`;
                }

                if (prevButton) {
                    prevButton.disabled = currentQuestionIndex === 0;
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
                    if (json.expired || (json.status && json.status !== 'active')) {
                        hasExpired = true;
                    }
                    updateTimerUi();
                } catch (_) {
                    // Abaikan error timer sementara.
                }
            };

            setInterval(() => {
                if (remainingSeconds !== null && remainingSeconds > 0) {
                    remainingSeconds -= 1;
                    updateTimerUi();
                }
            }, 1000);

            setInterval(() => {
                if (hasExpired) return;
                refreshTimer();
            }, timerPollIntervalSeconds * 1000);
            refreshTimer();

            const saveAnswer = async (questionId, optionId, retryCount = 0) => {
                if (hasExpired) {
                    autosaveStatus.textContent = 'Autosimpan dihentikan karena waktu sudah habis.';
                    return;
                }

                autosaveStatus.textContent = 'Menyimpan jawaban...';
                const formData = new FormData();
                formData.append('question_id', questionId);
                formData.append('option_id', optionId);
                if (answerVersions[questionId] !== undefined && answerVersions[questionId] !== null) {
                    formData.append('answer_version', answerVersions[questionId]);
                }

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
                        const json = await res.json();
                        answerVersions[questionId] = json.answer_version || answerVersions[questionId] || null;
                        autosaveStatus.textContent = '✓ Jawaban tersimpan otomatis.';
                    } else if (res.status === 409) {
                        const conflict = await res.json().catch(() => ({}));
                        if (
                            conflict.error_code === 'stale_answer_version'
                            && Number.isInteger(conflict.current_answer_version)
                            && retryCount < 1
                        ) {
                            answerVersions[questionId] = conflict.current_answer_version;
                            autosaveStatus.textContent = 'Sinkronisasi jawaban, mencoba simpan ulang...';
                            await saveAnswer(questionId, optionId, retryCount + 1);
                            return;
                        }

                        autosaveStatus.textContent = conflict.message || 'Jawaban bentrok. Muat ulang halaman untuk sinkronisasi.';
                    } else {
                        autosaveStatus.textContent = 'Gagal menyimpan. Coba pilih jawaban lagi.';
                    }
                } catch (_) {
                    autosaveStatus.textContent = 'Koneksi gagal saat menyimpan jawaban.';
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
                        submitWarning.textContent = `Masih ada soal yang belum dijawab: nomor ${unanswered.join(', ')}. Harap isi semua soal sebelum mengumpulkan.`;
                        submitWarning.classList.remove('hidden');
                    }
                }
            });

            renderQuestionStep();
        });
    </script>
</x-app-layout>
