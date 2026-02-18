<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl sm:text-md font-semibold text-gray-800">Monitoring Ujian</h2>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-6">
        <div class="mb-4">
            <x-back-button :href="route('dashboard')" />
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($exams as $exam)
                <a href="{{ route('operator.exams.show', $exam) }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $exam->title }}</h3>
                        <span class="rounded px-2 py-1 text-[11px] font-semibold uppercase {{ $exam->phase === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ $exam->phase === 'active' ? 'Aktif' : 'Akan Mulai' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-600">{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</p>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded bg-slate-100 p-2"><div class="font-semibold text-slate-900">{{ $exam->participants_started }}</div><div class="text-slate-500">Mulai</div></div>
                        <div class="rounded bg-amber-100 p-2"><div class="font-semibold text-amber-800">{{ $exam->participants_in_progress }}</div><div class="text-amber-700">Mengerjakan</div></div>
                        <div class="rounded bg-emerald-100 p-2"><div class="font-semibold text-emerald-800">{{ $exam->participants_submitted }}</div><div class="text-emerald-700">Submit</div></div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-gray-500">Belum ada ujian aktif.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
