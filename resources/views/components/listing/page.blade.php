@props([
    'title',
    'subtitle',
    'actionHref',
    'actionLabel',
])

<div class="-m-6 flex min-h-dvh flex-col gap-6 bg-white p-6 text-zinc-900 lg:-m-8 lg:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
            <p class="mt-1 text-sm text-zinc-500">{{ $subtitle }}</p>
        </div>

        <a href="{{ $actionHref }}" wire:navigate class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 sm:w-auto">
            <flux:icon name="plus" class="size-4" />
            {{ $actionLabel }}
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs">
        {{ $slot }}
    </div>

    @isset($modal)
        {{ $modal }}
    @endisset
</div>
