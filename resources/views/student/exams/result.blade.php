<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Hasil Ujian</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 text-center">
            <p class="text-sm uppercase tracking-wide text-gray-500">{{ $attempt->exam->title }}</p>
            <p class="text-sm uppercase tracking-wide text-green-500">Nilai</p>
            <p class="mt-2 text-4xl font-bold text-green-500">{{ $attempt->score ?? 0 }}</p>
            <p class="mt-1 text-sm text-green-700">Status: {{ $attempt->status }}</p>
            <p class="mt-4 text-xs text-gray-500">Jawaban sudah dikunci. <br>Ujian selesai.</p>

            <a href="{{ route('student.exams.index') }}" class="mt-5 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Kembali ke Daftar Ujian
            </a>
        </div>
    </div>
</x-app-layout>

