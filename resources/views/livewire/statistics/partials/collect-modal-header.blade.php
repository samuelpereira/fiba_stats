@props([
    'title',
    'cancelAction',
])

<header class="grid shrink-0 grid-cols-3 items-center px-4 py-3">
    <span></span>
    <h1 class="pointer-events-none min-w-0 truncate text-center text-sm font-medium sm:text-base">{{ $title }}</h1>
    <button
        type="button"
        wire:click="{{ $cancelAction }}"
        class="relative z-10 justify-self-end rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-800"
    >{{ __('Cancel') }}</button>
</header>
