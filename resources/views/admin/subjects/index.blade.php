<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Mata Pelajaran</h2>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
        <div>
            <x-back-button :href="route('dashboard')" />
        </div>

        <livewire:admin.subjects-index-table />
    </div>
</x-app-layout>

