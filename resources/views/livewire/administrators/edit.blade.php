<div class="flex h-full w-full flex-1 flex-col gap-4">
    <flux:heading size="xl">{{ __('Edit administrator') }}</flux:heading>

    <form wire:submit="save" class="w-full max-w-lg space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="off" />

        @if ($this->showRoleField)
            <flux:switch wire:model="enabled" :label="__('Enabled')" :description="__('Disabled accounts cannot sign in.')" />

            <flux:select wire:model="role" :label="__('Role')" required>
                @foreach (\App\Enums\RoleName::staff() as $role)
                    <flux:select.option :value="$role->value">{{ __($role->label()) }}</flux:select.option>
                @endforeach
            </flux:select>
        @endif

        <flux:input wire:model="password" :label="__('Password')" type="password" autocomplete="new-password" viewable :description="__('Leave blank to keep the current password.')" />

        <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" autocomplete="new-password" viewable />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

            <flux:button :href="route('administrators.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
