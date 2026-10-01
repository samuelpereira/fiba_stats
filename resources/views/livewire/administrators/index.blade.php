<x-listing.page
    :title="__('Administrators')"
    :subtitle="__('Administrators and managers')"
    :action-href="route('administrators.create')"
    :action-label="__('New administrator')"
>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-zinc-200 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase">
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Email') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Role') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Enabled') }}</th>
                <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->administrators as $administrator)
                <tr wire:key="administrator-{{ $administrator->id }}" class="border-b border-zinc-100 last:border-b-0">
                    <td class="px-4 py-4 font-semibold">{{ $administrator->name }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ $administrator->email }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ __($administrator->assignedRole()->label()) }}</td>
                    <td class="px-4 py-4">
                        <x-listing.status :enabled="$administrator->enabled" />
                    </td>
                    <td class="px-4 py-4">
                        <x-listing.actions :edit-href="route('administrators.edit', $administrator)">
                            @if (auth()->id() !== $administrator->id)
                                <x-slot:delete>
                                    <x-listing.delete-button :record-id="$administrator->id" />
                                </x-slot:delete>
                            @endif
                        </x-listing.actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-zinc-500">{{ __('No administrators yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-slot:modal>
        <x-listing.delete-modal
            name="confirm-delete"
            :title="__('Delete this administrator?')"
            :message="__('This will permanently delete the user :name.', ['name' => $this->recordToDelete?->name])"
        />
    </x-slot:modal>
</x-listing.page>
