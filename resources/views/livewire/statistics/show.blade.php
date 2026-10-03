<div class="flex h-dvh min-h-0 flex-col bg-zinc-100 text-zinc-900">
    <header class="grid shrink-0 grid-cols-3 items-center border-b border-zinc-200 bg-white px-4 py-3">
        <a href="{{ route('statistics.record', $game) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-medium text-zinc-900">
            <flux:icon name="arrow-left" class="size-4" />
            {{ __('Back') }}
        </a>
        <h1 class="truncate text-center text-sm font-medium sm:text-base">{{ __('Statistics') }}</h1>
        <span></span>
    </header>

    <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6">
        <div class="mx-auto w-1/2 rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-4 py-3 sm:px-6">
                <h2 class="text-base font-semibold">{{ __('Breakdown') }}</h2>
                <p class="text-sm text-zinc-500">{{ __('Stat breakdown by time period') }}</p>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 text-xs font-medium tracking-wide text-zinc-500 uppercase">
                        <th class="py-2 pr-3 pl-4 sm:pl-6"></th>
                        @foreach ($periods as $period)
                            <th class="px-2 py-2 text-center font-medium">{{ __($period['label']) }}</th>
                        @endforeach
                        <th class="bg-zinc-50 px-2 py-2 text-center font-medium">{{ __('T') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('F') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-zinc-100">
                        <td class="py-3 pr-3 pl-4 sm:pl-6">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-zinc-300 bg-zinc-100 text-[10px] font-semibold tracking-wide text-zinc-700">
                                    @if ($homeLogoUrl)
                                        <img src="{{ $homeLogoUrl }}" alt="" class="size-full object-cover" />
                                    @else
                                        {{ $homeInitials }}
                                    @endif
                                </span>
                                <span class="truncate font-medium">{{ $homeName }}</span>
                            </div>
                        </td>
                        @foreach ($periods as $period)
                            <td class="px-2 py-3 text-center tabular-nums">{{ $period['home'] }}</td>
                        @endforeach
                        <td class="bg-zinc-50 px-2 py-3 text-center font-bold tabular-nums">{{ $homeTotal }}</td>
                        <td class="px-2 py-3 text-center tabular-nums">{{ $homePeriodFouls }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-3 pl-4 sm:pl-6">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-zinc-300 bg-zinc-100 text-[10px] font-semibold tracking-wide text-zinc-700">
                                    @if ($awayLogoUrl)
                                        <img src="{{ $awayLogoUrl }}" alt="" class="size-full object-cover" />
                                    @else
                                        {{ $awayInitials }}
                                    @endif
                                </span>
                                <span class="truncate font-medium">{{ $awayName }}</span>
                            </div>
                        </td>
                        @foreach ($periods as $period)
                            <td class="px-2 py-3 text-center tabular-nums">{{ $period['away'] }}</td>
                        @endforeach
                        <td class="bg-zinc-50 px-2 py-3 text-center font-bold tabular-nums">{{ $awayTotal }}</td>
                        <td class="px-2 py-3 text-center tabular-nums">{{ $awayPeriodFouls }}</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="overflow-x-auto rounded-lg border border-zinc-200 bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 text-xs font-medium tracking-wide text-zinc-500 uppercase">
                            <th class="px-3 py-2 text-left font-medium">{{ __('On court') }}</th>
                            <th class="px-2 py-2 text-center font-medium">{{ __('Min') }}</th>
                            <th class="px-2 py-2 text-center font-medium">{{ __('Pts') }}</th>
                            <th class="px-2 py-2 text-center font-medium">{{ __('PF') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courtPlayers as $player)
                            <tr wire:key="court-{{ $player['id'] }}" class="border-b border-zinc-100 last:border-0">
                                <td class="px-3 py-2">
                                    <span class="font-medium tabular-nums">{{ $player['number'] ?? '—' }}</span>
                                    <span class="ml-2">{{ $player['name'] }}</span>
                                </td>
                                <td class="px-2 py-2 text-center tabular-nums">{{ $player['minutes'] }}</td>
                                <td class="px-2 py-2 text-center font-semibold tabular-nums">{{ $player['points'] }}</td>
                                <td class="px-2 py-2 text-center tabular-nums">{{ $player['fouls'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-3 text-center text-zinc-500">—</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="overflow-x-auto rounded-lg border border-zinc-200 bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 text-xs font-medium tracking-wide text-zinc-500 uppercase">
                            <th class="px-3 py-2 text-left font-medium">{{ __('Bench') }}</th>
                            <th class="px-2 py-2 text-center font-medium">{{ __('Min') }}</th>
                            <th class="px-2 py-2 text-center font-medium">{{ __('Pts') }}</th>
                            <th class="px-2 py-2 text-center font-medium">{{ __('PF') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($benchPlayers as $player)
                            <tr wire:key="bench-{{ $player['id'] }}" class="border-b border-zinc-100 last:border-0">
                                <td class="px-3 py-2">
                                    <span class="font-medium tabular-nums">{{ $player['number'] ?? '—' }}</span>
                                    <span class="ml-2">{{ $player['name'] }}</span>
                                </td>
                                <td class="px-2 py-2 text-center tabular-nums">{{ $player['minutes'] }}</td>
                                <td class="px-2 py-2 text-center font-semibold tabular-nums">{{ $player['points'] }}</td>
                                <td class="px-2 py-2 text-center tabular-nums">{{ $player['fouls'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-3 text-center text-zinc-500">—</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-zinc-200 bg-white px-4 py-4 shadow-sm sm:px-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold">{{ __('Matchup') }}</h2>
                    <p class="text-sm text-zinc-500">{{ __('Stat comparison vs opponent') }}</p>
                </div>
                <div class="flex min-w-0 items-center gap-4 text-xs font-medium text-zinc-500">
                    <span class="flex min-w-0 items-center gap-2">
                        <span class="size-3 shrink-0 rounded-full" style="background-color: {{ $homeColor }}"></span>
                        <span class="truncate">{{ $homeName }}</span>
                    </span>
                    <span class="flex min-w-0 items-center gap-2">
                        <span class="size-3 shrink-0 rounded-full" style="background-color: {{ $awayColor }}"></span>
                        <span class="truncate">{{ $awayName }}</span>
                    </span>
                </div>
            </div>
            <div class="space-y-4">
                @foreach ($matchups as $matchup)
                    <div wire:key="matchup-{{ $matchup['label'] }}">
                        <div class="grid items-center gap-3" style="grid-template-columns: 1fr auto 1fr">
                            <p class="min-w-0 text-sm tabular-nums">{{ $matchup['left'] }}</p>
                            <p class="text-center text-xs font-medium tracking-wide text-zinc-500 uppercase">{{ __($matchup['label']) }}</p>
                            <p class="min-w-0 text-right text-sm tabular-nums">{{ $matchup['right'] }}</p>
                        </div>
                        <div class="mt-2 flex h-1.5 overflow-hidden rounded-full bg-zinc-200">
                            <div class="h-full" style="width: {{ $matchup['leftShare'] }}%; background-color: {{ $homeColor }}"></div>
                            <div class="h-full" style="width: {{ $matchup['rightShare'] }}%; background-color: {{ $awayColor }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if (count($shotChartsByPeriod) > 0)
            <div class="mt-4 rounded-lg border border-zinc-200 bg-white px-4 py-4 shadow-sm sm:px-6">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold">{{ __('Shot Charts') }}</h2>
                        <p class="text-sm text-zinc-500">{{ __('Court locations for shot makes and misses') }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-zinc-500">
                        <span class="inline-flex items-center gap-2">
                            <span class="size-3 rounded-full bg-green-600"></span>
                            {{ __('Made') }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <span class="relative size-3">
                                <span class="absolute inset-0 rotate-45 border-t-2 border-red-600"></span>
                                <span class="absolute inset-0 -rotate-45 border-t-2 border-red-600"></span>
                            </span>
                            {{ __('Missed') }}
                        </span>
                    </div>
                </div>
                <div class="mb-4 grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model.live="homeShotFilter">
                        <flux:select.option value="team">{{ $homeName }}</flux:select.option>
                        @foreach ($homeShotChartPlayers as $player)
                            <flux:select.option value="player-{{ $player['id'] }}">
                                #{{ $player['number'] ?? '—' }} {{ $player['name'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="awayShotFilter">
                        <flux:select.option value="team">{{ $awayName }}</flux:select.option>
                        @foreach ($awayShotChartPlayers as $player)
                            <flux:select.option value="player-{{ $player['id'] }}">
                                #{{ $player['number'] ?? '—' }} {{ $player['name'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="space-y-6">
                    @foreach ($shotChartsByPeriod as $periodChart)
                        <div wire:key="shot-chart-period-{{ $periodChart['quarter'] }}-{{ $homeShotFilter }}-{{ $awayShotFilter }}">
                            <h3 class="mb-3 text-center text-sm font-semibold tracking-wide text-zinc-700 uppercase">{{ __($periodChart['label']) }}</h3>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3">
                                    <div class="mx-auto w-[62.5%]">
                                        <x-shot-chart :shots="$periodChart['home']" />
                                    </div>
                                </div>
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3">
                                    <div class="mx-auto w-[62.5%]">
                                        <x-shot-chart :shots="$periodChart['away']" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-4 rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-4 py-3 sm:px-6">
                <h2 class="text-base font-semibold">{{ __('Box score') }}</h2>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 text-xs font-medium tracking-wide text-zinc-500 uppercase">
                        <th class="py-2 pr-3 pl-4 font-medium sm:pl-6">{{ __('Player') }}</th>
                        <th class="px-2 py-2 text-right font-medium">{{ __('Pts') }}</th>
                        <th class="px-2 py-2 text-right font-medium">{{ __('2P') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('%') }}</th>
                        <th class="px-2 py-2 text-right font-medium">{{ __('3P') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('%') }}</th>
                        <th class="px-2 py-2 text-right font-medium">{{ __('FT') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('%') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('DR') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('OR') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('TR') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('AST') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('BLK') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('STL') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('TO') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('PF') }}</th>
                        <th class="px-2 py-2 text-center font-medium">{{ __('Min') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($boxScore['players'] as $player)
                        <tr wire:key="box-{{ $player['id'] }}" class="border-b border-zinc-100">
                            <td class="py-2 pr-3 pl-4 sm:pl-6">
                                <span class="font-medium tabular-nums">{{ $player['number'] ?? '—' }}</span>
                                <span class="ml-2">{{ $player['name'] }}</span>
                            </td>
                            <td class="px-2 py-2 text-right font-semibold tabular-nums">{{ $player['points'] }}</td>
                            <td class="px-2 py-2 text-right tabular-nums">{{ $player['twoMade'] }}-{{ $player['twoAttempts'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['twoAttempts'] > 0 ? round(100 * $player['twoMade'] / $player['twoAttempts']).'%' : '—' }}</td>
                            <td class="px-2 py-2 text-right tabular-nums">{{ $player['threeMade'] }}-{{ $player['threeAttempts'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['threeAttempts'] > 0 ? round(100 * $player['threeMade'] / $player['threeAttempts']).'%' : '—' }}</td>
                            <td class="px-2 py-2 text-right tabular-nums">{{ $player['freeMade'] }}-{{ $player['freeAttempts'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['freeAttempts'] > 0 ? round(100 * $player['freeMade'] / $player['freeAttempts']).'%' : '—' }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['defensiveRebounds'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['offensiveRebounds'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['offensiveRebounds'] + $player['defensiveRebounds'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['assists'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['blocks'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['steals'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['turnovers'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['fouls'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums">{{ $player['minutes'] }}</td>
                        </tr>
                    @endforeach
                    <tr class="border-b border-zinc-200 font-semibold">
                        <td class="py-2 pr-3 pl-4 sm:pl-6">{{ $teamName }}</td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ $boxScore['team']['points'] }}</td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ $boxScore['team']['twoMade'] }}-{{ $boxScore['team']['twoAttempts'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['twoAttempts'] > 0 ? round(100 * $boxScore['team']['twoMade'] / $boxScore['team']['twoAttempts']).'%' : '—' }}</td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ $boxScore['team']['threeMade'] }}-{{ $boxScore['team']['threeAttempts'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['threeAttempts'] > 0 ? round(100 * $boxScore['team']['threeMade'] / $boxScore['team']['threeAttempts']).'%' : '—' }}</td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ $boxScore['team']['freeMade'] }}-{{ $boxScore['team']['freeAttempts'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['freeAttempts'] > 0 ? round(100 * $boxScore['team']['freeMade'] / $boxScore['team']['freeAttempts']).'%' : '—' }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['defensiveRebounds'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['offensiveRebounds'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['offensiveRebounds'] + $boxScore['team']['defensiveRebounds'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['assists'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['blocks'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['steals'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['turnovers'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['team']['fouls'] }}</td>
                        <td class="px-2 py-2 text-center text-zinc-400">—</td>
                    </tr>
                    <tr class="font-semibold">
                        <td class="py-2 pr-3 pl-4 sm:pl-6">{{ $opponentName }}</td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ $boxScore['opponent']['points'] }}</td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ $boxScore['opponent']['twoMade'] }}-{{ $boxScore['opponent']['twoAttempts'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['twoAttempts'] > 0 ? round(100 * $boxScore['opponent']['twoMade'] / $boxScore['opponent']['twoAttempts']).'%' : '—' }}</td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ $boxScore['opponent']['threeMade'] }}-{{ $boxScore['opponent']['threeAttempts'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['threeAttempts'] > 0 ? round(100 * $boxScore['opponent']['threeMade'] / $boxScore['opponent']['threeAttempts']).'%' : '—' }}</td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ $boxScore['opponent']['freeMade'] }}-{{ $boxScore['opponent']['freeAttempts'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['freeAttempts'] > 0 ? round(100 * $boxScore['opponent']['freeMade'] / $boxScore['opponent']['freeAttempts']).'%' : '—' }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['defensiveRebounds'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['offensiveRebounds'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['offensiveRebounds'] + $boxScore['opponent']['defensiveRebounds'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['assists'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['blocks'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['steals'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['turnovers'] }}</td>
                        <td class="px-2 py-2 text-center tabular-nums">{{ $boxScore['opponent']['fouls'] }}</td>
                        <td class="px-2 py-2 text-center text-zinc-400">—</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
