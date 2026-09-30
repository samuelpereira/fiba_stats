<div class="flex h-full w-full flex-1 flex-col gap-4">
    <flux:heading size="xl">{{ __('Edit club') }}</flux:heading>

    <form wire:submit="save" class="w-full max-w-lg space-y-6">
        <flux:input wire:model="name" :label="__('Club name')" type="text" required autofocus />

        <flux:input wire:model="accessName" :label="__('Access name')" type="text" required autocomplete="name" />

        <flux:input wire:model="accessEmail" :label="__('Access email')" type="email" required autocomplete="off" />

        <flux:input wire:model="password" :label="__('Password')" type="password" autocomplete="new-password" viewable :description="__('Leave blank to keep the current password.')" />

        <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" autocomplete="new-password" viewable />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

            <flux:button :href="route('clubs.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
