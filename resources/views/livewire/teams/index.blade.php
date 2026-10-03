<x-listing.page
    :title="__('Teams')"
    :subtitle="__('Teams in each season')"
    :action-href="route('teams.create')"
    :action-label="__('New team')"
>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-zinc-200 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase">
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                @can(\App\Enums\PermissionName::CreateClubs->value)
                    <th class="px-4 py-3 font-medium">{{ __('Club') }}</th>
                @endcan
                <th class="px-4 py-3 font-medium">{{ __('Season') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Enabled') }}</th>
                <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->teams as $team)
                <tr wire:key="team-{{ $team->uuid }}" class="border-b border-zinc-100 last:border-b-0">
                    <td class="px-4 py-4 font-semibold">{{ $team->name }}</td>
                    @can(\App\Enums\PermissionName::CreateClubs->value)
                        <td class="px-4 py-4 text-zinc-600">{{ $team->club->name }}</td>
                    @endcan
                    <td class="px-4 py-4 text-zinc-600">{{ $team->season->name }}</td>
                    <td class="px-4 py-4">
                        <x-listing.status :enabled="$team->enabled" />
                    </td>
                    <td class="px-4 py-4">
                        <x-listing.actions :edit-href="route('teams.edit', $team)">
                            <x-slot:delete>
                                <x-listing.delete-button :record-id="$team->uuid" />
                            </x-slot:delete>
                        </x-listing.actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()->can(\App\Enums\PermissionName::CreateClubs->value) ? 5 : 4 }}" class="px-4 py-8 text-center text-zinc-500">{{ __('No teams yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-slot:modal>
        <x-listing.delete-modal
            name="confirm-delete"
            :title="__('Delete this team?')"
            :message="__('This will permanently delete the team :name.', ['name' => $this->recordToDelete?->name])"
        />
    </x-slot:modal>
</x-listing.page>
