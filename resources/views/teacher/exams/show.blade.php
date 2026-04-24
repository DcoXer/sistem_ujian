<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Kelola Soal Ujian</h2>
    </x-slot>

    @php
        $hasQuestionFormErrors = $errors->has('question_text') || $errors->has('question_image') || $errors->has('points') || $errors->has('order') || $errors->has('options') || $errors->has('options.*') || $errors->has('correct_option');
    @endphp

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
        <div>
            <x-back-button :href="route('teacher.exams.index')" />
        </div>

        {{-- Info Ujian --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ $exam->title }}</h3>
                    @if ($canManageQuestions)
                        <div class="mt-2 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            <x-icon name="clock" class="h-4 w-4 shrink-0 text-amber-600" />
                            <span>Batas buat soal: <strong>{{ $exam->authoring_start_at?->format('d M Y, H:i') ?? '-' }}</strong> – <strong>{{ $exam->authoring_end_at?->format('d M Y, H:i') ?? '-' }}</strong> WIB</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Pastikan semua soal sudah diisi sebelum batas waktu. Setelah waktu habis, soal otomatis terkunci.</p>
                    @else
                        <div class="mt-2 flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                            <x-icon name="lock" class="h-4 w-4 shrink-0" />
                            <span>Ujian sudah selesai. Soal ditampilkan hanya untuk dibaca.</span>
                        </div>
                    @endif
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $exam->status === 'draft' ? 'bg-amber-100 text-amber-800' : ($exam->status === 'running' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600') }}">
                    {{ match($exam->status) { 'draft' => 'Persiapan', 'running' => 'Berlangsung', 'finished' => 'Selesai', default => $exam->status } }}
                </span>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Panel Tambah Soal --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="font-semibold text-slate-900">Tambah Soal Baru</h4>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $questionsCount }} soal</span>
                </div>

                @if (! $canManageQuestions)
                    <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                        <x-icon name="lock" class="h-4 w-4 shrink-0" />
                        Tambah/edit soal sudah ditutup.
                    </div>
                @else
                    <button id="open-question-modal" type="button" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        <x-icon name="pencil" class="h-4 w-4" />
                        Tambah Soal
                    </button>
                @endif
            </div>

            {{-- Daftar Soal --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 class="mb-4 font-semibold text-slate-900">Daftar Soal yang Sudah Dibuat</h4>
                <div class="space-y-3">
                    @forelse ($exam->questions as $question)
                        @php
                            $optionTexts = $question->options->pluck('option_text')->values();
                            $correctIndex = $question->options->search(fn ($option) => (bool) $option->is_correct);
                            $correctIndex = $correctIndex === false ? 0 : $correctIndex;
                            $correctLabel = chr(65 + $correctIndex);
                        @endphp
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-900">#{{ $question->order }}. {{ $question->question_text }}</p>
                                @if ($canManageQuestions)
                                    <div class="flex shrink-0 items-center gap-1.5">
                                        <button
                                            type="button"
                                            class="rounded-lg border border-indigo-200 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50"
                                            data-edit-question-open="{{ $question->id }}"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('teacher.exams.questions.destroy', [$exam, $question]) }}" data-confirm data-confirm-title="Hapus Soal" data-confirm-message="Soal ini akan dihapus secara permanen. Lanjutkan?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            @if ($question->question_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($question->question_image_path) }}" alt="Gambar soal" class="mt-2 max-h-40 rounded-lg border border-slate-200 object-contain">
                            @endif

                            <ul class="mt-2 space-y-1">
                                @foreach ($question->options as $optIndex => $option)
                                    <li class="flex items-center gap-2 text-sm {{ $option->is_correct ? 'font-semibold text-emerald-700' : 'text-slate-600' }}">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[10px] font-bold {{ $option->is_correct ? 'border-emerald-400 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-400' }}">{{ chr(65 + $optIndex) }}</span>
                                        {{ $option->option_text }}
                                        @if ($option->is_correct)
                                            <span class="text-[10px] font-bold text-emerald-600">(Benar)</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @if ($canManageQuestions)
                            <div id="edit-question-modal-{{ $question->id }}" class="fixed inset-0 z-[106] hidden">
                                <div class="edit-question-modal-backdrop absolute inset-0 bg-slate-900/60" data-edit-question-close="{{ $question->id }}"></div>
                                <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                                    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                                        <div class="mb-5 flex items-center justify-between">
                                            <h3 class="text-lg font-bold text-slate-900">Edit Soal #{{ $question->order }}</h3>
                                            <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100" data-edit-question-close="{{ $question->id }}">Tutup</button>
                                        </div>

                                        <form method="POST" action="{{ route('teacher.exams.questions.update', [$exam, $question]) }}" enctype="multipart/form-data" class="space-y-4">
                                            @csrf
                                            @method('PUT')

                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-700">Teks Soal</label>
                                                <textarea name="question_text" placeholder="Tulis pertanyaan di sini..." rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400" required>{{ $question->question_text }}</textarea>
                                            </div>

                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-700">Gambar Soal <span class="text-slate-400">(opsional)</span></label>
                                                <input type="file" name="question_image" accept="image/*" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                                @if ($question->question_image_path)
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($question->question_image_path) }}" alt="Gambar soal saat ini" class="mt-2 max-h-40 rounded-lg border border-slate-200 object-contain">
                                                    <p class="mt-1 text-xs text-slate-400">Kosongkan jika tidak ingin mengganti gambar.</p>
                                                @endif
                                            </div>

                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-700">Bobot Nilai</label>
                                                <input type="number" min="1" name="points" value="{{ $question->points }}" class="w-32 rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400" required>
                                            </div>

                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-slate-700">Pilihan Jawaban</label>
                                                @foreach (['A', 'B', 'C', 'D'] as $idx => $letter)
                                                    <div class="flex items-center gap-2">
                                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-slate-300 text-xs font-bold text-slate-600">{{ $letter }}</span>
                                                        <input
                                                            name="options[]"
                                                            value="{{ $optionTexts->get($idx, '') }}"
                                                            placeholder="Opsi {{ $letter }}{{ $idx >= 2 ? ' (opsional)' : '' }}"
                                                            class="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400{{ $idx >= 2 ? '' : '' }}"
                                                            {{ $idx < 2 ? 'required' : '' }}
                                                        >
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div>
                                                <label class="mb-2 block text-sm font-medium text-slate-700">Jawaban yang Benar</label>
                                                <div class="flex flex-wrap gap-3">
                                                    @foreach (['A', 'B', 'C', 'D'] as $idx => $letter)
                                                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-400 hover:bg-emerald-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700">
                                                            <input type="radio" name="correct_option" value="{{ $idx }}" {{ $correctIndex === $idx ? 'checked' : '' }} class="accent-emerald-600">
                                                            Pilihan {{ $letter }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-end gap-2 pt-2">
                                                <button type="button" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-edit-question-close="{{ $question->id }}">Batal</button>
                                                <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center">
                            <x-icon name="document" class="mx-auto h-8 w-8 text-slate-300" />
                            <p class="mt-2 text-sm text-slate-500">Belum ada soal. Klik "Tambah Soal" untuk mulai.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if ($canManageQuestions)
        {{-- Modal Tambah Soal --}}
        <div id="question-modal" class="fixed inset-0 z-[105] hidden">
            <div class="question-modal-backdrop absolute inset-0 bg-slate-900/60"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Tambah Soal Baru</h3>
                        <button type="button" class="close-question-modal rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100">Tutup</button>
                    </div>

                    @if ($hasQuestionFormErrors)
                        <div class="mb-4 flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            <x-icon name="exclamation" class="h-4 w-4 shrink-0" />
                            Ada kesalahan input. Periksa kembali isian kamu.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('teacher.exams.questions.store', $exam) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Teks Soal</label>
                            <textarea name="question_text" placeholder="Tulis pertanyaan di sini..." rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400" required>{{ old('question_text') }}</textarea>
                            @error('question_text')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Gambar Soal <span class="text-slate-400">(opsional)</span></label>
                            <input type="file" name="question_image" accept="image/*" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <p class="mt-1 text-xs text-slate-400">Kosongkan jika soal tidak menggunakan gambar.</p>
                            @error('question_image')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Bobot Nilai</label>
                                <input type="number" min="1" name="points" value="{{ old('points', 10) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Nomor Soal</label>
                                <input type="number" min="1" name="order" value="{{ old('order', $exam->questions->count() + 1) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400" required>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-700">Pilihan Jawaban</label>
                            @foreach (['A', 'B', 'C', 'D'] as $idx => $letter)
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-slate-300 text-xs font-bold text-slate-600">{{ $letter }}</span>
                                    <input
                                        name="options[]"
                                        value="{{ old("options.$idx") }}"
                                        placeholder="Opsi {{ $letter }}{{ $idx >= 2 ? ' (opsional)' : '' }}"
                                        class="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400"
                                        {{ $idx < 2 ? 'required' : '' }}
                                    >
                                </div>
                            @endforeach
                            @error('options')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Jawaban yang Benar</label>
                            <div class="flex flex-wrap gap-3">
                                @foreach (['A', 'B', 'C', 'D'] as $idx => $letter)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-400 hover:bg-emerald-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700">
                                        <input type="radio" name="correct_option" value="{{ $idx }}" {{ old('correct_option', '0') == $idx ? 'checked' : '' }} class="accent-emerald-600" required>
                                        Pilihan {{ $letter }}
                                    </label>
                                @endforeach
                            </div>
                            @error('correct_option')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" class="close-question-modal rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Tambah Soal</button>
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
