<x-listing.page
    :title="__('Clubs')"
    :subtitle="__('Clubs and their access')"
    :action-href="route('clubs.create')"
    :action-label="__('New club')"
>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-zinc-200 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase">
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Access') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Enabled') }}</th>
                <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->clubs as $club)
                <tr wire:key="club-{{ $club->id }}" class="border-b border-zinc-100 last:border-b-0">
                    <td class="px-4 py-4 font-semibold">{{ $club->name }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ $club->access?->email }}</td>
                    <td class="px-4 py-4">
                        @if ($club->access)
                            <x-listing.status :enabled="$club->access->enabled" />
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <x-listing.actions :edit-href="route('clubs.edit', $club)">
                            <x-slot:delete>
                                <x-listing.delete-button :record-id="$club->id" />
                            </x-slot:delete>
                        </x-listing.actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-zinc-500">{{ __('No clubs yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-slot:modal>
        <x-listing.delete-modal
            name="confirm-delete"
            :title="__('Delete this club?')"
            :message="__('This will permanently delete the club :name and its users.', ['name' => $this->recordToDelete?->name])"
        />
    </x-slot:modal>
</x-listing.page>
