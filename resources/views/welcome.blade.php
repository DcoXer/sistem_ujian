<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sistem Ujian') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 top-0 h-72 w-72 rounded-full bg-cyan-500/20 blur-3xl"></div>
        <div class="absolute right-0 top-32 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-emerald-500/10 blur-3xl"></div>
    </div>

    <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-5 lg:px-8">
        <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-content-center rounded-xl bg-cyan-500/20 ring-1 ring-cyan-300/40">
                <span class="text-lg font-bold text-cyan-200">U</span>
            </div>
            <div>
                <p class="text-sm text-slate-300">Platform</p>
                <h1 class="text-lg font-semibold tracking-wide">Sistem Ujian</h1>
            </div>
        </div>
        <nav class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-cyan-400">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:border-slate-500">
                    Login
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-cyan-400">
                        Register
                    </a>
                @endif
            @endauth
        </nav>
    </header>

    <main class="mx-auto w-full max-w-7xl px-6 pb-16 lg:px-8">
        <section class="grid gap-8 rounded-3xl border border-slate-800 bg-slate-900/70 p-8 backdrop-blur-sm lg:grid-cols-[1.15fr_0.85fr] lg:p-10">
            <div>
                <span class="inline-flex rounded-full border border-cyan-400/40 bg-cyan-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-200">
                    Secure Exam Workflow
                </span>
                <h2 class="mt-4 text-3xl font-black leading-tight text-white lg:text-5xl">
                    Ujian terstruktur, hasil valid, audit jelas.
                </h2>
                <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-300 lg:text-base">
                    Kelola ujian dari draft sampai hasil akhir dengan alur yang ketat: publish, pengerjaan peserta, lock jawaban, scoring otomatis, lalu tampilkan hasil.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-cyan-400">
                            Masuk Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-cyan-400">
                            Mulai Sekarang
                        </a>
                    @endauth
                </div>
            </div>

            <div class="grid gap-3 rounded-2xl border border-slate-800 bg-slate-900 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alur Besar</p>
                <ol class="space-y-2 text-sm">
                    <li class="rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2">1. Ujian dibuat</li>
                    <li class="rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2">2. Ujian dipublish</li>
                    <li class="rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2">3. Peserta ikut ujian</li>
                    <li class="rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2">4. Jawaban dikunci</li>
                    <li class="rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2">5. Nilai dihitung</li>
                    <li class="rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2">6. Hasil ditampilkan</li>
                </ol>
            </div>
        </section>

        <section class="mt-8 grid gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-rose-300/20 bg-rose-500/5 p-5">
                <h3 class="text-lg font-bold text-rose-200">Admin</h3>
                <p class="mt-2 text-sm text-slate-300">Kelola exam, publish, dan monitor seluruh hasil.</p>
                <p class="mt-4 text-xs uppercase tracking-wide text-slate-400">Create • Update • Report</p>
            </article>
            <article class="rounded-2xl border border-amber-300/20 bg-amber-500/5 p-5">
                <h3 class="text-lg font-bold text-amber-200">Operator</h3>
                <p class="mt-2 text-sm text-slate-300">Pantau ujian aktif dan bantu operasional peserta.</p>
                <p class="mt-4 text-xs uppercase tracking-wide text-slate-400">Monitor • Support • Manual Score</p>
            </article>
            <article class="rounded-2xl border border-emerald-300/20 bg-emerald-500/5 p-5">
                <h3 class="text-lg font-bold text-emerald-200">Peserta</h3>
                <p class="mt-2 text-sm text-slate-300">Kerjakan ujian sekali, submit, lalu lihat nilai sendiri.</p>
                <p class="mt-4 text-xs uppercase tracking-wide text-slate-400">Start • Answer • Submit</p>
            </article>
        </section>
    </main>
</body>
</html>
