<table class="w-full table-fixed text-left text-sm">
    <thead>
        <tr class="border-b border-zinc-200 text-xs font-medium tracking-wide text-zinc-500 uppercase">
            <th class="w-14 px-2 py-2 font-medium">{{ __('Clock') }}</th>
            <th class="px-2 py-2 font-medium">{{ __('Player') }}</th>
            <th class="px-2 py-2 text-right font-medium">{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        <template x-for="line in $store.collectQueue.lines" :key="line.id">
            <tr
                class="border-b border-zinc-300 bg-zinc-200 align-top last:border-b-0"
                :class="line.editable ? 'cursor-pointer' : ''"
                @click="if (line.editable) $store.collectQueue.editPending(line.id)"
            >
                <td class="px-2 py-2 tabular-nums text-zinc-500" :style="line.side === 'home' ? 'box-shadow: inset 4px 0 0 {{ $homeColor }}' : ''" x-text="line.clock"></td>
                <td class="max-w-0 px-2 py-2">
                    <template x-if="line.playerLines.length > 0">
                        <div class="leading-tight">
                            <template x-for="playerLine in line.playerLines" :key="playerLine.name">
                                <p class="truncate font-medium" :class="playerLine.enters ? 'text-green-700' : 'text-red-700'" x-text="playerLine.name"></p>
                            </template>
                        </div>
                    </template>
                    <p x-show="line.playerLines.length === 0" class="truncate" x-text="line.player"></p>
                </td>
                <td class="px-2 py-2 text-right" :style="line.side === 'away' ? 'box-shadow: inset -4px 0 0 {{ $awayColor }}' : ''">
                    <p class="font-medium" :class="line.missed ? 'text-zinc-400' : 'text-zinc-900'" x-text="line.label"></p>
                </td>
            </tr>
        </template>
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
                <td class="max-w-0 px-2 py-2">
                    @if ($line['playerLines'] !== [])
                        <div class="leading-tight">
                            @foreach ($line['playerLines'] as $playerLine)
                                <p @class(['truncate font-medium', 'text-red-700' => ! $playerLine['enters'], 'text-green-700' => $playerLine['enters']])>{{ $playerLine['name'] }}</p>
                            @endforeach
                        </div>
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
