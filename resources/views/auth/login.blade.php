<x-guest-layout>
    <div class="mx-auto w-full max-w-md rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="mb-8 flex items-center gap-4">
            <div class="flex h-11 w-11 items-center justify-center rounded-lg border border-slate-300 bg-slate-100">
                <span class="text-sm font-bold tracking-wide text-slate-700">UJ</span>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-slate-900">Sistem Ujian</h1>
                <p class="text-sm text-slate-500">Silakan masuk untuk melanjutkan</p>
            </div>
        </div>

        <x-auth-session-status
            class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700"
            :status="session('status')"
        />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">
                    Email
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-rose-600" />
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">
                    Password
                </label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-rose-600" />
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2 text-slate-600">
                    <input
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    Ingat saya
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:underline">
                        Lupa password?
                    </a>
                @endif
            </div>

            <button
                type="submit"
                class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
            >
                Masuk
            </button>
        </form>
    </div>
</x-guest-layout>
