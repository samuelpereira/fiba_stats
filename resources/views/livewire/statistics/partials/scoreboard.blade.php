@php($weAreHome = $weAreHome ?? $this->game->home_away === \App\Enums\HomeAway::Home)

<div
    class="shrink-0 bg-zinc-900 px-3 py-2 text-white lg:px-4 lg:py-3"
    :class="{ '!bg-green-700': $store.gameClock?.running }"
    @if ($bindGameClock ?? false)
        x-init="$nextTick(() => Alpine.store('gameClock')?.bind($wire))"
    @endif
>
    <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-2 lg:gap-3">
        <div class="flex items-end">
            <button type="button" @click="$store.gameClock.pause()" wire:click="chooseTimeout" @disabled(! $this->periodIsOpen) @class(['flex flex-col items-center gap-1 text-white', 'opacity-40' => ! $this->periodIsOpen])>
                <flux:icon name="clock" class="size-5" />
                <span class="text-[11px] leading-none">{{ __('Timeout') }}</span>
            </button>
        </div>

        <div class="flex items-center justify-center gap-2 lg:gap-3">
            <button type="button" @click="$store.gameClock.adjust(-5)" class="flex flex-col items-center gap-1 text-white">
                <flux:icon name="minus" class="size-5" />
                <span class="text-[11px] leading-none">{{ __('-5 sec') }}</span>
            </button>
            <div class="text-center">
                @if ($editingClock)
                    <label>
                        <span class="sr-only">{{ __('Clock') }}</span>
                        <input wire:model="clock" x-init="if ($el.offsetParent !== null) $el.focus()" @change="$store.gameClock.setFrom($event.target.value); $wire.$set('editingClock', false)" type="text" inputmode="numeric" maxlength="5" class="w-20 rounded-md border border-zinc-900 bg-white px-1 py-1 text-center text-3xl font-semibold tabular-nums lg:w-24 lg:px-2 lg:text-4xl" />
                    </label>
                @else
                    <button type="button" @click="if (! $store.gameClock.running && ! $wire.awaitingQuarterStart) $wire.$set('editingClock', true)" class="text-3xl font-bold tracking-tight tabular-nums lg:text-4xl" aria-label="{{ __('Edit clock') }}">
                        <span x-text="$wire.awaitingQuarterStart ? '--:--' : $store.gameClock.formatted">{{ $awaitingQuarterStart ? '--:--' : $clock }}</span>
                    </button>
                @endif
                <p class="text-xs text-zinc-300 lg:text-sm">{{ $awaitingQuarterStart ? $awaitingPeriodLabel : $periodName }}</p>
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
                class="flex h-12 min-w-12 flex-col items-center justify-center gap-0.5 rounded-xl px-2 text-[11px] font-medium leading-none lg:h-14 lg:min-w-14"
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
        <p class="pt-1 text-center text-sm text-zinc-300">{{ $message }}</p>
    @enderror
</div>

<div class="grid shrink-0 grid-cols-2 gap-2 border-b border-zinc-200 bg-zinc-100 px-2 py-2 lg:gap-3 lg:px-4 lg:py-4">
    <div @class(['min-w-0 rounded-lg border border-zinc-200 bg-white px-3 py-2 shadow-sm lg:px-4 lg:py-3', 'order-1' => $weAreHome, 'order-2 text-right' => ! $weAreHome])>
        <p class="truncate text-sm font-semibold">{{ $teamName }}</p>
        <div class="flex items-center justify-between gap-2 lg:gap-3">
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
    <div @class(['min-w-0 rounded-lg border border-zinc-200 bg-white px-3 py-2 shadow-sm lg:px-4 lg:py-3', 'order-2 text-right' => $weAreHome, 'order-1' => ! $weAreHome])>
        <p class="truncate text-sm font-semibold">{{ $opponentName }}</p>
        <div class="flex items-center justify-between gap-2 lg:gap-3">
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
