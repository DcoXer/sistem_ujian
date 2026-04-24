<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Hasil Ujian</h2>
    </x-slot>

    <div class="mx-auto max-w-lg px-4 py-6">
        @php
            $score = (int) ($attempt->score ?? 0);
            $isPassed = $score >= 70;
        @endphp

        {{-- Kartu Nilai Utama --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full {{ $isPassed ? 'bg-emerald-100' : 'bg-amber-100' }}">
                @if ($isPassed)
                    <x-icon name="trophy" class="h-10 w-10 text-emerald-600" />
                @else
                    <x-icon name="star" class="h-10 w-10 text-amber-500" />
                @endif
            </div>

            <p class="mt-4 text-sm font-medium uppercase tracking-wide text-slate-500">{{ $attempt->exam->title }}</p>
            <p class="mt-5 text-6xl font-extrabold {{ $isPassed ? 'text-emerald-600' : 'text-amber-500' }}">{{ $score }}</p>
            <p class="mt-1 text-sm text-slate-500">dari 100</p>

            @if ($isPassed)
                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-semibold text-emerald-700">
                    <x-icon name="check-circle" class="h-4 w-4" />
                    Selamat, kamu lulus!
                </div>
            @else
                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-1.5 text-sm font-semibold text-amber-700">
                    <x-icon name="exclamation" class="h-4 w-4" />
                    Tetap semangat belajar!
                </div>
            @endif

            <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-left">
                <dl class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Status ujian</dt>
                        <dd class="font-semibold text-slate-700">Sudah Dikumpulkan</dd>
                    </div>
                    @if ($attempt->submitted_at)
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Dikumpulkan pada</dt>
                            <dd class="font-semibold text-slate-700">{{ $attempt->submitted_at->format('d M Y, H:i') }} WIB</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <p class="mt-5 text-xs text-slate-400">Jawaban sudah dikunci dan tidak bisa diubah lagi.</p>
        </div>

        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('student.exams.index') }}" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali ke Daftar Ujian
            </a>
            <a href="{{ route('dashboard') }}" class="flex-1 rounded-xl bg-indigo-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-indigo-700">
                Ke Beranda
            </a>
        </div>
    </div>
</x-app-layout>
