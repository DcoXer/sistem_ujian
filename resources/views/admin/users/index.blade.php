<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Manajemen User</h1>
    </x-slot>

    <div class="space-y-4">
        <div>
            <x-back-button :href="route('dashboard')" />
        </div>

        <livewire:admin.users-index-table :scope-role="$scopeRole ?? \App\Models\User::ROLE_STUDENT" />
    </div>
</x-app-layout>
