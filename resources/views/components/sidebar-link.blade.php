@props([
    'href',
    'label',
    'icon' => null,
    'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'mb-1 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition '.($active ? 'bg-indigo-500 text-white' : 'text-slate-200 hover:bg-slate-800'),
    ]) }}
>
    <span class="inline-flex h-7 w-7 items-center justify-center rounded-md {{ $active ? 'bg-white/20' : 'bg-slate-800 text-slate-300' }}">
        @if ($icon)
            <x-icon :name="$icon" class="h-4 w-4" />
        @endif
    </span>
    {{ $label }}
</a>
