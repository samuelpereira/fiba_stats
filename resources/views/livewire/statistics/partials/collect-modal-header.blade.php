@props([
    'title',
    'cancelAction',
])

<header class="shrink-0 border-b border-zinc-200 bg-white">
    <div class="mx-auto grid w-full grid-cols-3 items-center px-4 py-3">
        <button type="button" wire:click="{{ $cancelAction }}" class="inline-flex items-center gap-2 text-sm font-medium text-zinc-900">
            <flux:icon name="arrow-left" class="size-4" />
            {{ __('Back') }}
        </button>
        <h1 class="truncate text-center text-sm font-medium sm:text-base">{{ $title }}</h1>
        <span></span>
    </div>
</header>
