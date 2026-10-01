@props([
    'enabled',
])

<span @class([
    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
    'bg-green-100 text-green-700' => $enabled,
    'bg-red-100 text-red-700' => ! $enabled,
])>
    {{ $enabled ? __('Enabled') : __('Disabled') }}
</span>
