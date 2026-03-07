@props([
    'role',
    'sections' => [],
])

<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 h-dvh w-72 -translate-x-full bg-slate-900 text-slate-100 shadow-2xl transition-transform duration-300 lg:translate-x-0">
    <div class="flex h-full min-h-0 flex-col">
        <div class="flex items-center justify-between border-b border-slate-800 px-5 py-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 text-sm font-bold text-white">PTSO</span>
                <span class="text-lg font-semibold">PTS Online</span>
            </a>
            <button id="sidebar-close" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 text-slate-200 hover:bg-slate-800 lg:hidden">
                <x-icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <div class="px-5 py-4">
            <div class="rounded-xl border border-slate-800 bg-slate-800/50 px-4 py-3">
                <p class="text-xs uppercase tracking-wide text-slate-400">Role</p>
                <p class="mt-1 text-sm font-semibold text-indigo-300">{{ strtoupper($role) }}</p>
            </div>
        </div>

        <nav class="flex-1 space-y-4 overflow-y-auto px-3 pb-5 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            @foreach ($sections as $section)
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $section['title'] }}</p>
                    @foreach ($section['items'] as $link)
                        @if (! empty($link['children'] ?? []))
                            @php
                                $isParentActive = isActiveRoute($link['active'] ?? []);
                            @endphp
                            <details class="group mb-1" {{ $isParentActive ? 'open' : '' }}>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ $isParentActive ? 'bg-indigo-500 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                                    <span class="flex items-center gap-3">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-md {{ $isParentActive ? 'bg-white/20' : 'bg-slate-800 text-slate-300' }}">
                                            @if (! empty($link['icon']))
                                                <x-icon :name="$link['icon']" class="h-4 w-4" />
                                            @endif
                                        </span>
                                        {{ $link['label'] }}
                                    </span>
                                    <x-icon name="chevron-down" class="h-4 w-4 transition group-open:rotate-180" />
                                </summary>
                                <div class="mt-1 space-y-1 pl-4">
                                    @foreach ($link['children'] as $child)
                                        <x-sidebar-link
                                            :href="route($child['route'])"
                                            :label="$child['label']"
                                            :active="isActiveRoute($child['active'] ?? [])"
                                        />
                                    @endforeach
                                </div>
                            </details>
                        @else
                            <x-sidebar-link
                                :href="route($link['route'])"
                                :label="$link['label']"
                                :icon="$link['icon'] ?? null"
                                :active="isActiveRoute($link['active'] ?? [])"
                            />
                        @endif
                    @endforeach
                </div>
            @endforeach
        </nav>
    </div>
</aside>
