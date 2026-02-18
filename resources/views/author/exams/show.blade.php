<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Kelola Soal</h2>
    </x-slot>

    @php
        $hasQuestionFormErrors = $errors->has('question_text') || $errors->has('points') || $errors->has('order') || $errors->has('options') || $errors->has('options.*') || $errors->has('correct_option');
    @endphp

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
        <div>
            <x-back-button :href="route('author.exams.index')" />
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $exam->title }}</h3>
                    <p class="mt-1 text-sm font-extrabold text-red-600">{{ $exam->authoring_start_at?->format('d M Y H:i') ?? '-' }} - {{ $exam->authoring_end_at?->format('d M Y H:i') ?? '-' }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        @if ($canManageQuestions)
                            Note: pembuatan/pengeditan soal ini memiliki batas waktu yang sudah ditentukan oleh admin.
                        @else
                            Exam sudah selesai. Soal ditampilkan read-only.
                        @endif
                    </p>
                </div>
                <span class="rounded bg-gray-100 px-2 py-1 text-xs uppercase text-gray-700">{{ $exam->status }}</span>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h4 class="mb-3 font-semibold text-gray-900">Tambah Soal</h4>
                @if (! $canManageQuestions)
                    <p class="text-sm text-amber-700">Tambah/edit soal ditutup. Halaman ini hanya untuk review soal final.</p>
                @else
                    <button id="open-question-modal" type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Tambah Soal
                    </button>
                @endif
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h4 class="font-semibold text-gray-900">Daftar Soal</h4>
                    <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">{{ $questionsCount }} soal</span>
                </div>
                <div class="space-y-3">
                    @forelse ($exam->questions as $question)
                        @php
                            $optionTexts = $question->options->pluck('option_text')->values();
                            $correctIndex = $question->options->search(fn ($option) => (bool) $option->is_correct);
                            $correctIndex = $correctIndex === false ? 0 : $correctIndex;
                        @endphp
                        <div class="rounded-lg border border-gray-200 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-900">#{{ $question->order }} ({{ $question->points }} pts)</p>
                                @if ($canManageQuestions)
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="rounded-md border border-indigo-200 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50"
                                            data-edit-question-open="{{ $question->id }}"
                                        >
                                            Edit Soal
                                        </button>
                                        <form method="POST" action="{{ route('author.exams.questions.destroy', [$exam, $question]) }}" data-confirm data-confirm-title="Hapus Soal" data-confirm-message="Soal ini akan dihapus. Lanjutkan?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-700">{{ $question->question_text }}</p>
                            <ul class="mt-2 space-y-1 text-sm text-gray-600">
                                @foreach ($question->options as $option)
                                    <li class="{{ $option->is_correct ? 'font-semibold text-emerald-700' : '' }}">{{ $option->option_text }}</li>
                                @endforeach
                            </ul>
                        </div>

                        @if ($canManageQuestions)
                            <div id="edit-question-modal-{{ $question->id }}" class="fixed inset-0 z-[106] hidden">
                                <div class="edit-question-modal-backdrop absolute inset-0 bg-slate-900/60" data-edit-question-close="{{ $question->id }}"></div>
                                <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                                    <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                                        <div class="mb-4 flex items-center justify-between">
                                            <h3 class="text-xl font-bold text-slate-900">Edit Soal #{{ $question->order }}</h3>
                                            <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100" data-edit-question-close="{{ $question->id }}">Tutup</button>
                                        </div>

                                        <form method="POST" action="{{ route('author.exams.questions.update', [$exam, $question]) }}" class="space-y-3">
                                            @csrf
                                            @method('PUT')

                                            <textarea name="question_text" placeholder="Tulis soal..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>{{ $question->question_text }}</textarea>

                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <div>
                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Points (bobot nilai soal)</label>
                                                    <input type="number" min="1" name="points" value="{{ $question->points }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Urutan soal</label>
                                                    <input
                                                        type="number"
                                                        min="1"
                                                        name="order"
                                                        value="{{ $question->order }}"
                                                        readonly
                                                        class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-500"
                                                        required
                                                    >
                                                    <p class="mt-1 text-xs text-gray-500">Urutan soal dikunci sistem dan tidak bisa diubah.</p>
                                                </div>
                                            </div>

                                            <input name="options[]" value="{{ $optionTexts->get(0, '') }}" placeholder="Opsi A" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                            <input name="options[]" value="{{ $optionTexts->get(1, '') }}" placeholder="Opsi B" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                            <input name="options[]" value="{{ $optionTexts->get(2, '') }}" placeholder="Opsi C (opsional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <input name="options[]" value="{{ $optionTexts->get(3, '') }}" placeholder="Opsi D (opsional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">

                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-700">Index jawaban benar (0=A, 1=B, dst)</label>
                                                <input type="number" min="0" name="correct_option" value="{{ $correctIndex }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                            </div>

                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100" data-edit-question-close="{{ $question->id }}">Batal</button>
                                                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-gray-500">Belum ada soal.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if ($canManageQuestions)
        <div id="question-modal" class="fixed inset-0 z-[105] hidden">
            <div class="question-modal-backdrop absolute inset-0 bg-slate-900/60"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Tambah Soal</h3>
                        <button type="button" class="close-question-modal rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100">Tutup</button>
                    </div>

                    @if ($hasQuestionFormErrors)
                        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            Periksa kembali input soal.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('author.exams.questions.store', $exam) }}" class="space-y-3">
                        @csrf
                        <textarea name="question_text" placeholder="Tulis soal..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>{{ old('question_text') }}</textarea>
                        @error('question_text')
                            <p class="text-xs text-rose-600">{{ $message }}</p>
                        @enderror

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Points (bobot nilai soal)</label>
                                <input type="number" min="1" name="points" value="{{ old('points', 10) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nomor Urut Soal</label>
                                <input type="number" min="1" name="order" value="{{ old('order', $exam->questions->count() + 1) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            </div>
                        </div>

                        <input name="options[]" value="{{ old('options.0') }}" placeholder="Opsi A" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        <input name="options[]" value="{{ old('options.1') }}" placeholder="Opsi B" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        <input name="options[]" value="{{ old('options.2') }}" placeholder="Opsi C (opsional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="options[]" value="{{ old('options.3') }}" placeholder="Opsi D (opsional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Index jawaban benar (0=A, 1=B, dst)</label>
                            <input type="number" min="0" name="correct_option" value="{{ old('correct_option', 0) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="close-question-modal rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Batal</button>
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Tambah Soal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('question-modal');
                if (!modal) return;

                const openBtn = document.getElementById('open-question-modal');
                const closeButtons = modal.querySelectorAll('.close-question-modal');
                const backdrop = modal.querySelector('.question-modal-backdrop');
                const hasErrors = @json($hasQuestionFormErrors);

                const openModal = () => modal.classList.remove('hidden');
                const closeModal = () => modal.classList.add('hidden');

                openBtn?.addEventListener('click', openModal);
                closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
                backdrop?.addEventListener('click', closeModal);

                if (hasErrors) openModal();

                document.querySelectorAll('[data-edit-question-open]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const id = btn.getAttribute('data-edit-question-open');
                        document.getElementById(`edit-question-modal-${id}`)?.classList.remove('hidden');
                    });
                });

                document.querySelectorAll('[data-edit-question-close]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const id = btn.getAttribute('data-edit-question-close');
                        document.getElementById(`edit-question-modal-${id}`)?.classList.add('hidden');
                    });
                });
            });
        </script>
    @endif
</x-app-layout>
