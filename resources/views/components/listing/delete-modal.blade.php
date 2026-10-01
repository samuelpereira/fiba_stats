@props([
    'name',
    'title',
    'message',
])

<flux:modal :name="$name" class="max-w-lg !bg-white">
    <div class="space-y-6 text-zinc-900">
        <div>
            <h2 class="text-lg font-semibold">{{ $title }}</h2>
            <p class="mt-2 text-sm text-zinc-500">{{ $message }}</p>
        </div>

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <button type="button" class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">{{ __('Cancel') }}</button>
            </flux:modal.close>

            <button type="button" wire:click="deleteConfirmed" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500">{{ __('Delete') }}</button>
        </div>
    </div>
</flux:modal>
