<section>
    <header>
        <h2 class="text-lg font-semibold text-slate-900">Keamanan Akun</h2>
        <p class="mt-1 text-sm text-slate-500">Ganti password secara berkala untuk menjaga keamanan akun.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="text-sm font-semibold text-slate-700">Password Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="text-sm font-semibold text-slate-700">Password Baru</label>
            <input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="text-sm font-semibold text-slate-700">Konfirmasi Password Baru</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Update Password
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
