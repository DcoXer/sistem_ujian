<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Manajemen Ujian</h2>
    </x-slot>

    @php
        $hasExamFormErrors = $errors->has('title') || $errors->has('start_at') || $errors->has('end_at') || $errors->has('authoring_start_at') || $errors->has('authoring_end_at') || $errors->has('duration_minutes');
        $modalMode = request()->query('modal');
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-6">
        <div class="mb-4">
            <x-back-button :href="route('dashboard')" />
        </div>

        <div class="mb-4 grid gap-3 md:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs uppercase text-gray-500">Create</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">Ujian dibuat sebagai draft</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs uppercase text-gray-500">Publish</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">Publish hanya jika soal sudah ada</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs uppercase text-gray-500">Lock</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">Setelah publish, soal terkunci</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs uppercase text-gray-500">Result</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">Hasil dipantau dari laporan</p>
            </div>
        </div>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600">Buat, review, dan publish ujian.</p>
            <button id="open-exam-create" type="button" class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 sm:w-auto">Buat Ujian</button>
        </div>

        {{-- Mobile View --}}
        <div class="space-y-3 md:hidden">
            @forelse ($exams as $exam)
                <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-900">{{ $exam->title }}</h3>
                        <span class="rounded px-2 py-1 text-[10px] uppercase {{ $exam->status === 'draft' ? 'bg-amber-100 text-amber-800' : ($exam->status === 'running' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                            {{ $exam->status }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-gray-600">Author: {{ $exam->author?->name ?? '-' }}</p>
                    <p class="mt-1 text-xs text-gray-600">{{ $exam->start_at }} - {{ $exam->end_at }}</p>
                    <p class="mt-1 text-xs text-gray-600">Batas Waktu: {{ $exam->authoring_start_at?->format('d M Y H:i') ?? '-' }} - {{ $exam->authoring_end_at?->format('d M Y H:i') ?? '-' }}</p>
                    @php($isAuthoringOpen = $exam->status === 'draft' && $exam->isWithinAuthoringWindow())
                    @php($isPublishLockedByAuthoring = $exam->status === 'draft' && ! $exam->isAuthoringWindowClosed())
                    <div class="mt-2">
                        <span class="rounded px-2 py-1 text-[10px] font-semibold uppercase {{ $isAuthoringOpen ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ $isAuthoringOpen ? 'authoring_open' : 'authoring_closed' }}
                        </span>
                        <span class="ml-1 rounded px-2 py-1 text-[10px] font-semibold uppercase {{ $isPublishLockedByAuthoring ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $isPublishLockedByAuthoring ? 'publish_locked_by_authoring_window' : 'publish_window_ready' }}
                        </span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded bg-slate-50 px-2 py-1 text-slate-600">Soal: <span class="font-semibold text-slate-900">{{ $exam->questions_count }}</span></div>
                        <div class="rounded bg-slate-50 px-2 py-1 text-slate-600">Attempt: <span class="font-semibold text-slate-900">{{ $exam->attempts_count }}</span></div>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <a href="{{ route('admin.exams.show', $exam) }}" class="rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Detail</a>
                        @if ($exam->status === 'draft')
                            <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" data-confirm data-confirm-title="Hapus Exam" data-confirm-message="Exam draft ini akan dihapus permanen. Lanjutkan?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                    Hapus
                                </button>
                            </form>
                        @else
                            <span class="rounded bg-slate-100 px-2 py-1 text-[10px] uppercase text-slate-600">Read-only</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-500">Belum ada exam.</div>
            @endforelse
        </div>

        {{-- Tablet + Desktop view --}}
        <div class="-mx-4 hidden px-4 md:block">
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white [scrollbar-gutter:stable]">
                <table class="w-full min-w-[1080px] text-sm">
                <thead class="bg-gray-50 text-center text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Judul</th>
                        <th class="px-4 py-3 font-medium">Author</th>
                        <th class="px-4 py-3 font-medium">Waktu</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Soal</th>
                        <th class="px-4 py-3 font-medium">Attempt</th>
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $exam->title }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $exam->author?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <p class="mt-1 text-ms text-green-600">{{ $exam->start_at?->format('d M Y H:i') ?? '-' }} <br> {{ $exam->end_at->format('d M Y H:i') ?? '-' }}</p>
                                <p class="mt-1 text-xs text-rose-700">Batas Waktu: <br> {{ $exam->authoring_start_at?->format('d M Y H:i') ?? '-' }} <br> {{ $exam->authoring_end_at?->format('d M Y H:i') ?? '-' }}</p>
                                @php($isAuthoringOpen = $exam->status === 'draft' && $exam->isWithinAuthoringWindow())
                                @php($isPublishLockedByAuthoring = $exam->status === 'draft' && ! $exam->isAuthoringWindowClosed())
                                <span class="mt-1 inline-flex rounded px-2 py-1 text-[10px] font-semibold uppercase {{ $isAuthoringOpen ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $isAuthoringOpen ? 'Soal sedang dibuat' : 'soal ditutup' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded px-2 py-1 text-xs uppercase {{ $exam->status === 'draft' ? 'bg-amber-100 text-amber-800' : ($exam->status === 'running' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $exam->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $exam->questions_count }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $exam->attempts_count }}</td>
                            <td class="min-w-[190px] whitespace-nowrap px-4 py-3 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.exams.show', $exam) }}" class="text-indigo-600 hover:underline">Detail</a>
                                    @if ($exam->status === 'draft')
                                        <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" class="inline-block" data-confirm data-confirm-title="Hapus Exam" data-confirm-message="Exam draft ini akan dihapus permanen. Lanjutkan?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded border border-rose-200 bg-rose-50 px-2 py-1 text-[10px] font-semibold uppercase text-rose-700 hover:bg-rose-100">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span class="rounded bg-slate-100 px-2 py-1 text-[10px] uppercase text-slate-600">Read-only</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada exam.</td></tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="exam-modal" class="fixed inset-0 z-[105] hidden">
        <div class="exam-modal-backdrop absolute inset-0 bg-slate-900/60"></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-900">Buat Exam</h3>
                    <button type="button" class="close-exam-modal rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100">Tutup</button>
                </div>

                @if ($hasExamFormErrors)
                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        Periksa kembali input form exam.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.exams.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Judul</label>
                        <input name="title" value="{{ old('title') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        @error('title')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Mulai</label>
                            <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            @error('start_at')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Selesai</label>
                            <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            @error('end_at')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Mulai Authoring Soal</label>
                            <input type="datetime-local" name="authoring_start_at" value="{{ old('authoring_start_at') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            @error('authoring_start_at')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Selesai Authoring Soal</label>
                            <input type="datetime-local" name="authoring_end_at" value="{{ old('authoring_end_at') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            @error('authoring_end_at')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Durasi (menit)</label>
                        <input type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        @error('duration_minutes')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Author Penanggung Jawab Soal</label>
                        <select name="author_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            <option value="">Pilih author</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}" {{ (string) old('author_id') === (string) $author->id ? 'selected' : '' }}>
                                    {{ $author->name }} ({{ $author->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('author_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" class="close-exam-modal rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Batal</button>
                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('exam-modal');
            if (!modal) return;

            const openBtn = document.getElementById('open-exam-create');
            const closeButtons = modal.querySelectorAll('.close-exam-modal');
            const backdrop = modal.querySelector('.exam-modal-backdrop');

            const openModal = () => modal.classList.remove('hidden');
            const closeModal = () => modal.classList.add('hidden');

            openBtn?.addEventListener('click', openModal);
            closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
            backdrop?.addEventListener('click', closeModal);

            const hasErrors = @json($hasExamFormErrors);
            const modalMode = @json($modalMode);
            if (hasErrors || modalMode === 'create') {
                openModal();
            }
        });
    </script>
</x-app-layout>
