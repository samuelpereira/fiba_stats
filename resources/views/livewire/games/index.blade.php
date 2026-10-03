<x-listing.page
    :title="__('Games')"
    :subtitle="__('Games of the teams')"
    :action-href="route('games.create')"
    :action-label="__('New game')"
>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-zinc-200 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase">
                <th class="px-4 py-3 font-medium">{{ __('Date and time') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Team') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Opponent') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Home or away') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Score') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->games as $game)
                <tr wire:key="game-{{ $game->uuid }}" class="border-b border-zinc-100 last:border-b-0">
                    <td class="px-4 py-4 text-zinc-600">{{ $game->date_time->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ $game->team->name }}</td>
                    <td class="px-4 py-4 font-semibold">{{ $game->opponent }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ __($game->home_away->label()) }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ $game->score() ?? '—' }}</td>
                    <td class="px-4 py-4">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $game->status->badgeClasses() }}">
                            {{ __($game->status->label()) }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <x-listing.actions :edit-href="route('games.edit', $game)">
                            <x-slot:delete>
                                <x-listing.delete-button :record-id="$game->uuid" />
                            </x-slot:delete>
                        </x-listing.actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-zinc-500">{{ __('No games yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-slot:modal>
        <x-listing.delete-modal
            name="confirm-delete"
            :title="__('Delete this game?')"
            :message="__('This will permanently delete the game against :name.', ['name' => $this->recordToDelete?->opponent])"
        />
    </x-slot:modal>
</x-listing.page>
