<div class="flex h-full w-full flex-1 flex-col gap-4">
    <div class="flex items-center justify-between gap-4">
        <flux:heading size="xl">{{ __('Clubs') }}</flux:heading>

        <flux:button variant="primary" :href="route('clubs.create')" wire:navigate>
            {{ __('New club') }}
        </flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Access') }}</flux:table.column>
            <flux:table.column>{{ __('Enabled') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->clubs as $club)
                <flux:table.row wire:key="club-{{ $club->id }}">
                    <flux:table.cell variant="strong">{{ $club->name }}</flux:table.cell>
                    <flux:table.cell>{{ $club->access?->email }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($club->access)
                            <flux:badge :color="$club->access->enabled ? 'green' : 'red'" size="sm">{{ $club->access->enabled ? __('Enabled') : __('Disabled') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="sm" :href="route('clubs.edit', $club)" wire:navigate>
                            {{ __('Edit') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">{{ __('No clubs yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
