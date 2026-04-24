<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($code ?? 'Error') . ' - ' . config('app.name', 'Sistem Ujian') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-['Plus_Jakarta_Sans'] text-slate-100 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-20 top-0 h-72 w-72 rounded-full bg-cyan-500/20 blur-3xl"></div>
        <div class="absolute right-0 top-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl"></div>
    </div>

    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-6 py-10">
        <div class="w-full max-w-3xl rounded-3xl border border-slate-800 bg-slate-600 p-8 text-center shadow-2xl backdrop-blur sm:p-10">
            <p class="text-4xl font-extrabold uppercase tracking-[0.2em] text-white">Error {{ $code ?? '000' }}</p>
            <h1 class="mt-3 text-3xl font-extrabold text-white sm:text-4xl">{{ $title ?? 'Terjadi Kesalahan' }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-slate-300 sm:text-base">
                {{ $message ?? 'Terjadi kendala saat memproses permintaan Anda.' }}
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-slate-300">
                        Kembali ke Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-slate-300">
                        Ke Halaman Login
                    </a>
                @endauth
                <a href="{{ url()->previous() }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:border-slate-500 hover:bg-slate-800">
                    Kembali
                </a>
            </div>
        </div>
    </main>
</body>
</html>
