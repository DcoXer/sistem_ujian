@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col items-center justify-between gap-3 sm:flex-row">

        {{-- Info --}}
        <p class="text-xs text-slate-400">
            Soal
            <span class="font-semibold text-slate-600">{{ $paginator->firstItem() }}</span>
            –
            <span class="font-semibold text-slate-600">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-slate-600">{{ $paginator->total() }}</span>
        </p>

        {{-- Controls --}}
        <div class="flex items-center gap-1">

            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg text-slate-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-8 w-8 items-center justify-center text-sm text-slate-400">…</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-semibold text-white shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="Halaman {{ $page }}"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg text-slate-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif

        </div>
    </nav>
@endif
