<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Kelola Soal Ujian</h1>
    </x-slot>

    <div class="space-y-4">
        <div>
            <x-back-button :href="route('dashboard')" />
        </div>

        <livewire:teacher.exams-index-table />
    </div>
</x-app-layout>

