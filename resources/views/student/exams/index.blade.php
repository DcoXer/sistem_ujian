<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Ujian</h2>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-6 space-y-4">
        <div>
            <x-back-button :href="route('dashboard')" />
        </div>

        <livewire:student.exams-index-table />
    </div>
</x-app-layout>

