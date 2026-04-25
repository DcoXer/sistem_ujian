<x-exam-layout :title="$examContent['title']">

    <div
        class="mx-auto max-w-2xl px-4 py-6 pb-36"
        data-attempt-id="{{ $attempt->id }}"
        data-answer-url="{{ route('student.exams.answer', $attempt) }}"
        data-timer-url="{{ route('student.exams.timer', $attempt) }}"
        data-timer-poll-interval-seconds="{{ max(5, (int) config('exam.timer_poll_interval_seconds', 30)) }}"
    >
        {{-- Info progress --}}
        <div class="mb-5 flex items-center justify-between text-sm">
            <div class="flex items-center gap-2 text-slate-500">
                <span id="question-step-label" class="font-bold text-slate-800">Soal 1</span>
                <span>dari {{ count($examContent['questions']) }} soal</span>
            </div>
            <span id="answered-count-label" class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                0 / {{ count($examContent['questions']) }} dijawab
            </span>
        </div>

        {{-- Kartu Soal --}}
        @foreach ($examContent['questions'] as $question)
            @php $myAnswer = $answersByQuestion->get($question['id']); @endphp
            <div
                class="{{ $loop->first ? '' : 'hidden' }}"
                data-question-card
                data-question-id="{{ $question['id'] }}"
                data-question-order="{{ $question['order'] }}"
                data-answer-version="{{ $myAnswer?->lock_version ?? 0 }}"
            >
                {{-- Soal --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                            {{ $question['order'] }}
                        </span>
                        <p class="text-base font-semibold leading-relaxed text-slate-900">
                            {{ $question['question_text'] }}
                        </p>
                    </div>

                    @if (! empty($question['question_image_url']))
                        <img src="{{ $question['question_image_url'] }}" alt="Gambar soal" class="mb-4 max-h-72 w-full rounded-xl border border-slate-200 object-contain">
                    @endif

                    {{-- Opsi Jawaban --}}
                    <div class="space-y-3">
                        @foreach ($question['options'] as $index => $option)
                            @php $optionLabel = chr(65 + $index); @endphp
                            <label class="group flex cursor-pointer items-start gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 transition-all hover:border-indigo-300 hover:bg-indigo-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:shadow-sm">
                                <input
                                    type="radio"
                                    name="question_{{ $question['id'] }}"
                                    value="{{ $option['id'] }}"
                                    data-option-input
                                    data-question-id="{{ $question['id'] }}"
                                    {{ (int) ($myAnswer->exam_option_id ?? 0) === (int) $option['id'] ? 'checked' : '' }}
                                    class="sr-only"
                                >
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-slate-300 text-xs font-bold text-slate-500 transition-all group-has-[:checked]:border-indigo-500 group-has-[:checked]:bg-indigo-500 group-has-[:checked]:text-white">{{ $optionLabel }}</span>
                                <span class="text-sm leading-relaxed text-slate-800 group-has-[:checked]:font-medium group-has-[:checked]:text-indigo-900">
                                    {{ $option['option_text'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Bottom navigation (fixed) --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white shadow-[0_-4px_12px_rgba(0,0,0,0.06)]">

        {{-- Question grid --}}
        <div class="border-b border-slate-100 px-4 py-3">
            <div class="flex flex-wrap gap-1.5" id="question-grid">
                @foreach ($examContent['questions'] as $question)
                    @php $myAnswer = $answersByQuestion->get($question['id']); @endphp
                    <button
                        type="button"
                        data-grid-btn
                        data-question-index="{{ $loop->index }}"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold transition-all
                            {{ $myAnswer ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}"
                    >
                        {{ $question['order'] }}
                    </button>
                @endforeach
            </div>
            <div class="mt-2 flex items-center gap-4 text-[11px] text-slate-400">
                <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-emerald-100"></span> Sudah dijawab</span>
                <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-slate-100"></span> Belum dijawab</span>
                <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-indigo-500"></span> Soal sekarang</span>
            </div>
        </div>

        {{-- Prev / Submit --}}
        <form id="submit-form" method="POST" action="{{ route('student.exams.submit', $attempt) }}">
            @csrf
            <div class="px-4 py-3">
                <div id="submit-warning" class="mb-3 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"></div>
                <div class="flex items-center justify-between gap-3">
                    <button
                        type="button"
                        id="prev-question"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <x-icon name="arrow-left" class="h-4 w-4" />
                        Sebelumnya
                    </button>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            id="next-question"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Selanjutnya
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </button>
                        <button
                            id="submit-button"
                            class="hidden inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"
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
            const answeredCountLabel = document.getElementById('answered-count-label');
            const gridButtons = Array.from(document.querySelectorAll('[data-grid-btn]'));

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

            // --- Timer ---
            const formatTime = (totalSeconds) => {
                const safe = Math.max(0, Math.floor(Number(totalSeconds || 0)));
                const mins = Math.floor(safe / 60).toString().padStart(2, '0');
                const secs = (safe % 60).toString().padStart(2, '0');
                return `${mins}:${secs}`;
            };

            const updateTimerUi = () => {
                if (remainingSeconds === null) return;
                timerDisplay.textContent = formatTime(remainingSeconds);

                // Warna merah mendesak jika < 5 menit
                if (remainingSeconds <= 300) {
                    timerDisplay.classList.add('text-rose-600');
                }

                if (remainingSeconds <= 0 && !hasExpired) {
                    hasExpired = true;
                    if (autosaveStatus) {
                        autosaveStatus.textContent = 'Waktu habis. Jawaban sedang dikunci oleh sistem, harap tunggu...';
                        autosaveStatus.classList.remove('text-slate-400');
                        autosaveStatus.classList.add('text-rose-600', 'font-semibold');
                    }
                    document.querySelectorAll('[data-option-input]').forEach((input) => {
                        input.disabled = true;
                    });
                    submitButton?.setAttribute('disabled', 'disabled');
                    submitButton?.classList.add('cursor-not-allowed', 'opacity-50');
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
                } catch (_) {}
            };
            refreshTimer();

            // --- Question grid & answered count ---
            const getAnsweredCount = () => {
                return questionCards.filter((card) =>
                    card.querySelector('input[data-option-input]:checked')
                ).length;
            };

            const updateGridAndCount = () => {
                const answered = getAnsweredCount();
                if (answeredCountLabel) {
                    answeredCountLabel.textContent = `${answered} / ${totalQuestions} dijawab`;
                }

                gridButtons.forEach((btn, index) => {
                    const card = questionCards[index];
                    const isAnswered = card?.querySelector('input[data-option-input]:checked');
                    const isCurrent = index === currentQuestionIndex;

                    btn.className = btn.className.replace(/bg-\S+|text-\S+|ring-\S+/g, '').trim();

                    if (isCurrent) {
                        btn.classList.add('bg-indigo-500', 'text-white', 'ring-2', 'ring-indigo-300');
                    } else if (isAnswered) {
                        btn.classList.add('bg-emerald-100', 'text-emerald-700', 'hover:bg-emerald-200');
                    } else {
                        btn.classList.add('bg-slate-100', 'text-slate-500', 'hover:bg-slate-200');
                    }
                });
            };

            gridButtons.forEach((btn, index) => {
                btn.addEventListener('click', () => {
                    currentQuestionIndex = index;
                    renderQuestionStep();
                    submitWarning?.classList.add('hidden');
                });
            });

            // --- Navigation ---
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
                if (nextButton) nextButton.classList.toggle('hidden', isLastQuestion);
                if (submitButton) submitButton.classList.toggle('hidden', !isLastQuestion);

                updateGridAndCount();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

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

            // --- Autosave ---
            const getUnansweredQuestionOrders = () => {
                return questionCards
                    .filter((card) => !card.querySelector('input[data-option-input]:checked'))
                    .map((card) => Number(card.dataset.questionOrder));
            };

            const saveAnswer = async (questionId, optionId, retryCount = 0) => {
                if (hasExpired) {
                    if (autosaveStatus) autosaveStatus.textContent = 'Autosimpan dihentikan karena waktu sudah habis.';
                    return;
                }

                if (autosaveStatus) autosaveStatus.textContent = 'Menyimpan jawaban...';
                const formData = new FormData();
                formData.append('question_id', questionId);
                formData.append('option_id', optionId);
                if (answerVersions[questionId] !== undefined && answerVersions[questionId] !== null) {
                    formData.append('answer_version', answerVersions[questionId]);
                }

                try {
                    const res = await fetch(answerUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: formData,
                    });

                    if (res.ok) {
                        const json = await res.json();
                        answerVersions[questionId] = json.answer_version || answerVersions[questionId] || null;
                        if (autosaveStatus) autosaveStatus.textContent = '✓ Jawaban tersimpan otomatis.';
                        updateGridAndCount();
                    } else if (res.status === 409) {
                        const conflict = await res.json().catch(() => ({}));
                        if (
                            conflict.error_code === 'stale_answer_version'
                            && Number.isInteger(conflict.current_answer_version)
                            && retryCount < 1
                        ) {
                            answerVersions[questionId] = conflict.current_answer_version;
                            if (autosaveStatus) autosaveStatus.textContent = 'Sinkronisasi jawaban, mencoba simpan ulang...';
                            await saveAnswer(questionId, optionId, retryCount + 1);
                            return;
                        }
                        if (autosaveStatus) autosaveStatus.textContent = conflict.message || 'Jawaban bentrok. Muat ulang halaman.';
                    } else {
                        if (autosaveStatus) autosaveStatus.textContent = 'Gagal menyimpan. Coba pilih jawaban lagi.';
                    }
                } catch (_) {
                    if (autosaveStatus) autosaveStatus.textContent = 'Koneksi gagal saat menyimpan jawaban.';
                }
            };

            document.querySelectorAll('[data-option-input]').forEach((input) => {
                input.addEventListener('change', () => {
                    saveAnswer(input.dataset.questionId, input.value);
                });
            });

            // --- Submit ---
            submitForm.addEventListener('submit', (event) => {
                const unanswered = getUnansweredQuestionOrders();
                if (unanswered.length > 0) {
                    event.preventDefault();
                    const firstMissingOrder = unanswered[0];
                    const missingIndex = questionCards.findIndex(
                        (card) => Number(card.dataset.questionOrder) === firstMissingOrder
                    );
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

</x-exam-layout>
