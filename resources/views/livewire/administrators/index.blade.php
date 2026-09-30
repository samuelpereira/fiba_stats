<div class="flex h-full w-full flex-1 flex-col gap-4">
    <div class="flex items-center justify-between gap-4">
        <flux:heading size="xl">{{ __('Administrators') }}</flux:heading>

        <flux:button variant="primary" :href="route('administrators.create')" wire:navigate>
            {{ __('New administrator') }}
        </flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->administrators as $administrator)
                <flux:table.row wire:key="administrator-{{ $administrator->id }}">
                    <flux:table.cell variant="strong">{{ $administrator->name }}</flux:table.cell>
                    <flux:table.cell>{{ $administrator->email }}</flux:table.cell>
                    <flux:table.cell>{{ __($administrator->assignedRole()->label()) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="sm" :href="route('administrators.edit', $administrator)" wire:navigate>
                            {{ __('Edit') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">{{ __('No administrators yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
