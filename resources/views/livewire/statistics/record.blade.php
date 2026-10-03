@php
    $pendingStat = $pendingType ? \App\Enums\StatType::from($pendingType) : null;
@endphp

<div data-statistics-record>
@if (! $this->lineupReady)
    <div wire:key="lineup-setup" class="flex h-dvh min-h-0 flex-col bg-zinc-100 text-zinc-900">
        <header class="px-4 py-3 text-center">
            @if ($editingSquad || ! $this->squadReady)
                <h1 class="text-sm font-medium sm:text-base">{{ __('Players in this game') }}</h1>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Choose at least 5 players.') }}</p>
            @else
                <h1 class="text-sm font-medium sm:text-base">{{ __('Starting five') }}</h1>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Choose the 5 players who start.') }}</p>
            @endif
        </header>
        @error('squad')
            <p class="px-4 text-center text-xs text-red-700">{{ $message }}</p>
        @enderror
        @error('starters')
            <p class="px-4 text-center text-xs text-red-700">{{ $message }}</p>
        @enderror
        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 py-3 sm:px-6">
            <div class="flex flex-col gap-2">
                @foreach (($editingSquad || ! $this->squadReady) ? $this->players : $this->attachedPlayers as $player)
                    <button
                        type="button"
                        wire:key="setup-player-{{ $player->id }}"
                        wire:click="{{ ($editingSquad || ! $this->squadReady) ? 'toggleSquadPlayer' : 'toggleStarter' }}({{ $player->id }})"
                        @class([
                            'flex items-center gap-3 rounded-lg border px-3 py-2 text-left',
                            'border-zinc-900 bg-amber-100' => in_array($player->id, ($editingSquad || ! $this->squadReady) ? $squadIds : $starterIds, true),
                            'border-zinc-300 bg-white hover:border-zinc-900' => ! in_array($player->id, ($editingSquad || ! $this->squadReady) ? $squadIds : $starterIds, true),
                        ])
                    >
                        <span class="pointer-events-none flex size-10 shrink-0 items-center justify-center rounded-md border border-zinc-300 text-sm font-semibold">{{ $player->number ?? '—' }}</span>
                        <span class="min-w-0 flex-1 truncate text-sm">{{ $player->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>
        <div class="flex items-center justify-between gap-3 border-t border-zinc-200 px-3 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            @if (! $editingSquad && $this->squadReady && ! $this->gameStarted)
                <button type="button" wire:click="changeLineup" class="min-h-11 px-2 text-sm text-zinc-900">{{ __('Back') }}</button>
            @else
                <span></span>
            @endif
            @if ($editingSquad || ! $this->squadReady)
                <button type="button" wire:click="saveSquad" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">{{ __('Continue') }}</button>
            @else
                <button type="button" wire:click="saveStarters" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">{{ __('Confirm lineup') }}</button>
            @endif
        </div>
    </div>
@elseif ($configuringGame)
    <div wire:key="game-settings" class="flex h-dvh min-h-0 flex-col bg-zinc-100 text-zinc-900">
        <header class="grid shrink-0 grid-cols-3 items-center px-4 py-3">
            <span></span>
            <h1 class="pointer-events-none min-w-0 truncate text-center text-sm font-medium sm:text-base">{{ __('Game settings') }}</h1>
            <button type="button" wire:click="$set('configuringGame', false)" class="relative z-10 justify-self-end rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-800">{{ __('Cancel') }}</button>
        </header>
        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 py-3 sm:px-6">
            @php
                $homeSideName = $this->game->home_away === \App\Enums\HomeAway::Home ? $teamName : $opponentName;
                $awaySideName = $this->game->home_away === \App\Enums\HomeAway::Home ? $opponentName : $teamName;
            @endphp
            <div class="grid grid-cols-2 items-start gap-6">
                <div class="mx-auto flex flex-col gap-3" style="width: 80%">
                    <label>
                        <span class="text-sm text-zinc-500">{{ __('Period (minutes)') }}</span>
                        <input wire:model="quarterMinutes" type="number" min="1" max="99" inputmode="numeric" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-center text-lg tabular-nums" />
                        @error('quarterMinutes')
                            <span class="mt-1 block text-xs text-red-700">{{ $message }}</span>
                        @enderror
                    </label>
                    <label>
                        <span class="text-sm text-zinc-500">{{ __('Overtime (minutes)') }}</span>
                        <input wire:model="overtimeMinutes" type="number" min="1" max="99" inputmode="numeric" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-center text-lg tabular-nums" />
                        @error('overtimeMinutes')
                            <span class="mt-1 block text-xs text-red-700">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="flex items-start gap-3 rounded-lg border border-zinc-300 bg-white px-3 py-3">
                        <input wire:model="markShotLocation" type="checkbox" class="mt-1 size-4 rounded border-zinc-300" />
                        <span>
                            <span class="block text-sm font-medium">{{ __('Mark shot location on court') }}</span>
                            <span class="mt-0.5 block text-xs text-zinc-500">{{ __('After 2P or 3P, tap the half court where the shot was taken.') }}</span>
                        </span>
                    </label>
                </div>
                <div class="mx-auto flex flex-col items-center gap-4" style="width: 80%">
                    <div class="w-full">
                        <p class="truncate text-center text-sm text-zinc-500">{{ $homeSideName }} · {{ __('At home') }}</p>
                        <div class="mt-2 grid w-full gap-2" style="grid-template-columns: repeat(8, minmax(0, 1fr));">
                            @foreach (\App\Enums\TeamColor::cases() as $color)
                                <button
                                    type="button"
                                    wire:key="home-color-{{ $color->value }}"
                                    wire:click="$set('homeColor', '{{ $color->value }}')"
                                    aria-label="{{ __($color->label()) }}"
                                    aria-pressed="{{ $homeColor === $color->value ? 'true' : 'false' }}"
                                    class="rounded-full border border-zinc-300"
                                    @style([
                                        'aspect-ratio: 1',
                                        'width: 100%',
                                        'max-width: 2rem',
                                        'justify-self: center',
                                        'background-color: '.$color->hex(),
                                        'box-shadow: 0 0 0 2px #fff, 0 0 0 4px #18181b' => $homeColor === $color->value,
                                    ])
                                ></button>
                            @endforeach
                        </div>
                        @error('homeColor')
                            <span class="mt-1 block text-center text-xs text-red-700">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="w-full">
                        <p class="truncate text-center text-sm text-zinc-500">{{ $awaySideName }} · {{ __('Away') }}</p>
                        <div class="mt-2 grid w-full gap-2" style="grid-template-columns: repeat(8, minmax(0, 1fr));">
                            @foreach (\App\Enums\TeamColor::cases() as $color)
                                <button
                                    type="button"
                                    wire:key="away-color-{{ $color->value }}"
                                    wire:click="$set('awayColor', '{{ $color->value }}')"
                                    aria-label="{{ __($color->label()) }}"
                                    aria-pressed="{{ $awayColor === $color->value ? 'true' : 'false' }}"
                                    class="rounded-full border border-zinc-300"
                                    @style([
                                        'aspect-ratio: 1',
                                        'width: 100%',
                                        'max-width: 2rem',
                                        'justify-self: center',
                                        'background-color: '.$color->hex(),
                                        'box-shadow: 0 0 0 2px #fff, 0 0 0 4px #18181b' => $awayColor === $color->value,
                                    ])
                                ></button>
                            @endforeach
                        </div>
                        @error('awayColor')
                            <span class="mt-1 block text-center text-xs text-red-700">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            @error('settingsSquad')
                <p class="px-4 pt-3 text-center text-xs text-red-700">{{ $message }}</p>
            @enderror
            @php
                $courtIds = $this->lineup['court'];
                $protectedIds = $this->protectedSquadIds;
                $settingsOnCourt = $this->players->filter(fn ($player) => in_array($player->id, $courtIds, true));
                $settingsOthers = $this->players->reject(fn ($player) => in_array($player->id, $courtIds, true));
            @endphp
            <div class="mt-4 flex flex-col gap-2 border-t border-zinc-200 pt-4">
                @foreach ($settingsOnCourt as $player)
                    <div wire:key="settings-court-{{ $player->id }}" class="flex items-center gap-3 rounded-lg border border-zinc-900 bg-amber-100 px-3 py-2">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-md border border-zinc-300 text-sm font-semibold">{{ $player->number ?? '—' }}</span>
                        <span class="min-w-0 flex-1 truncate text-sm">{{ $player->name }}</span>
                        <span class="shrink-0 text-[11px] font-medium text-zinc-500">{{ __('On court') }}</span>
                    </div>
                @endforeach
            </div>
            @if ($settingsOthers->isNotEmpty())
                <div class="mt-4 flex flex-col gap-2 border-t border-zinc-200 pt-3">
                    @foreach ($settingsOthers as $player)
                        @if (in_array($player->id, $protectedIds, true))
                            <div wire:key="settings-kept-{{ $player->id }}" class="flex items-center gap-3 rounded-lg border border-zinc-900 bg-amber-100 px-3 py-2">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-md border border-zinc-300 text-sm font-semibold">{{ $player->number ?? '—' }}</span>
                                <span class="min-w-0 flex-1 truncate text-sm">{{ $player->name }}</span>
                            </div>
                        @else
                            <button
                                type="button"
                                wire:key="settings-player-{{ $player->id }}"
                                wire:click="toggleSettingsPlayer({{ $player->id }})"
                                @class([
                                    'flex items-center gap-3 rounded-lg border px-3 py-2 text-left',
                                    'border-zinc-900 bg-amber-100' => in_array($player->id, $settingsSquadIds, true),
                                    'border-zinc-300 bg-white hover:border-zinc-900' => ! in_array($player->id, $settingsSquadIds, true),
                                ])
                            >
                                <span class="pointer-events-none flex size-10 shrink-0 items-center justify-center rounded-md border border-zinc-300 text-sm font-semibold">{{ $player->number ?? '—' }}</span>
                                <span class="min-w-0 flex-1 truncate text-sm">{{ $player->name }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-3 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <button type="button" wire:click="saveGameSettings" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">{{ __('Save') }}</button>
        </div>
    </div>
@elseif ($substituting)
    <div wire:key="substitution" class="flex h-dvh min-h-0 flex-col bg-zinc-100 text-zinc-900">
        <header class="grid shrink-0 grid-cols-3 items-center px-4 py-3">
            <span></span>
            <h1 class="pointer-events-none min-w-0 truncate text-center text-sm font-medium sm:text-base">{{ __('Substitution') }}</h1>
            <button type="button" wire:click="cancel" class="relative z-10 justify-self-end rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-800">{{ __('Cancel') }}</button>
        </header>
        <p class="px-4 text-center text-sm text-zinc-500">{{ __('Mark 5 players.') }}</p>
        @error('court')
            <p class="px-4 pt-1 text-center text-xs text-red-700">{{ $message }}</p>
        @enderror
        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 py-3 sm:px-6">
            @foreach (['court' => $this->courtPlayers, 'bench' => $this->benchPlayers] as $group => $players)
                @continue($players->isEmpty())
                <div @class(['mt-4 border-t border-zinc-200 pt-3' => $group === 'bench'])>
                    <div class="grid grid-cols-2 content-start gap-3 sm:grid-cols-3">
                        @foreach ($players as $player)
                            <button
                                type="button"
                                wire:key="sub-{{ $group }}-{{ $player->id }}"
                                wire:click="toggleCourtSelection({{ $player->id }})"
                                @class([
                                    'flex items-center gap-3 rounded-xl border px-3 py-4 text-left',
                                    'border-zinc-900 bg-amber-100' => in_array($player->id, $courtSelection, true),
                                    'border-zinc-300 bg-white hover:border-zinc-900' => ! in_array($player->id, $courtSelection, true),
                                ])
                            >
                                <span class="pointer-events-none flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-zinc-900">
                                    @if ($player->photo_url)
                                        <img src="{{ $player->photo_url }}" alt="" class="size-full object-cover" />
                                    @else
                                        <flux:icon name="user" class="size-7" />
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    @if ($player->number !== null)
                                        <span class="block text-lg font-semibold leading-tight">#{{ $player->number }}</span>
                                    @endif
                                    @foreach ($player->nameLines() as $line)
                                        <span class="block truncate text-sm leading-tight">{{ $line }}</span>
                                    @endforeach
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-end border-t border-zinc-200 px-3 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <button type="button" wire:click="confirmSubstitutions" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">{{ __('Confirm') }}</button>
        </div>
    </div>
@else
<div wire:key="stat-pad" class="flex h-dvh min-h-0 flex-col overflow-hidden bg-zinc-100 text-zinc-900 lg:hidden">
    @php($weAreHome = $this->game->home_away === \App\Enums\HomeAway::Home)
    <header class="grid shrink-0 grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-start gap-2 border-b border-zinc-200 bg-white px-3 pt-3 pb-2">
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold">{{ $weAreHome ? $teamName : $opponentName }}</p>
            <div class="mt-1 flex items-center justify-between gap-2">
                <p class="text-3xl font-bold tracking-tight tabular-nums leading-none">{{ $weAreHome ? $this->teamPoints() : $opponentPoints }}</p>
                <div class="shrink-0 text-right leading-tight text-zinc-500" style="font-size: 13px">
                    <p class="flex items-center justify-end gap-3">{{ __('Team Foul') }}<span @class(['font-bold tabular-nums', 'text-green-700' => ($weAreHome ? $this->fouls['team'] : $this->fouls['opponent']) >= 1 && ($weAreHome ? $this->fouls['team'] : $this->fouls['opponent']) <= 3, 'text-orange-600' => ($weAreHome ? $this->fouls['team'] : $this->fouls['opponent']) === 4, 'text-red-700' => ($weAreHome ? $this->fouls['team'] : $this->fouls['opponent']) >= 5, 'text-zinc-900' => ($weAreHome ? $this->fouls['team'] : $this->fouls['opponent']) < 1])>{{ $weAreHome ? $this->fouls['team'] : $this->fouls['opponent'] }}</span></p>
                    <p class="flex items-center justify-end gap-3">{{ __('Timeout') }}<span @class(['font-bold tabular-nums', 'text-red-700' => ($weAreHome ? $this->timeouts['team'] : $this->timeouts['opponent']) >= $this->timeouts['allowance'], 'text-zinc-900' => ($weAreHome ? $this->timeouts['team'] : $this->timeouts['opponent']) < $this->timeouts['allowance']])>{{ $weAreHome ? $this->timeouts['team'] : $this->timeouts['opponent'] }}/{{ $this->timeouts['allowance'] }}</span></p>
                </div>
            </div>
        </div>
        <div class="flex flex-col items-center px-1 text-center" x-init="$nextTick(() => Alpine.store('gameClock')?.bind($wire))">
            <button type="button" @click="$store.gameClock?.press()" class="text-zinc-900" :aria-label="$wire.awaitingQuarterStart ? @js($startPeriodAction) : ($store.gameClock?.running ? @js(__('Pause')) : (($store.gameClock?.remaining === 0 && ! $wire.quarterEnded) ? @js($endPeriodAction) : @js(__('Start'))))">
                <flux:icon name="play" variant="solid" class="size-5" x-show="$wire.awaitingQuarterStart || (! $store.gameClock.running && ($store.gameClock.remaining > 0 || $wire.quarterEnded))" />
                <flux:icon name="pause" variant="solid" class="size-5" x-cloak x-show="! $wire.awaitingQuarterStart && $store.gameClock.running" />
                <flux:icon name="forward" variant="solid" class="size-5" x-cloak x-show="! $wire.awaitingQuarterStart && ! $store.gameClock.running && $store.gameClock.remaining === 0 && ! $wire.quarterEnded" />
            </button>
            @if ($editingClock)
                <label class="mt-1">
                    <span class="sr-only">{{ __('Clock') }}</span>
                    <input wire:model="clock" x-init="if ($el.offsetParent !== null) $el.focus()" @change="$store.gameClock.setFrom($event.target.value); $wire.$set('editingClock', false)" type="text" inputmode="numeric" maxlength="5" class="w-24 rounded-md border border-zinc-900 bg-white px-1 py-1 text-center text-2xl font-semibold tabular-nums" />
                </label>
            @else
                <button type="button" @click="if (! $store.gameClock.running && ! $wire.awaitingQuarterStart) $wire.$set('editingClock', true)" class="text-2xl font-bold tracking-tight tabular-nums leading-none" aria-label="{{ __('Edit clock') }}">
                    <span x-text="$wire.awaitingQuarterStart ? '--:--' : $store.gameClock.formatted">{{ $awaitingQuarterStart ? '--:--' : $clock }}</span>
                </button>
            @endif
            <p class="text-xs text-zinc-500">{{ $awaitingQuarterStart ? $awaitingPeriodLabel : $periodName }}</p>
            @error('clock')
                <p class="pt-1 text-[11px] text-zinc-500">{{ $message }}</p>
            @enderror
            <button type="button" wire:click="openGameSettings" class="mt-1 text-zinc-500" aria-label="{{ __('Game settings') }}">
                <flux:icon name="cog-6-tooth" class="size-3.5" />
            </button>
        </div>
        <div class="min-w-0 text-right">
            <p class="truncate text-sm font-semibold">{{ $weAreHome ? $opponentName : $teamName }}</p>
            <div class="mt-1 flex items-center justify-between gap-2">
                <div class="shrink-0 text-left leading-tight text-zinc-500" style="font-size: 13px">
                    <p class="flex items-center justify-start gap-3"><span @class(['font-bold tabular-nums', 'text-green-700' => ($weAreHome ? $this->fouls['opponent'] : $this->fouls['team']) >= 1 && ($weAreHome ? $this->fouls['opponent'] : $this->fouls['team']) <= 3, 'text-orange-600' => ($weAreHome ? $this->fouls['opponent'] : $this->fouls['team']) === 4, 'text-red-700' => ($weAreHome ? $this->fouls['opponent'] : $this->fouls['team']) >= 5, 'text-zinc-900' => ($weAreHome ? $this->fouls['opponent'] : $this->fouls['team']) < 1])>{{ $weAreHome ? $this->fouls['opponent'] : $this->fouls['team'] }}</span><span>{{ __('Team Foul') }}</span></p>
                    <p class="flex items-center justify-start gap-3"><span @class(['font-bold tabular-nums', 'text-red-700' => ($weAreHome ? $this->timeouts['opponent'] : $this->timeouts['team']) >= $this->timeouts['allowance'], 'text-zinc-900' => ($weAreHome ? $this->timeouts['opponent'] : $this->timeouts['team']) < $this->timeouts['allowance']])>{{ $weAreHome ? $this->timeouts['opponent'] : $this->timeouts['team'] }}/{{ $this->timeouts['allowance'] }}</span><span>{{ __('Timeout') }}</span></p>
                </div>
                <p class="text-3xl font-bold tracking-tight tabular-nums leading-none">{{ $weAreHome ? $opponentPoints : $this->teamPoints() }}</p>
            </div>
        </div>
    </header>

    <div class="flex shrink-0 items-stretch border-b border-zinc-200 bg-white">
        <a href="{{ route('statistics.show', $this->game) }}" wire:navigate class="flex items-center px-3 text-zinc-900" aria-label="{{ __('Statistics') }}">
            <flux:icon name="bars-3" class="size-5" />
        </a>
        <div class="grid min-w-0 flex-1 grid-cols-3">
            <button type="button" @click="$store.collectPane.name = 'scoring'" class="border-b-2 py-3 text-sm font-medium" :class="$store.collectPane.name === 'scoring' ? 'border-zinc-900 text-zinc-900' : 'border-transparent text-zinc-500'">{{ __('Scoring') }}</button>
            <button type="button" @click="$store.collectPane.name = 'roster'" class="border-b-2 py-3 text-sm font-medium" :class="$store.collectPane.name === 'roster' ? 'border-zinc-900 text-zinc-900' : 'border-transparent text-zinc-500'">{{ __('Roster') }}</button>
            <button type="button" @click="$store.collectPane.name = 'record'" class="border-b-2 py-3 text-sm font-medium" :class="$store.collectPane.name === 'record' ? 'border-zinc-900 text-zinc-900' : 'border-transparent text-zinc-500'">{{ __('Game log') }}</button>
        </div>
        <button type="button" wire:click="undo" @disabled(! $this->canUndo) @class(['flex items-center px-3', 'text-zinc-900' => $this->canUndo, 'text-zinc-500' => ! $this->canUndo]) aria-label="{{ __('Undo') }}">
            <flux:icon name="arrow-uturn-left" class="size-5" />
        </button>
    </div>

    <div x-show="! $store.collectPane || $store.collectPane.name === 'scoring'" class="grid min-h-0 flex-1 grid-cols-2 grid-rows-7 gap-2 p-2 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
        @foreach ([
            [\App\Enums\StatType::ThreePointMade, '3P', 'made'],
            [\App\Enums\StatType::ThreePointMissed, '3P', 'missed'],
            [\App\Enums\StatType::TwoPointMade, '2P', 'made'],
            [\App\Enums\StatType::TwoPointMissed, '2P', 'missed'],
            [\App\Enums\StatType::FreeThrowMade, '1P', 'made'],
            [\App\Enums\StatType::FreeThrowMissed, '1P', 'missed'],
            [\App\Enums\StatType::OffensiveRebound, \App\Enums\StatType::OffensiveRebound->buttonLines(), 'plain'],
            [\App\Enums\StatType::DefensiveRebound, \App\Enums\StatType::DefensiveRebound->buttonLines(), 'plain'],
            [\App\Enums\StatType::Assist, 'Assist', 'plain'],
            [\App\Enums\StatType::Block, \App\Enums\StatType::Block->buttonLines(), 'plain'],
            [\App\Enums\StatType::Steal, \App\Enums\StatType::Steal->buttonLines(), 'plain'],
            [\App\Enums\StatType::Turnover, \App\Enums\StatType::Turnover->buttonLines(), 'plain'],
            [\App\Enums\StatType::Foul, 'Foul', 'plain'],
            [\App\Enums\StatType::Substitution, $substitutionLabel, 'plain'],
        ] as [$stat, $statLabel, $tone])
            @php($statEnabled = $stat === \App\Enums\StatType::Substitution ? (! $this->gameStarted || $this->periodIsOpen) : $this->periodIsOpen)
            <button
                type="button"
                wire:click="select('{{ $stat->value }}')"
                @disabled(! $statEnabled)
                @if ($stat === \App\Enums\StatType::Foul || $stat === \App\Enums\StatType::Substitution)
                    @click="$store.gameClock.pause()"
                @endif
                @class([
                    'flex flex-col items-center justify-center gap-1 rounded-lg border bg-white text-sm font-semibold tracking-wide leading-none shadow-sm',
                    'border-green-700 text-green-700' => $tone === 'made',
                    'border-red-700 text-red-700' => $tone === 'missed',
                    'border-zinc-300 text-zinc-900' => $tone === 'plain',
                    'ring-2 ring-zinc-900 ring-inset' => $pendingType === $stat->value,
                    'opacity-40' => ! $statEnabled,
                ])
            >@foreach ((array) $statLabel as $line)<span>{{ __($line) }}</span>@endforeach</button>
        @endforeach
    </div>

    <div x-cloak x-show="$store.collectPane.name === 'roster'" class="min-h-0 flex-1 space-y-2 overflow-y-auto overscroll-contain px-3 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
        @foreach ($this->squad as $player)
            @php($played = $this->lineup['seconds'][$player->id] ?? 0)
            @php($onCourt = in_array($player->id, $this->lineup['court'], true))
            <div wire:key="mobile-player-{{ $player->id }}" @class(['flex items-center gap-3 rounded-lg border bg-white px-3 py-2', 'border-zinc-900' => $onCourt, 'border-zinc-200' => ! $onCourt])>
                <span class="flex size-10 shrink-0 items-center justify-center rounded-md border border-zinc-300 text-sm font-semibold">{{ $player->number ?? '—' }}</span>
                <span class="min-w-0 flex-1 truncate text-sm">{{ $player->name }}</span>
                @if ($onCourt)
                    <span class="shrink-0 text-[11px] font-medium text-zinc-500">{{ __('On court') }}</span>
                @endif
                <span class="shrink-0 text-xs font-medium tabular-nums text-zinc-500">{{ intdiv($played, 60) }}:{{ sprintf('%02d', $played % 60) }}</span>
                @php($personalFouls = $this->fouls['players'][$player->id] ?? 0)
                <span @class(['shrink-0 text-sm font-medium tabular-nums', 'text-green-700' => $personalFouls >= 1 && $personalFouls <= 3, 'text-orange-600' => $personalFouls === 4, 'text-red-700' => $personalFouls >= 5])>{{ $personalFouls }} {{ __('FP') }}</span>
            </div>
        @endforeach
    </div>

    <div x-cloak x-show="$store.collectPane.name === 'record'" class="min-h-0 flex-1 overflow-y-auto px-3 py-4 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
        @include('livewire.statistics.log', ['lines' => $this->lines, 'keyPrefix' => 'mobile-line', 'homeColor' => $this->game->home_color->hex(), 'awayColor' => $this->game->away_color->hex()])
    </div>
</div>

<div wire:key="stat-pad-desktop" class="hidden h-dvh overflow-hidden bg-zinc-100 text-zinc-900 lg:grid lg:grid-cols-[minmax(0,0.6fr)_minmax(0,1.2fr)_minmax(0,1.1fr)]">
    <section class="flex min-h-0 flex-col border-r border-zinc-200 p-2">
        <div class="grid h-4/5 min-h-0 grid-cols-2 grid-rows-7 gap-2">
        @foreach ([
            [\App\Enums\StatType::TwoPointMade, '2P', 'made'],
            [\App\Enums\StatType::TwoPointMissed, '2P', 'missed'],
            [\App\Enums\StatType::ThreePointMade, '3P', 'made'],
            [\App\Enums\StatType::ThreePointMissed, '3P', 'missed'],
            [\App\Enums\StatType::FreeThrowMade, '1P', 'made'],
            [\App\Enums\StatType::FreeThrowMissed, '1P', 'missed'],
            [\App\Enums\StatType::DefensiveRebound, \App\Enums\StatType::DefensiveRebound->buttonLines(), 'plain'],
            [\App\Enums\StatType::OffensiveRebound, \App\Enums\StatType::OffensiveRebound->buttonLines(), 'plain'],
            [\App\Enums\StatType::Assist, 'Assist', 'plain'],
            [\App\Enums\StatType::Block, \App\Enums\StatType::Block->buttonLines(), 'plain'],
            [\App\Enums\StatType::Steal, \App\Enums\StatType::Steal->buttonLines(), 'plain'],
            [\App\Enums\StatType::Turnover, \App\Enums\StatType::Turnover->buttonLines(), 'plain'],
            [\App\Enums\StatType::Foul, 'Foul', 'plain'],
            [\App\Enums\StatType::Substitution, $substitutionLabel, 'plain'],
        ] as [$stat, $statLabel, $tone])
            @php($statEnabled = $stat === \App\Enums\StatType::Substitution ? (! $this->gameStarted || $this->periodIsOpen) : $this->periodIsOpen)
            <button
                type="button"
                wire:click="select('{{ $stat->value }}')"
                @disabled(! $statEnabled)
                @if ($stat === \App\Enums\StatType::Foul || $stat === \App\Enums\StatType::Substitution)
                    @click="$store.gameClock.pause()"
                @endif
                @class([
                    'flex flex-col items-center justify-center gap-1 rounded-lg border bg-white text-sm font-semibold tracking-wide leading-none shadow-sm',
                    'border-green-700 text-green-700' => $tone === 'made',
                    'border-red-700 text-red-700' => $tone === 'missed',
                    'border-zinc-300 text-zinc-900' => $tone === 'plain',
                    'ring-2 ring-zinc-900 ring-inset' => $pendingType === $stat->value,
                    'opacity-40' => ! $statEnabled,
                ])
            >@foreach ((array) $statLabel as $line)<span>{{ __($line) }}</span>@endforeach</button>
        @endforeach
        </div>
        <div class="flex min-h-0 flex-1 items-end justify-center gap-3">
            <button type="button" wire:click="attemptExit" class="flex size-12 items-center justify-center rounded-xl bg-white text-zinc-700 shadow-sm" aria-label="{{ __('Exit') }}">
                <flux:icon name="power" class="size-5" />
            </button>
            <a href="{{ route('statistics.show', $this->game) }}" wire:navigate class="flex size-12 items-center justify-center rounded-xl bg-white text-zinc-700 shadow-sm" aria-label="{{ __('Statistics') }}">
                <flux:icon name="chart-bar" class="size-5" />
            </a>
            <button type="button" wire:click="openGameSettings" class="flex size-12 items-center justify-center rounded-xl bg-white text-zinc-700 shadow-sm" aria-label="{{ __('Game settings') }}">
                <flux:icon name="cog-6-tooth" class="size-5" />
            </button>
            <button type="button" wire:click="undo" @disabled(! $this->canUndo) @class(['flex size-12 items-center justify-center rounded-xl bg-white shadow-sm', 'text-zinc-700' => $this->canUndo, 'text-zinc-500' => ! $this->canUndo]) aria-label="{{ __('Undo') }}">
                <flux:icon name="arrow-uturn-left" class="size-5" />
            </button>
        </div>
    </section>

    <section class="flex min-h-0 flex-col border-r border-zinc-200">
        @php($weAreHome = $this->game->home_away === \App\Enums\HomeAway::Home)
        <div
            class="shrink-0 bg-zinc-900 px-4 py-3 text-white"
            :class="{ '!bg-green-700': $store.gameClock?.running }"
            x-init="$nextTick(() => Alpine.store('gameClock')?.bind($wire))"
        >
            <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-3">
                <div class="flex items-end">
                    <button type="button" @click="$store.gameClock.pause()" wire:click="chooseTimeout" @disabled(! $this->periodIsOpen) @class(['flex flex-col items-center gap-1 text-white', 'opacity-40' => ! $this->periodIsOpen])>
                        <flux:icon name="clock" class="size-5" />
                        <span class="text-[11px] leading-none">{{ __('Timeout') }}</span>
                    </button>
                </div>

                <div class="flex items-center justify-center gap-3">
                    <button type="button" @click="$store.gameClock.adjust(-5)" class="flex flex-col items-center gap-1 text-white">
                        <flux:icon name="minus" class="size-5" />
                        <span class="text-[11px] leading-none">{{ __('-5 sec') }}</span>
                    </button>
                    <div class="text-center">
                        @if ($editingClock)
                            <label>
                                <span class="sr-only">{{ __('Clock') }}</span>
                                <input wire:model="clock" x-init="if ($el.offsetParent !== null) $el.focus()" @change="$store.gameClock.setFrom($event.target.value); $wire.$set('editingClock', false)" type="text" inputmode="numeric" maxlength="5" class="w-24 rounded-md border border-zinc-900 bg-white px-2 py-1 text-center text-4xl font-semibold tabular-nums" />
                            </label>
                        @else
                            <button type="button" @click="if (! $store.gameClock.running && ! $wire.awaitingQuarterStart) $wire.$set('editingClock', true)" class="text-4xl font-bold tracking-tight tabular-nums" aria-label="{{ __('Edit clock') }}">
                                <span x-text="$wire.awaitingQuarterStart ? '--:--' : $store.gameClock.formatted">{{ $awaitingQuarterStart ? '--:--' : $clock }}</span>
                            </button>
                        @endif
                        <p class="text-sm text-zinc-300">{{ $awaitingQuarterStart ? $awaitingPeriodLabel : $periodName }}</p>
                    </div>
                    <button type="button" @click="$store.gameClock.adjust(5)" class="flex flex-col items-center gap-1 text-white">
                        <flux:icon name="plus" class="size-5" />
                        <span class="text-[11px] leading-none">{{ __('+5 sec') }}</span>
                    </button>
                </div>

                <div class="flex justify-end">
                    <button
                        type="button"
                        @click="$store.gameClock.press()"
                        class="flex h-14 min-w-14 flex-col items-center justify-center gap-0.5 rounded-xl px-2 text-[11px] font-medium leading-none"
                        :class="$store.gameClock.running ? 'bg-red-700 text-white' : ((! $wire.awaitingQuarterStart && ! $store.gameClock.running && $store.gameClock.remaining === 0 && ! $wire.quarterEnded) ? 'bg-white text-zinc-900' : 'bg-green-700 text-white')"
                    >
                        <span x-show="$wire.awaitingQuarterStart" class="flex flex-col items-center gap-0.5">
                            <flux:icon name="play" variant="solid" class="size-5" />
                            {{ $startPeriodAction }}
                        </span>
                        <span x-show="! $wire.awaitingQuarterStart && ! $store.gameClock.running && ($store.gameClock.remaining > 0 || $wire.quarterEnded)" class="flex flex-col items-center gap-0.5">
                            <flux:icon name="play" variant="solid" class="size-5" />
                            {{ __('Start') }}
                        </span>
                        <span x-cloak x-show="! $wire.awaitingQuarterStart && $store.gameClock.running" class="flex flex-col items-center gap-0.5">
                            <flux:icon name="pause" variant="solid" class="size-5" />
                            {{ __('Pause') }}
                        </span>
                        <span x-cloak x-show="! $wire.awaitingQuarterStart && ! $store.gameClock.running && $store.gameClock.remaining === 0 && ! $wire.quarterEnded" class="flex flex-col items-center gap-0.5">
                            <flux:icon name="forward" variant="solid" class="size-5" />
                            {{ $endPeriodAction }}
                        </span>
                    </button>
                </div>
            </div>
            @error('clock')
                <p class="pt-1 text-center text-sm text-zinc-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid shrink-0 grid-cols-2 gap-3 border-b border-zinc-200 px-4 py-4">
            <div @class(['min-w-0 rounded-lg border border-zinc-200 bg-white px-4 py-3 shadow-sm', 'order-1' => $weAreHome, 'order-2 text-right' => ! $weAreHome])>
                <p class="truncate text-sm font-semibold">{{ $teamName }}</p>
                <div class="flex items-center justify-between gap-3">
                    <p @class(['text-3xl font-bold tracking-tight tabular-nums leading-none', 'order-1' => $weAreHome, 'order-2' => ! $weAreHome])>{{ $this->teamPoints() }}</p>
                    <div style="font-size: 13px" @class(['shrink-0 leading-tight text-zinc-500', 'order-2 text-right' => $weAreHome, 'order-1 text-left' => ! $weAreHome])>
                        @if ($weAreHome)
                            <p class="flex items-center justify-end gap-3">{{ __('Team Foul') }}<span @class(['font-bold tabular-nums', 'text-green-700' => $this->fouls['team'] >= 1 && $this->fouls['team'] <= 3, 'text-orange-600' => $this->fouls['team'] === 4, 'text-red-700' => $this->fouls['team'] >= 5, 'text-zinc-900' => $this->fouls['team'] < 1])>{{ $this->fouls['team'] }}</span></p>
                            <p class="flex items-center justify-end gap-3">{{ __('Timeout') }}<span @class(['font-bold tabular-nums', 'text-red-700' => $this->timeouts['team'] >= $this->timeouts['allowance'], 'text-zinc-900' => $this->timeouts['team'] < $this->timeouts['allowance']])>{{ $this->timeouts['team'] }}/{{ $this->timeouts['allowance'] }}</span></p>
                        @else
                            <p class="flex items-center justify-start gap-3"><span @class(['font-bold tabular-nums', 'text-green-700' => $this->fouls['team'] >= 1 && $this->fouls['team'] <= 3, 'text-orange-600' => $this->fouls['team'] === 4, 'text-red-700' => $this->fouls['team'] >= 5, 'text-zinc-900' => $this->fouls['team'] < 1])>{{ $this->fouls['team'] }}</span><span>{{ __('Team Foul') }}</span></p>
                            <p class="flex items-center justify-start gap-3"><span @class(['font-bold tabular-nums', 'text-red-700' => $this->timeouts['team'] >= $this->timeouts['allowance'], 'text-zinc-900' => $this->timeouts['team'] < $this->timeouts['allowance']])>{{ $this->timeouts['team'] }}/{{ $this->timeouts['allowance'] }}</span><span>{{ __('Timeout') }}</span></p>
                        @endif
                    </div>
                </div>
            </div>
            <div @class(['min-w-0 rounded-lg border border-zinc-200 bg-white px-4 py-3 shadow-sm', 'order-2 text-right' => $weAreHome, 'order-1' => ! $weAreHome])>
                <p class="truncate text-sm font-semibold">{{ $opponentName }}</p>
                <div class="flex items-center justify-between gap-3">
                    <p @class(['text-3xl font-bold tracking-tight tabular-nums leading-none', 'order-2' => $weAreHome, 'order-1' => ! $weAreHome])>{{ $opponentPoints }}</p>
                    <div style="font-size: 13px" @class(['shrink-0 leading-tight text-zinc-500', 'order-1 text-left' => $weAreHome, 'order-2 text-right' => ! $weAreHome])>
                        @if ($weAreHome)
                            <p class="flex items-center justify-start gap-3"><span @class(['font-bold tabular-nums', 'text-green-700' => $this->fouls['opponent'] >= 1 && $this->fouls['opponent'] <= 3, 'text-orange-600' => $this->fouls['opponent'] === 4, 'text-red-700' => $this->fouls['opponent'] >= 5, 'text-zinc-900' => $this->fouls['opponent'] < 1])>{{ $this->fouls['opponent'] }}</span><span>{{ __('Team Foul') }}</span></p>
                            <p class="flex items-center justify-start gap-3"><span @class(['font-bold tabular-nums', 'text-red-700' => $this->timeouts['opponent'] >= $this->timeouts['allowance'], 'text-zinc-900' => $this->timeouts['opponent'] < $this->timeouts['allowance']])>{{ $this->timeouts['opponent'] }}/{{ $this->timeouts['allowance'] }}</span><span>{{ __('Timeout') }}</span></p>
                        @else
                            <p class="flex items-center justify-end gap-3">{{ __('Team Foul') }}<span @class(['font-bold tabular-nums', 'text-green-700' => $this->fouls['opponent'] >= 1 && $this->fouls['opponent'] <= 3, 'text-orange-600' => $this->fouls['opponent'] === 4, 'text-red-700' => $this->fouls['opponent'] >= 5, 'text-zinc-900' => $this->fouls['opponent'] < 1])>{{ $this->fouls['opponent'] }}</span></p>
                            <p class="flex items-center justify-end gap-3">{{ __('Timeout') }}<span @class(['font-bold tabular-nums', 'text-red-700' => $this->timeouts['opponent'] >= $this->timeouts['allowance'], 'text-zinc-900' => $this->timeouts['opponent'] < $this->timeouts['allowance']])>{{ $this->timeouts['opponent'] }}/{{ $this->timeouts['allowance'] }}</span></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="flex min-h-0 flex-1 flex-col gap-2 overflow-y-auto px-4 py-4">
            @php($seenCourt = false)
            @php($benchStarted = false)
            @foreach ($this->squad as $player)
                @php($played = $this->lineup['seconds'][$player->id] ?? 0)
                @php($onCourt = in_array($player->id, $this->lineup['court'], true))
                @php($firstBench = $seenCourt && ! $onCourt && ! $benchStarted)
                @php($seenCourt = $seenCourt || $onCourt)
                @php($benchStarted = $benchStarted || ! $onCourt)
                <div wire:key="lineup-{{ $player->id }}" @class(['flex items-center gap-3 rounded-lg border bg-white px-3 py-2', 'mt-2' => $firstBench, 'border-zinc-900' => $onCourt, 'border-zinc-200' => ! $onCourt])>
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-md border border-zinc-300 text-sm font-semibold">
                        {{ $player->number ?? '—' }}
                    </span>
                    <span class="min-w-0 flex-1 truncate text-sm">{{ $player->name }}</span>
                    @if ($onCourt)
                        <span class="shrink-0 text-[11px] font-medium text-zinc-500">{{ __('On court') }}</span>
                    @endif
                    <span class="shrink-0 text-xs font-medium tabular-nums text-zinc-500">{{ intdiv($played, 60) }}:{{ sprintf('%02d', $played % 60) }}</span>
                    @php($personalFouls = $this->fouls['players'][$player->id] ?? 0)
                    <span @class(['shrink-0 text-sm font-medium tabular-nums', 'text-green-700' => $personalFouls >= 1 && $personalFouls <= 3, 'text-orange-600' => $personalFouls === 4, 'text-red-700' => $personalFouls >= 5])>{{ $personalFouls }} {{ __('FP') }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="min-h-0 overflow-y-auto overscroll-contain px-3 py-4">
        @include('livewire.statistics.log', ['lines' => $this->lines, 'keyPrefix' => 'desktop-line', 'homeColor' => $this->game->home_color->hex(), 'awayColor' => $this->game->away_color->hex()])
    </section>
</div>
@endif

<flux:modal wire:model="choosingTimeout" class="max-w-md !bg-white">
    @php($timeoutAtHome = $this->game->home_away === \App\Enums\HomeAway::Home)
    <div class="space-y-4 text-zinc-900">
        <div class="pe-8 text-center">
            <h2 class="text-base font-semibold">{{ __('Record timeout') }}</h2>
            <p class="mt-1 text-sm text-zinc-500">{{ __('Select which team called the timeout') }}</p>
        </div>
        <div class="space-y-3">
            <button type="button" wire:click="recordTimeout('home')" class="flex w-full items-center justify-between gap-3 rounded-lg border border-zinc-300 px-4 py-3 text-left hover:border-zinc-900">
                <span class="min-w-0 truncate text-sm font-medium">{{ $timeoutAtHome ? $teamName : $opponentName }}</span>
                <span class="shrink-0 text-xs text-zinc-500">{{ __('At home') }}</span>
            </button>
            <button type="button" wire:click="recordTimeout('away')" class="flex w-full items-center justify-between gap-3 rounded-lg border border-zinc-300 px-4 py-3 text-left hover:border-zinc-900">
                <span class="min-w-0 truncate text-sm font-medium">{{ $timeoutAtHome ? $opponentName : $teamName }}</span>
                <span class="shrink-0 text-xs text-zinc-500">{{ __('Away') }}</span>
            </button>
        </div>
    </div>
</flux:modal>

<flux:modal wire:model="choosingPlayer" class="w-full max-w-3xl !bg-white" style="width: min(48rem, calc(100vw - 3rem))">
    @if ($pendingStat)
        <div class="space-y-4 text-zinc-900">
            <div class="pe-8 text-center">
                <h2 class="text-base font-semibold">{{ __($pendingStat->pickerTitle()) }}</h2>
            </div>
            @error('clock')
                <p class="text-center text-xs text-zinc-500">{{ $message }}</p>
            @enderror
            <div class="grid grid-cols-2 content-start gap-3 overflow-y-auto" style="max-height: 70vh">
                @foreach ($this->courtPlayers as $player)
                    <button
                        type="button"
                        wire:key="player-{{ $player->id }}"
                        wire:click="record({{ $player->id }})"
                        class="flex items-center gap-3 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-left hover:border-zinc-900"
                    >
                        <span class="pointer-events-none flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-zinc-900">
                            @if ($player->photo_url)
                                <img src="{{ $player->photo_url }}" alt="" class="size-full object-cover" />
                            @else
                                <flux:icon name="user" class="size-7" />
                            @endif
                        </span>
                        <span class="min-w-0">
                            @if ($player->number !== null)
                                <span class="block text-lg font-semibold leading-tight">#{{ $player->number }}</span>
                            @endif
                            @foreach ($player->nameLines() as $line)
                                <span class="block truncate text-sm leading-tight">{{ $line }}</span>
                            @endforeach
                        </span>
                    </button>
                @endforeach
                <button
                    type="button"
                    wire:click="recordOpponent"
                    class="flex items-center gap-3 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-left hover:border-zinc-900"
                >
                    <span class="pointer-events-none flex size-14 shrink-0 items-center justify-center rounded-full border border-zinc-900">
                        <flux:icon name="user-group" class="size-7" />
                    </span>
                    <span class="min-w-0 text-base font-semibold">{{ $opponentName }}</span>
                </button>
            </div>
        </div>
    @endif
</flux:modal>

<flux:modal wire:model="choosingShotLocation" class="max-w-2xl !border-0 !bg-zinc-900 !p-0" style="width: min(36rem, calc(100vw - 2rem))">
    @if ($pendingStat?->isFieldGoal())
        <div class="relative px-4 py-4 text-white sm:px-6">
            <button
                type="button"
                wire:click="cancelShotLocation"
                class="absolute end-4 top-4 z-10 text-sm font-medium text-white/80 hover:text-white"
            >
                {{ __('Cancel') }}
            </button>
            <div class="pe-16 text-center">
                <h2 class="text-base font-semibold">{{ __('Mark shot location') }}</h2>
                <p class="mt-1 text-sm text-white/70">{{ __($pendingStat->pickerTitle()) }}</p>
            </div>
            @error('shotLocation')
                <p class="mt-3 text-center text-sm text-red-300">{{ $message }}</p>
            @enderror
            <div class="mt-4">
                <x-shot-court />
            </div>
        </div>
    @endif
</flux:modal>

<flux:modal wire:model="editingAction" class="max-w-2xl !bg-white">
    <div class="space-y-4 text-zinc-900">
        <div class="pe-8 text-center">
            <h2 class="text-base font-semibold">{{ __('Edit') }}</h2>
        </div>
        <div class="grid grid-cols-2 items-start gap-4">
            <div class="space-y-2">
                @foreach ($this->attachedPlayers as $player)
                    <button
                        type="button"
                        wire:key="edit-player-{{ $player->id }}"
                        wire:click="selectEditingPlayer({{ $player->id }})"
                        @class([
                            'flex w-full items-center gap-3 rounded-lg border px-3 py-2 text-left',
                            'border-zinc-900 ring-2 ring-zinc-900 ring-inset' => ! $editingForOpponent && $editingPlayerId === $player->id,
                            'border-zinc-300' => $editingForOpponent || $editingPlayerId !== $player->id,
                        ])
                    >
                        <span class="w-8 shrink-0 text-sm font-semibold tabular-nums">{{ $player->number ?? '—' }}</span>
                        <span class="min-w-0 truncate text-sm">{{ $player->name }}</span>
                    </button>
                @endforeach
                <button
                    type="button"
                    wire:click="selectEditingOpponent"
                    @class([
                        'flex w-full items-center rounded-lg border px-3 py-2 text-left text-sm font-medium',
                        'border-zinc-900 ring-2 ring-zinc-900 ring-inset' => $editingForOpponent,
                        'border-zinc-300' => ! $editingForOpponent,
                    ])
                >{{ $opponentName }}</button>
            </div>
            <div class="space-y-2">
                @foreach ([
                    [\App\Enums\StatType::TwoPointMade, 'made'],
                    [\App\Enums\StatType::TwoPointMissed, 'missed'],
                    [\App\Enums\StatType::ThreePointMade, 'made'],
                    [\App\Enums\StatType::ThreePointMissed, 'missed'],
                    [\App\Enums\StatType::FreeThrowMade, 'made'],
                    [\App\Enums\StatType::FreeThrowMissed, 'missed'],
                    [\App\Enums\StatType::DefensiveRebound, 'plain'],
                    [\App\Enums\StatType::OffensiveRebound, 'plain'],
                    [\App\Enums\StatType::Assist, 'plain'],
                    [\App\Enums\StatType::Block, 'plain'],
                    [\App\Enums\StatType::Steal, 'plain'],
                    [\App\Enums\StatType::Turnover, 'plain'],
                    [\App\Enums\StatType::Foul, 'plain'],
                ] as [$stat, $tone])
                    <button
                        type="button"
                        wire:click="selectEditingType('{{ $stat->value }}')"
                        @class([
                            'w-full rounded-lg border bg-white px-3 py-2 text-sm font-medium',
                            'border-green-700 text-green-700' => $tone === 'made',
                            'border-red-700 text-red-700' => $tone === 'missed',
                            'border-zinc-300 text-zinc-900' => $tone === 'plain',
                            'ring-2 ring-zinc-900 ring-inset' => $editingType === $stat->value,
                        ])
                    >{{ $stat->logLabel() }}</button>
                @endforeach
            </div>
        </div>
        <div class="flex justify-end">
            <button type="button" wire:click="saveLogLine" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">{{ __('Confirm') }}</button>
        </div>
    </div>
</flux:modal>

<flux:modal wire:model="confirmingQuarterEnd" class="max-w-md !bg-white">
    <div class="space-y-6 text-zinc-900">
        <div class="pe-8 text-center">
            <h2 class="text-base font-semibold">{{ $quarterTitle }}</h2>
            <p class="mt-3 text-sm text-zinc-600">{{ $endPeriodQuestion }}</p>
            <p class="mt-1 text-sm font-medium text-red-700">{{ __('You cannot undo this action.') }}</p>
        </div>
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <button type="button" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-800">{{ __('Cancel') }}</button>
            </flux:modal.close>
            <button type="button" wire:click="endQuarter" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">{{ __('Confirm') }}</button>
        </div>
    </div>
</flux:modal>

<flux:modal wire:model="confirmingGameFinish" class="max-w-md !bg-white">
    <div class="space-y-6 text-zinc-900">
        <div class="pe-8 text-center">
            <h2 class="text-base font-semibold">{{ __('Finish game?') }}</h2>
            <p class="mt-3 text-sm text-zinc-600">{{ __('Mark this game as played and save the final score from the collected stats?') }}</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button type="button" wire:click="leaveWithoutFinishing" class="w-full rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-800 sm:w-auto">{{ __('Leave without finishing') }}</button>
            <button type="button" wire:click="finishGameAndExit" class="w-full rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white sm:w-auto">{{ __('Finish game') }}</button>
        </div>
    </div>
</flux:modal>
@once
    <script>
        document.addEventListener('alpine:init', () => {
            if (! window.Alpine.store('collectPane')) {
                window.Alpine.store('collectPane', { name: 'scoring' });
            }

            if (window.Alpine.store('gameClock')) {
                return;
            }

            window.Alpine.store('gameClock', {
                componentId: null,
                wire: null,
                remaining: 600,
                formatted: '10:00',
                running: false,
                timer: null,
                bind(wire) {
                    const id = wire.__instance.id;

                    if (this.componentId !== id) {
                        this.stop();
                        this.componentId = id;
                        this.wire = wire;
                    }

                    const parsed = this.parse(wire.clock);
                    this.remaining = parsed ?? 600;
                    this.formatted = this.format(this.remaining);
                },
                syncFromStatisticsRecord() {
                    const root = document.querySelector('[data-statistics-record]')?.closest('[wire\\:id]');
                    const id = root?.getAttribute('wire:id');

                    if (! id) {
                        return;
                    }

                    const component = window.Livewire?.find?.(id);

                    if (component?.$wire) {
                        this.bind(component.$wire);
                    }
                },
                toggle() {
                    if (this.running) {
                        this.pause();

                        return;
                    }

                    this.start();
                },
                press() {
                    const wire = this.wire;

                    if (wire === null) {
                        return;
                    }

                    if (wire.awaitingQuarterStart) {
                        wire.startQuarter();

                        return;
                    }

                    if (! this.running && this.remaining === 0 && ! wire.quarterEnded) {
                        wire.askToEndQuarter();

                        return;
                    }

                    if (! this.running) {
                        wire.recordQuarterStart();
                        this.start();

                        return;
                    }

                    this.pause();
                },
                start() {
                    if (this.running || this.remaining <= 0) {
                        return;
                    }

                    this.running = true;
                    this.timer = setInterval(() => this.tick(), 1000);
                },
                pause() {
                    this.stop();
                    this.publish();
                },
                stop() {
                    this.running = false;

                    if (this.timer !== null) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                },
                adjust(seconds) {
                    this.remaining = Math.min((99 * 60) + 59, Math.max(0, this.remaining + seconds));
                    this.formatted = this.format(this.remaining);

                    if (this.remaining === 0) {
                        this.stop();
                    }

                    this.publish();
                },
                tick() {
                    if (! this.alive()) {
                        this.stop();

                        return;
                    }

                    if (this.remaining <= 1) {
                        this.remaining = 0;
                        this.formatted = this.format(0);
                        this.stop();
                        this.publish();

                        return;
                    }

                    this.remaining -= 1;
                    this.formatted = this.format(this.remaining);
                    this.publish();
                },
                apply(value) {
                    this.stop();
                    this.setFrom(value);
                },
                hold() {
                    this.stop();
                    this.remaining = 0;
                    this.formatted = '--:--';
                },
                begin(value) {
                    this.stop();
                    this.setFrom(value ?? '10:00');
                    this.start();
                },
                setFrom(value) {
                    const parsed = this.parse(value);

                    if (parsed === null) {
                        return;
                    }

                    this.remaining = parsed;
                    this.formatted = this.format(parsed);

                    if (this.remaining === 0) {
                        this.stop();
                    }

                    this.publish();
                },
                parse(value) {
                    if (typeof value !== 'string' || ! /^\d{1,2}:\d{2}$/.test(value)) {
                        return null;
                    }

                    const [minutes, seconds] = value.split(':').map((part) => Number(part));

                    if (minutes > 99 || seconds > 59) {
                        return null;
                    }

                    return (minutes * 60) + seconds;
                },
                format(total) {
                    const minutes = Math.floor(total / 60);
                    const seconds = total % 60;

                    return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                },
                alive() {
                    return this.componentId !== null && document.querySelector(`[wire\\:id="${this.componentId}"]`) !== null;
                },
                publish() {
                    if (! this.alive()) {
                        return;
                    }

                    this.wire?.$set('clock', this.formatted, false);
                },
            });

            document.addEventListener('pointerdown', (event) => {
                const clock = window.Alpine.store('gameClock');

                if (! clock?.alive()) {
                    return;
                }

                if (event.target instanceof Element && event.target.closest('input, select, textarea')) {
                    return;
                }

                clock.publish();
            }, true);

            document.addEventListener('livewire:navigated', () => {
                requestAnimationFrame(() => {
                    window.Alpine.store('gameClock')?.syncFromStatisticsRecord();
                });
            });
        });
    </script>
@endonce
@script
    <script>
        const bindGameClock = () => {
            window.Alpine.store('gameClock')?.bind($wire);
        };

        if (window.Alpine.store('gameClock')) {
            bindGameClock();
        } else {
            document.addEventListener('alpine:init', bindGameClock, { once: true });
        }

        document.addEventListener('livewire:navigated', () => {
            requestAnimationFrame(bindGameClock);
        });
    </script>
@endscript
</div>
