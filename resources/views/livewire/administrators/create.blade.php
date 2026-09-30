<div class="flex h-full w-full flex-1 flex-col gap-4">
    <flux:heading size="xl">{{ __('New administrator') }}</flux:heading>

    <form wire:submit="save" class="w-full max-w-lg space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="off" />

        <flux:select wire:model="role" :label="__('Role')" required>
            @foreach (\App\Enums\RoleName::staff() as $role)
                <flux:select.option :value="$role->value">{{ __($role->label()) }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="password" :label="__('Password')" type="password" required autocomplete="new-password" viewable />

        <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" viewable />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

            <flux:button :href="route('administrators.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
