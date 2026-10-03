<div class="flex h-full w-full flex-1 flex-col gap-4">
    <flux:heading size="xl">{{ __('Edit user') }}</flux:heading>

    <form wire:submit="save" class="w-full max-w-lg space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="off" />

        @can(\App\Enums\PermissionName::CreateClubs->value)
            <flux:select wire:model.live="clubId" :label="__('Club')" required>
                @foreach ($this->clubs as $club)
                    <flux:select.option :value="$club->id">{{ $club->name }}</flux:select.option>
                @endforeach
            </flux:select>
        @endcan

        @include('livewire.users.partials.team-assignment')

        @if (auth()->id() !== $user->id)
            <flux:switch wire:model="enabled" :label="__('Enabled')" :description="__('Disabled accounts cannot sign in.')" />
        @endif

        <flux:input wire:model="password" :label="__('Password')" type="password" autocomplete="new-password" viewable :description="__('Leave blank to keep the current password.')" />

        <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" autocomplete="new-password" viewable />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

            <flux:button :href="route('users.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
