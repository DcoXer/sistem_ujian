<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-tight text-slate-900">Profile</h2>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-5 px-4 py-6">
        <div>
            <x-back-button :href="route('dashboard')" />
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    @include('profile.partials.update-password-form')
                </div>
                <div class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
