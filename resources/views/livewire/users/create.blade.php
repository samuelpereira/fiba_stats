<div class="flex h-full w-full flex-1 flex-col gap-4">
    <flux:heading size="xl">{{ __('New user') }}</flux:heading>

    <form wire:submit="save" class="w-full max-w-lg space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="off" />

        @can(\App\Enums\PermissionName::CreateClubs->value)
            <flux:select wire:model="clubId" :label="__('Club')" required>
                <flux:select.option value="">{{ __('Select a club') }}</flux:select.option>
                @foreach ($this->clubs as $club)
                    <flux:select.option :value="$club->id">{{ $club->name }}</flux:select.option>
                @endforeach
            </flux:select>
        @endcan

        <flux:input wire:model="password" :label="__('Password')" type="password" required autocomplete="new-password" viewable />

        <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" viewable />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

            <flux:button :href="route('users.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
