@props([
    'editHref',
])

<div class="flex items-center justify-end gap-3">
    <a href="{{ $editHref }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-zinc-600 hover:text-zinc-900">
        <flux:icon name="pencil" class="size-4" />
        {{ __('Edit') }}
    </a>

    @isset($delete)
        {{ $delete }}
    @endisset
</div>
