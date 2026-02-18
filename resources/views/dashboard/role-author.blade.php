<section id="authoring-queue" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Queue Authoring Soal</h2>
            <p class="text-sm text-slate-500">Buat dan rapikan soal untuk exam draft yang ditugaskan admin.</p>
        </div>
        <a href="{{ route('author.exams.index') }}" class="inline-flex text-center items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Buka Panel Author
        </a>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($latestExams as $exam)
            <article class="rounded-xl border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-semibold text-slate-900">{{ $exam->title }}</p>
                    <span class="rounded bg-slate-100 px-2 py-1 text-[10px] font-semibold uppercase text-slate-700">{{ $exam->status }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</p>
            </article>
        @empty
            <p class="text-sm text-slate-500">Belum ada exam yang bisa dikerjakan author.</p>
        @endforelse
    </div>
</section>
