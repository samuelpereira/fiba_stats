@props([
    'label' => null,
    'primary' => false,
    'actions' => false,
])

@php
    $dataLabel = $primary ? 'primary' : ($actions ? 'actions' : $label);
@endphp

<td
    @if ($dataLabel !== null)
        data-label="{{ $dataLabel }}"
    @endif
    {{ $attributes->class([
        'px-4 py-4',
        'font-semibold' => $primary,
        'text-zinc-600' => ! $primary && ! $actions,
        'sm:text-right' => $actions,
    ]) }}
>{{ $slot }}</td>
