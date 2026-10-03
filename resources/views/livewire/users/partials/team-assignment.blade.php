<flux:fieldset>
    <flux:legend>{{ __('Assigned teams') }}</flux:legend>
    <flux:description>{{ __('Users can collect statistics only for these teams.') }}</flux:description>

    @if ($this->assignableTeams->isEmpty())
        <p class="text-sm text-zinc-500">
            @can(\App\Enums\PermissionName::CreateClubs->value)
                {{ __('Select a club to choose teams.') }}
            @else
                {{ __('No teams available for this club.') }}
            @endcan
        </p>
    @else
        <div class="mt-3 space-y-2">
            @foreach ($this->assignableTeams as $team)
                <flux:checkbox
                    wire:model="teamIds"
                    :value="(string) $team->id"
                    :label="$team->name"
                    :description="$team->season?->name"
                />
            @endforeach
        </div>
    @endif

    <flux:error name="teamIds" />
</flux:fieldset>
