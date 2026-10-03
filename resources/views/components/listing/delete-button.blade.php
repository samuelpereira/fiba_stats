@props([
    'recordId',
])

<button type="button" wire:click="confirmDelete('{{ $recordId }}')" class="inline-flex items-center gap-1.5 text-sm text-zinc-600 hover:text-zinc-900">
    <flux:icon name="trash" class="size-4" />
    {{ __('Delete') }}
</button>
