<x-listing.page
    :title="__('Seasons')"
    :subtitle="__('Sports seasons of the club')"
    :action-href="route('seasons.create')"
    :action-label="__('New season')"
>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-zinc-200 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase">
                <th class="px-4 py-3 font-medium">{{ __('Season') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Period') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Teams') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->seasons as $season)
                <tr wire:key="season-{{ $season->id }}" class="border-b border-zinc-100 last:border-b-0">
                    <td class="px-4 py-4 font-semibold">{{ $season->name }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ $season->starts_at->format('d/m/Y') }} – {{ $season->ends_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ $season->teams_count }}</td>
                    <td class="px-4 py-4">
                        @if ($season->is_current)
                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">{{ __('Current') }}</span>
                        @else
                            <button type="button" wire:click="markAsCurrent({{ $season->id }})" class="text-sm text-zinc-500 hover:text-zinc-900">
                                {{ __('Mark as current') }}
                            </button>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <x-listing.actions :edit-href="route('seasons.edit', $season)">
                            <x-slot:delete>
                                <x-listing.delete-button :record-id="$season->id" />
                            </x-slot:delete>
                        </x-listing.actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-zinc-500">{{ __('No seasons yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-slot:modal>
        <x-listing.delete-modal
            name="confirm-delete"
            :title="__('Delete this season?')"
            :message="__('This will permanently delete the season :name.', ['name' => $this->recordToDelete?->name])"
        />
    </x-slot:modal>
</x-listing.page>
