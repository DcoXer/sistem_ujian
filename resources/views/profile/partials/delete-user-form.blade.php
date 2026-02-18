<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-rose-700">Hapus Akun</h2>
        <p class="mt-1 text-sm text-slate-600">Aksi ini permanen. Semua data akun kamu akan dihapus dan tidak bisa dikembalikan.</p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center rounded-lg border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100"
    >
        Hapus Akun
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-slate-900">Yakin ingin menghapus akun?</h2>

            <p class="mt-1 text-sm text-slate-600">Masukkan password untuk konfirmasi penghapusan akun permanen.</p>

            <div class="mt-6">
                <label for="password" class="text-sm font-semibold text-slate-700">Password</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    placeholder="Masukkan password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" x-on:click="$dispatch('close')" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                    Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>
