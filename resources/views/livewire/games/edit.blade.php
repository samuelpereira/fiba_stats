<div class="flex h-full w-full flex-1 flex-col gap-4">
    <flux:heading size="xl">{{ __('Edit game') }}</flux:heading>

    <form wire:submit="save" class="w-full max-w-lg space-y-6">
        @include('livewire.games.fields')

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

            <flux:button :href="route('games.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>

    @can('importLog', $game)
        <div class="mt-10 w-full max-w-lg space-y-4 border-t border-zinc-200 pt-8">
            <flux:heading size="lg">{{ __('Statistics log') }}</flux:heading>

            <flux:text>
                @if ($this->hasStatistics)
                    {{ __('This game has a statistics log.') }}
                @else
                    {{ __('This game has no statistics log.') }}
                @endif
            </flux:text>

            <flux:text>{{ __('Importing replaces every event in this game.') }}</flux:text>

            <form wire:submit="importLog" class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-zinc-800">{{ __('CSV file') }}</label>
                    <input wire:model="logFile" type="file" accept=".csv,text/csv,.txt" class="mt-2 block w-full text-sm" />
                    @error('logFile')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <flux:button variant="primary" type="submit">{{ __('Import log') }}</flux:button>
            </form>
        </div>
    @endcan
</div>
