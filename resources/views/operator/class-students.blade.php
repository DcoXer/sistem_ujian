<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Siswa Kelas {{ $class->name }}</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-6">
        <div class="flex items-center justify-between gap-3">
            <x-back-button :href="route('dashboard')" />
            <a href="{{ route('operator.exams.index') }}" class="inline-flex rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                Ke Monitoring Ujian
            </a>
        </div>

        <livewire:operator.class-students-table :class-id="$class->id" />
    </div>
</x-app-layout>

