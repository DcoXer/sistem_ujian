<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Kelola Soal Ujian</h1>
    </x-slot>

    <div class="space-y-4">
        <div>
            <x-back-button :href="route('dashboard')" />
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-600">Halo <b>{{ Auth::user()->name }}</b>, Kamu dapet tugas untuk membuat soal dari <b>ADMIN</b> nih. Yuk buat soalnya !</p>
            <br>
            <p class="text-xs text-slate-500 italic">Note : Author hanya bisa buat/edit soal di rentang waktu yang ditentukan admin.</p>
        </div>

        {{-- Mobile view --}}
        <div class="space-y-3 md:hidden">
            @forelse ($exams as $exam)
                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-slate-900">{{ $exam->title }}</h3>
                        <span class="rounded px-2 py-1 text-[10px] uppercase {{ $exam->status === 'draft' ? 'bg-amber-100 text-amber-800' : ($exam->status === 'running' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                            {{ $exam->status }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-600">Waktu Mulai Ujian:
                        <br> 
                        <span class="text-green-700">
                            {{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}
                        </span>
                    </p>
                    <p class="mt-1 text-xs text-slate-600">Batas Waktu Pembuatan Soal: 
                        <br>
                        <span class="text-red-700"> 
                            {{ $exam->authoring_start_at?->format('d M Y H:i') ?? '-' }} - {{ $exam->authoring_end_at?->format('d M Y H:i') ?? '-' }}
                        </span>
                    </p>
                    <p class="mt-1 text-xs text-slate-600">Total soal: 
                        <span class="font-semibold text-slate-900">
                            {{ $exam->questions_count }}
                        </span>
                    </p>
                    <div class="mt-3">
                        @php($canAuthorManage = $exam->status === 'draft' && $exam->isWithinAuthoringWindow())
                        @php($canAuthorViewFinished = $exam->status === 'finished')
                        @if ($canAuthorManage)
                            <a href="{{ route('author.exams.show', $exam) }}" class="inline-flex rounded border border-indigo-200 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">
                                Buat Soal
                            </a>
                        @elseif ($canAuthorViewFinished)
                            <a href="{{ route('author.exams.show', $exam) }}" class="inline-flex rounded border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                Lihat Soal
                            </a>
                        @else
                            <button type="button" disabled class="cursor-not-allowed rounded border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-400">
                                {{ $exam->status === 'running' ? 'Ujian Berjalan' : ($exam->authoring_end_at && now()->greaterThan($exam->authoring_end_at) ? 'Window Authoring Berakhir' : 'Authoring Terkunci') }}
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">Belum ada Ujian Aktif.</div>
            @endforelse
        </div>

        {{-- Desktop view --}}
        <div class="-mx-4 hidden overflow-x-auto sm:mx-0 md:block">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Judul</th>
                        <th class="px-4 py-3 font-medium">Jadwal</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Soal</th>
                        <th class="px-4 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $exam->title }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                <p>{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</p>
                                <p class="mt-1 text-xs text-slate-500">Batas Waktu Pembuatan Soal: <br> {{ $exam->authoring_start_at?->format('d M Y H:i') ?? '-' }} - {{ $exam->authoring_end_at?->format('d M Y H:i') ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded px-2 py-1 text-xs uppercase {{ $exam->status === 'draft' ? 'bg-amber-100 text-amber-800' : ($exam->status === 'running' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $exam->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $exam->questions_count }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @php($canAuthorManage = $exam->status === 'draft' && $exam->isWithinAuthoringWindow())
                                @php($canAuthorViewFinished = $exam->status === 'finished')
                                @if ($canAuthorManage)
                                    <a href="{{ route('author.exams.show', $exam) }}" class="rounded border border-indigo-200 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">
                                        Buat Soal
                                    </a>
                                @elseif ($canAuthorViewFinished)
                                    <a href="{{ route('author.exams.show', $exam) }}" class="rounded border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                        Lihat Soal
                                    </a>
                                @else
                                    <button type="button" disabled class="cursor-not-allowed rounded border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-400">
                                        {{ $exam->status === 'running' ? 'Ujian Berjalan' : ($exam->authoring_end_at && now()->greaterThan($exam->authoring_end_at) ? 'Pembuatan Soal Berakhir' : 'Soal Terkunci') }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data exam.</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>

        {{ $exams->links() }}
    </div>
</x-app-layout>
