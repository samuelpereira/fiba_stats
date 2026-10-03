<table class="w-full table-fixed text-left text-sm">
    <thead>
        <tr class="border-b border-zinc-200 text-xs font-medium tracking-wide text-zinc-500 uppercase">
            <th class="w-14 px-2 py-2 font-medium">{{ __('Clock') }}</th>
            <th class="px-2 py-2 font-medium">{{ __('Player') }}</th>
            <th class="px-2 py-2 text-right font-medium">{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lines as $line)
            <tr
                wire:key="{{ $keyPrefix }}-{{ $line['id'] }}"
                @class([
                    'border-b border-zinc-100 align-top last:border-b-0',
                    'cursor-pointer' => $line['editable'],
                ])
                @if ($line['editable']) wire:click="editAction({{ $line['id'] }})" @endif
            >
                <td class="px-2 py-2 tabular-nums text-zinc-500" @style(['box-shadow: inset 4px 0 0 '.$homeColor => $line['side'] === 'home'])>{{ $line['clock'] }}</td>
                <td class="px-2 py-2">
                    @if ($line['playerLines'] !== [])
                        <p class="truncate">
                            @foreach ($line['playerLines'] as $playerLine)
                                @if (! $loop->first)
                                    <span class="text-zinc-400"> - </span>
                                @endif
                                <span @class(['text-green-700' => $playerLine['enters'], 'text-red-700' => ! $playerLine['enters']])>{{ $playerLine['name'] }}</span>
                            @endforeach
                        </p>
                    @else
                        <p class="truncate">{{ $line['player'] }}</p>
                    @endif
                </td>
                <td class="px-2 py-2 text-right" @style(['box-shadow: inset -4px 0 0 '.$awayColor => $line['side'] === 'away'])>
                    <p @class(['font-medium', 'text-zinc-400' => $line['missed'], 'text-zinc-900' => ! $line['missed']])>{{ __($line['label']) }}</p>
                    @if ($line['detail'] !== '')
                        <p class="text-xs text-zinc-500">{{ $line['detail'] }}</p>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
