<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div class="flex flex-col gap-6">
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
            </flux:radio.group>

            <div class="flex flex-col gap-2">
                <flux:heading>{{ __('Language') }}</flux:heading>

                <flux:radio.group wire:model.live="locale" variant="segmented">
                    @foreach (\App\Support\Locale::supported() as $code => $label)
                        <flux:radio :value="$code">{{ $label }}</flux:radio>
                    @endforeach
                </flux:radio.group>
            </div>
        </div>
    </x-settings.layout>
</section>
