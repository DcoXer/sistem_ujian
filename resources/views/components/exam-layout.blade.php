<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Ujian') }} — Ujian</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none !important;}</style>
    </head>
    <body class="bg-slate-100 font-['Plus_Jakarta_Sans'] antialiased">

        {{-- Top bar sticky --}}
        <header class="fixed inset-x-0 top-0 z-50 border-b border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
                {{-- Kiri: icon + judul + autosave --}}
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white">
                        <x-icon name="document" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Sedang Ujian</p>
                        <p class="truncate text-sm font-bold text-slate-900">{{ $title ?? 'Ujian' }}</p>
                    </div>
                </div>

                {{-- Tengah: autosave (hidden di mobile) --}}
                <p id="autosave-status" class="hidden truncate text-xs text-slate-400 sm:block">Autosimpan aktif.</p>

                {{-- Kanan: timer --}}
                <div class="flex shrink-0 items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2">
                    <x-icon name="clock" class="h-4 w-4 text-rose-500" />
                    <div class="text-right">
                        <p class="text-[9px] font-semibold uppercase tracking-wide text-rose-500">Sisa Waktu</p>
                        <p id="timer-display" class="text-lg font-bold leading-none text-rose-700">--:--</p>
                    </div>
                </div>
            </div>

            {{-- Progress bar tipis di bawah top bar --}}
            <div class="h-1 w-full bg-slate-100">
                <div id="progress-bar" class="h-1 bg-indigo-500 transition-all duration-300" style="width: 0%"></div>
            </div>
        </header>

        {{-- Main content (pt untuk offset fixed header) --}}
        <main class="min-h-screen pt-[72px]">
            {{ $slot }}
        </main>

    </body>
</html>
