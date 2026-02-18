<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Ujian') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none !important;}</style>
    </head>
    <body class="font-['Plus_Jakarta_Sans'] antialiased">
        @php
            $user = auth()->user();
            $role = $user?->role ?? config('sidebar.default_role');
            $sidebarSections = config("sidebar.roles.$role", config('sidebar.roles.'.config('sidebar.default_role'), []));
            $examUiActions = \App\Support\ExamUiAction::all();
            $examUiStates = \App\Support\ExamUiState::all();
            $popupStatus = session('status');
            $popupError = session('error')
                ?? $errors->first('delete')
                ?? $errors->first('publish')
                ?? $errors->first('role');
            $userInitial = strtoupper(substr(trim((string) ($user?->name ?? 'U')), 0, 1));
        @endphp

        <div
            id="app-layout-root"
            class="min-h-screen bg-slate-100"
            data-popup-status="{{ $popupStatus ?? '' }}"
            data-popup-error="{{ $popupError ?? '' }}"
            data-server-now-ms="{{ now()->getTimestamp() * 1000 }}"
            data-exam-ui-actions='@json($examUiActions)'
            data-exam-ui-states='@json($examUiStates)'
        >
            <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden"></div>

            <x-sidebar :role="$role" :sections="$sidebarSections" />

            <div class="min-h-screen lg:pl-72">
                <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
                    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button id="sidebar-open" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 lg:hidden">
                                <x-icon name="menu" class="h-5 w-5" />
                            </button>
                            <div class="text-slate-900">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    <h1 class="text-2xl font-bold">Dashboard</h1>
                                @endisset
                                <p id="app-live-clock" class="text-sm text-slate-500" data-timezone="{{ config('app.timezone', 'Asia/Jakarta') }}">{{ now()->format('l, d M Y H:i:s') }} WIB</p>
                            </div>
                        </div>

                        <details class="relative">
                            <summary class="list-none inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                @if ($user?->profile_photo_url)
                                    <img src="{{ $user->profile_photo_url }}" alt="Avatar" class="h-8 w-8 rounded-full object-cover ring-1 ring-slate-200">
                                @else
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700">
                                        {{ $userInitial }}
                                    </span>
                                @endif
                                <span>{{ $user?->name }}</span>
                                <x-icon name="chevron-down" class="h-4 w-4" />
                            </summary>
                            <div class="absolute right-0 mt-2 w-44 rounded-lg border border-slate-200 bg-white p-1 shadow-lg">
                                <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">Logout</button>
                                </form>
                            </div>
                        </details>
                    </div>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <div id="app-toast-container" class="pointer-events-none fixed left-1/2 top-20 z-[100] flex w-[calc(100%-1rem)] max-w-md -translate-x-1/2 flex-col gap-2 sm:top-6 sm:w-full"></div>

        <div id="app-confirm-modal" class="fixed inset-0 z-[110] hidden">
            <div id="app-confirm-backdrop" class="absolute inset-0 bg-slate-900/60"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <h3 id="app-confirm-title" class="text-lg font-bold text-slate-900">Konfirmasi</h3>
                    <p id="app-confirm-message" class="mt-2 text-sm text-slate-600">Apakah Anda yakin?</p>
                    <div class="mt-5 flex items-center justify-end gap-2">
                        <button id="app-confirm-cancel" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Batal
                        </button>
                        <button id="app-confirm-ok" type="button" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                            Ya, lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
