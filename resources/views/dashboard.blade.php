<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    </x-slot>

    <div class="space-y-6">
        {{-- <section id="stats" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
            @foreach ($stats as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                    <p class="mt-2 text-3xl font-bold leading-none text-slate-900">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </section> --}}

        @if ($role === \App\Models\User::ROLE_ADMIN)
            @include('dashboard.role-admin')
        @elseif ($role === \App\Models\User::ROLE_AUTHOR)
            @include('dashboard.role-author')
        @elseif ($role === \App\Models\User::ROLE_OPERATOR)
            @include('dashboard.role-operator')
        @else
            @include('dashboard.role-peserta')
        @endif
    </div>
</x-app-layout>
