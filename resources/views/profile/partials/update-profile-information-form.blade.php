<section>
    <header>
        <h2 class="text-lg font-semibold text-slate-900">Informasi Profile</h2>
        <p class="mt-1 text-sm text-slate-500">Perbarui data akun dan foto profile kamu.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="profile_photo" class="text-sm font-semibold text-slate-700">Foto Profil</label>
            <div class="mt-2 flex items-center gap-4">
                @if ($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="Foto profil" class="h-16 w-16 rounded-full object-cover ring-2 ring-slate-200">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-200 text-lg font-bold text-slate-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <input id="profile_photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800" />
                    <p class="mt-1 text-xs text-slate-500">Format: JPG/PNG/WEBP, maksimal 2MB.</p>
                    <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remove_profile_photo" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Hapus foto profil saat simpan
                    </label>
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>

        <div>
            <label for="name" class="text-sm font-semibold text-slate-700">Nama</label>
            <input id="name" name="name" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
            <input id="email" name="email" type="email" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-slate-700">
                        Email kamu belum terverifikasi.

                        <button form="send-verification" class="ml-1 rounded-md text-sm font-semibold text-indigo-600 underline hover:text-indigo-700 focus:outline-none">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-600">
                            Link verifikasi baru sudah dikirim ke email kamu.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
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
