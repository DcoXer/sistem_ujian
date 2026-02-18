<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">{{ $exam->title }}</h2>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-6">
        <div class="mb-4">
            <x-back-button :href="route('operator.exams.index')" />
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($attempts as $attempt)
                @php($audit = $attempt->audits->first())
                <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900">{{ $attempt->user->name }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $attempt->user->email }}</p>
                        </div>
                        <span class="rounded bg-gray-100 px-2 py-1 text-xs uppercase text-gray-700">{{ $attempt->status }}</span>
                    </div>

                    <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                        <div class="rounded-lg bg-slate-100 px-2 py-2">
                            <p class="text-slate-500">Koneksi</p>
                            @if ($attempt->status === \App\Models\ExamAttempt::STATUS_ACTIVE)
                                <p class="font-semibold {{ $attempt->is_online ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $attempt->is_online ? 'Online' : 'Offline' }}
                                </p>
                            @else
                                <p class="font-semibold text-slate-700">-</p>
                            @endif
                        </div>
                        <div class="rounded-lg bg-slate-100 px-2 py-2">
                            <p class="text-slate-500">Sisa Waktu</p>
                            <p class="font-semibold text-slate-800">
                                {{ $attempt->status === \App\Models\ExamAttempt::STATUS_ACTIVE ? gmdate('i:s', (int) $attempt->remaining_seconds) : '-' }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-slate-100 px-2 py-2">
                            <p class="text-slate-500">Skor</p>
                            <p class="font-semibold text-slate-800">{{ $attempt->score ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2 border-t border-slate-100 pt-3">
                        @if ($attempt->status === \App\Models\ExamAttempt::STATUS_ACTIVE)
                            <form method="POST" action="{{ route('operator.exams.force-submit', $attempt) }}" class="grid gap-2 md:grid-cols-[1fr_auto]">
                                @csrf
                                <input type="text" name="reason" class="w-full rounded border border-gray-300 px-3 py-2 text-xs" placeholder="Alasan force submit" required>
                                <button class="rounded bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">Force Submit</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('operator.exams.reopen', $attempt) }}" class="grid gap-2 md:grid-cols-[1fr_auto]">
                                @csrf
                                <input type="text" name="reason" class="w-full rounded border border-gray-300 px-3 py-2 text-xs" placeholder="Alasan reopen attempt" required>
                                <button class="rounded bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">Re-open</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('operator.exams.mark-issue', $attempt) }}" class="grid gap-2 md:grid-cols-[1fr_auto]">
                            @csrf
                            <input type="text" name="reason" class="w-full rounded border border-gray-300 px-3 py-2 text-xs" placeholder="Catat kendala teknis peserta" required>
                            <button class="rounded bg-slate-700 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Log Issue</button>
                        </form>

                        <form method="POST" action="{{ route('operator.exams.manual-score', $attempt) }}" class="grid gap-2 md:grid-cols-[90px_1fr_auto]">
                            @csrf
                            <input type="hidden" name="intent" value="manual_essay_scoring">
                            <input type="number" name="score" min="0" class="w-full rounded border border-gray-300 px-3 py-2 text-xs" placeholder="Score" required>
                            <input type="text" name="reason" class="w-full rounded border border-gray-300 px-3 py-2 text-xs" placeholder="Alasan manual score" required>
                            <button class="rounded bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Manual Score</button>
                        </form>
                    </div>

                    <div class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                        <p class="font-semibold text-slate-700">Audit Terakhir</p>
                        @if ($audit)
                            <p class="mt-1 font-medium text-slate-800">{{ $audit->action }}</p>
                            <p>{{ $audit->reason ?: '-' }}</p>
                            <p class="text-[11px] text-slate-500">{{ $audit->created_at?->format('d M Y H:i') }}</p>
                        @else
                            <p class="mt-1">Belum ada log.</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-500 md:col-span-2 xl:col-span-3">
                    Belum ada peserta yang mulai ujian.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
