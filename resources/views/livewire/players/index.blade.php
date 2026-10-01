<x-listing.page
    :title="__('Players')"
    :subtitle="__('Players on each team')"
    :action-href="route('players.create')"
    :action-label="__('New player')"
>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-zinc-200 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase">
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Number') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Team') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Enabled') }}</th>
                <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->players as $player)
                <tr wire:key="player-{{ $player->id }}" class="border-b border-zinc-100 last:border-b-0">
                    <td class="px-4 py-4 font-semibold">{{ $player->name }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ $player->number }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ $player->team->name }}</td>
                    <td class="px-4 py-4">
                        <x-listing.status :enabled="$player->enabled" />
                    </td>
                    <td class="px-4 py-4">
                        <x-listing.actions :edit-href="route('players.edit', $player)">
                            <x-slot:delete>
                                <x-listing.delete-button :record-id="$player->id" />
                            </x-slot:delete>
                        </x-listing.actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-zinc-500">{{ __('No players yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-slot:modal>
        <x-listing.delete-modal
            name="confirm-delete"
            :title="__('Delete this player?')"
            :message="__('This will permanently delete the player :name.', ['name' => $this->recordToDelete?->name])"
        />
    </x-slot:modal>
</x-listing.page>
