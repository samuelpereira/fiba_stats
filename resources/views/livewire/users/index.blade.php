<x-listing.page
    :title="__('Users')"
    :subtitle="__('Users of the clubs')"
    :action-href="route('users.create')"
    :action-label="__('New user')"
>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-zinc-200 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase">
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Email') }}</th>
                @can(\App\Enums\PermissionName::CreateClubs->value)
                    <th class="px-4 py-3 font-medium">{{ __('Club') }}</th>
                @endcan
                <th class="px-4 py-3 font-medium">{{ __('Enabled') }}</th>
                <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->users as $user)
                <tr wire:key="user-{{ $user->id }}" class="border-b border-zinc-100 last:border-b-0">
                    <td class="px-4 py-4 font-semibold">{{ $user->name }}</td>
                    <td class="px-4 py-4 text-zinc-600">{{ $user->email }}</td>
                    @can(\App\Enums\PermissionName::CreateClubs->value)
                        <td class="px-4 py-4 text-zinc-600">{{ $user->club?->name }}</td>
                    @endcan
                    <td class="px-4 py-4">
                        <x-listing.status :enabled="$user->enabled" />
                    </td>
                    <td class="px-4 py-4">
                        <x-listing.actions :edit-href="route('users.edit', $user)">
                            <x-slot:delete>
                                <x-listing.delete-button :record-id="$user->id" />
                            </x-slot:delete>
                        </x-listing.actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()->can(\App\Enums\PermissionName::CreateClubs->value) ? 5 : 4 }}" class="px-4 py-8 text-center text-zinc-500">{{ __('No users yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-slot:modal>
        <x-listing.delete-modal
            name="confirm-delete"
            :title="__('Delete this user?')"
            :message="__('This will permanently delete the user :name.', ['name' => $this->recordToDelete?->name])"
        />
    </x-slot:modal>
</x-listing.page>
