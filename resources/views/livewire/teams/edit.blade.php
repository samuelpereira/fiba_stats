<div class="flex h-full w-full flex-1 flex-col gap-4">
    <flux:heading size="xl">{{ __('Edit team') }}</flux:heading>

    <form wire:submit="save" class="w-full max-w-lg space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus />

        @can(\App\Enums\PermissionName::CreateClubs->value)
            <flux:select wire:model="clubId" :label="__('Club')" required>
                <flux:select.option value="">{{ __('Select a club') }}</flux:select.option>
                @foreach ($this->clubs as $club)
                    <flux:select.option :value="$club->id">{{ $club->name }}</flux:select.option>
                @endforeach
            </flux:select>
        @endcan

        <flux:select wire:model="seasonId" :label="__('Season')" required>
            <flux:select.option value="">{{ __('Select a season') }}</flux:select.option>
            @foreach ($this->seasons as $season)
                <flux:select.option :value="$season->id">{{ $season->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="gender" :label="__('Gender')" required>
            <flux:select.option value="">{{ __('Select a gender') }}</flux:select.option>
            @foreach (\App\Enums\Gender::cases() as $gender)
                <flux:select.option :value="$gender->value">{{ __($gender->label()) }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="ageGroup" :label="__('Age group')" type="text" />

        <flux:input wire:model="competition" :label="__('Competition')" type="text" />

        <flux:textarea wire:model="description" :label="__('Description')" />

        <flux:input wire:model="fpbUrl" :label="__('FPB URL')" type="url" />

        <flux:input wire:model="order" :label="__('Order')" type="number" min="0" required />

        @if ($team->photo_url)
            <img src="{{ $team->photo_url }}" alt="" class="h-24 w-24 rounded-lg object-cover" />
        @endif

        <flux:input wire:model="photo" :label="__('Photo')" type="file" accept="image/*" />

        <flux:switch wire:model="enabled" :label="__('Enabled')" />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

            <flux:button :href="route('teams.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
