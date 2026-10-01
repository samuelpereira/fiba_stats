<div class="flex h-full w-full flex-1 flex-col gap-4">
    <div class="flex items-center justify-between gap-4">
        <flux:heading size="xl">{{ __('Users') }}</flux:heading>

        <flux:button variant="primary" :href="route('users.create')" wire:navigate>
            {{ __('New user') }}
        </flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            @can(\App\Enums\PermissionName::CreateClubs->value)
                <flux:table.column>{{ __('Club') }}</flux:table.column>
            @endcan
            <flux:table.column>{{ __('Enabled') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->users as $user)
                <flux:table.row wire:key="user-{{ $user->id }}">
                    <flux:table.cell variant="strong">{{ $user->name }}</flux:table.cell>
                    <flux:table.cell>{{ $user->email }}</flux:table.cell>
                    @can(\App\Enums\PermissionName::CreateClubs->value)
                        <flux:table.cell>{{ $user->club?->name }}</flux:table.cell>
                    @endcan
                    <flux:table.cell>
                        <flux:badge :color="$user->enabled ? 'green' : 'red'" size="sm">{{ $user->enabled ? __('Enabled') : __('Disabled') }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="sm" :href="route('users.edit', $user)" wire:navigate>
                            {{ __('Edit') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="{{ auth()->user()->can(\App\Enums\PermissionName::CreateClubs->value) ? 5 : 4 }}">{{ __('No users yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
