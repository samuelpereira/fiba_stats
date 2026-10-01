<div class="flex h-full w-full flex-1 flex-col gap-4">
    <flux:heading size="xl">{{ __('New game') }}</flux:heading>

    <form wire:submit="save" class="w-full max-w-lg space-y-6">
        @include('livewire.games.fields')

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

            <flux:button :href="route('games.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
