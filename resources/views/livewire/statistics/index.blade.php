<div class="-m-6 flex min-h-dvh flex-col gap-6 bg-white p-6 text-zinc-900 lg:-m-8 lg:p-8">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('Statistics') }}</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ __('Games of your club') }}</p>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-xs">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-200 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase">
                    <th class="px-4 py-3 font-medium">{{ __('Date and time') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Team') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Opponent') }}</th>
                    <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->games as $game)
                    <tr wire:key="game-{{ $game->uuid }}" class="border-b border-zinc-100 last:border-b-0">
                        <td class="px-4 py-4 text-zinc-600">{{ $game->date_time->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-4 font-semibold">{{ $game->team->name }}</td>
                        <td class="px-4 py-4 text-zinc-600">{{ $game->opponent }}</td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('statistics.record', $game) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-medium text-zinc-900 hover:text-zinc-600">
                                <flux:icon name="clipboard-document-list" class="size-4" />
                                {{ __('Collect') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-zinc-500">{{ __('No games yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
