@props([
    'href',
    'label' => 'Kembali',
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200',
    ]) }}
>
    <span aria-hidden="true">&larr;</span>
    <span>{{ $label }}</span>
</a>
