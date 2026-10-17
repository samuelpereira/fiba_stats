@props([
    'players',
    'teamLine',
    'teamName',
    'opponentLine',
    'opponentName',
])

@php
    $pct = fn (int $made, int $attempts): string => $attempts > 0 ? round(100 * $made / $attempts).'%' : '—';

    $mainStats = fn (array $line): array => [
        ['label' => __('Pts'), 'value' => $line['points'], 'emphasize' => true],
        ['label' => __('Min'), 'value' => $line['minutes'] ?? '—'],
        ['label' => __('DR'), 'value' => $line['defensiveRebounds']],
        ['label' => __('OR'), 'value' => $line['offensiveRebounds']],
        ['label' => __('TR'), 'value' => ($line['offensiveRebounds'] ?? 0) + ($line['defensiveRebounds'] ?? 0)],
        ['label' => __('AST'), 'value' => $line['assists']],
        ['label' => __('STL'), 'value' => $line['steals']],
        ['label' => __('BLK'), 'value' => $line['blocks']],
        ['label' => __('TO'), 'value' => $line['turnovers']],
        ['label' => __('+/-'), 'value' => is_int($line['plusMinus'] ?? null) ? ($line['plusMinus'] > 0 ? '+'.$line['plusMinus'] : $line['plusMinus']) : '—'],
        ['label' => __('PF'), 'value' => $line['fouls']],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'divide-y divide-zinc-100 sm:hidden']) }}>
    @foreach ($players as $player)
        <article wire:key="box-mobile-{{ $player['id'] }}" class="px-4 py-4">
            <h3 class="min-w-0 truncate text-sm font-semibold">
                <span class="tabular-nums text-zinc-500">#{{ $player['number'] ?? '—' }}</span>
                <span class="ml-1.5">{{ $player['name'] }}</span>
            </h3>
            <dl class="mt-3 grid grid-cols-5 gap-x-1 gap-y-2 text-center">
                @foreach ($mainStats($player) as $stat)
                    <div>
                        <dt class="text-[10px] font-medium tracking-wide text-zinc-500 uppercase">{{ $stat['label'] }}</dt>
                        <dd @class([
                            'mt-0.5 tabular-nums text-sm font-medium text-zinc-900',
                            'font-bold' => $stat['emphasize'] ?? false,
                        ])>{{ $stat['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
            <dl class="mt-3 grid grid-cols-3 gap-2 rounded-lg bg-zinc-50 px-2 py-2 text-center text-xs">
                <div>
                    <dt class="text-[10px] font-medium tracking-wide text-zinc-500 uppercase">{{ __('2P') }}</dt>
                    <dd class="whitespace-nowrap tabular-nums text-xs">{{ $player['twoMade'] }}-{{ $player['twoAttempts'] }} <span class="text-zinc-500">({{ $pct($player['twoMade'], $player['twoAttempts']) }})</span></dd>
                </div>
                <div>
                    <dt class="text-[10px] font-medium tracking-wide text-zinc-500 uppercase">{{ __('3P') }}</dt>
                    <dd class="whitespace-nowrap tabular-nums text-xs">{{ $player['threeMade'] }}-{{ $player['threeAttempts'] }} <span class="text-zinc-500">({{ $pct($player['threeMade'], $player['threeAttempts']) }})</span></dd>
                </div>
                <div>
                    <dt class="text-[10px] font-medium tracking-wide text-zinc-500 uppercase">{{ __('FT') }}</dt>
                    <dd class="whitespace-nowrap tabular-nums text-xs">{{ $player['freeMade'] }}-{{ $player['freeAttempts'] }} <span class="text-zinc-500">({{ $pct($player['freeMade'], $player['freeAttempts']) }})</span></dd>
                </div>
            </dl>
        </article>
    @endforeach

    @foreach ([['line' => $teamLine, 'name' => $teamName], ['line' => $opponentLine, 'name' => $opponentName]] as $summary)
        @php($line = $summary['line'])
        <article class="bg-zinc-50 px-4 py-4">
            <h3 class="min-w-0 truncate text-sm font-semibold">{{ $summary['name'] }}</h3>
            <dl class="mt-3 grid grid-cols-5 gap-x-1 gap-y-2 text-center">
                @foreach ($mainStats([
                    'points' => $line['points'],
                    'minutes' => '—',
                    'defensiveRebounds' => $line['defensiveRebounds'],
                    'offensiveRebounds' => $line['offensiveRebounds'],
                    'assists' => $line['assists'],
                    'steals' => $line['steals'],
                    'blocks' => $line['blocks'],
                    'turnovers' => $line['turnovers'],
                    'fouls' => $line['fouls'],
                ]) as $stat)
                    <div>
                        <dt class="text-[10px] font-medium tracking-wide text-zinc-500 uppercase">{{ $stat['label'] }}</dt>
                        <dd @class([
                            'mt-0.5 tabular-nums text-sm font-medium text-zinc-900',
                            'font-bold' => $stat['emphasize'] ?? false,
                        ])>{{ $stat['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
            <dl class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
                <div>
                    <dt class="font-medium text-zinc-500 uppercase">{{ __('2P') }}</dt>
                    <dd class="whitespace-nowrap tabular-nums">{{ $line['twoMade'] }}-{{ $line['twoAttempts'] }} ({{ $pct($line['twoMade'], $line['twoAttempts']) }})</dd>
                </div>
                <div>
                    <dt class="font-medium text-zinc-500 uppercase">{{ __('3P') }}</dt>
                    <dd class="whitespace-nowrap tabular-nums">{{ $line['threeMade'] }}-{{ $line['threeAttempts'] }} ({{ $pct($line['threeMade'], $line['threeAttempts']) }})</dd>
                </div>
                <div>
                    <dt class="font-medium text-zinc-500 uppercase">{{ __('FT') }}</dt>
                    <dd class="whitespace-nowrap tabular-nums">{{ $line['freeMade'] }}-{{ $line['freeAttempts'] }} ({{ $pct($line['freeMade'], $line['freeAttempts']) }})</dd>
                </div>
            </dl>
        </article>
    @endforeach
</div>
