<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistem Ujian') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 font-['Plus_Jakarta_Sans'] text-slate-800 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-6">
        <div class="grid w-full grid-cols-1 gap-10 lg:grid-cols-2 lg:gap-16">
            
            <!-- Informational Panel -->
            <section class="hidden lg:flex flex-col justify-center">
                <div class="max-w-md">
                    <h1 class="text-3xl font-semibold text-slate-900">
                        Sistem Ujian Terintegrasi
                    </h1>
                    <p class="mt-3 text-slate-600">
                        Platform resmi untuk pengelolaan dan pelaksanaan ujian berbasis peran.
                    </p>

                    <ul class="mt-6 space-y-2 text-sm text-slate-600">
                        <li>• Admin menyiapkan dan mempublikasikan ujian</li>
                        <li>• Operator memantau pelaksanaan</li>
                        <li>• Peserta mengerjakan dan melihat hasil</li>
                    </ul>
                </div>
            </section>

            <!-- Auth Slot -->
            <section class="flex items-center justify-center">
                {{ $slot }}
            </section>

        </div>
    </main>
</body>
</html>
