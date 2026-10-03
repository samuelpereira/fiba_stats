<?php

namespace App\Actions;

use App\Enums\HomeAway;
use App\Enums\StatType;
use App\Models\Game;
use App\Models\Player;
use App\Support\ShotCourt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImportGameLog
{
    public function __construct(private RecordGameEvent $recordGameEvent) {}

    public function handle(Game $game, string $csv): int
    {
        $game->loadMissing('team');

        $parsed = $this->rows($game, $csv);

        DB::transaction(function () use ($game, $parsed): void {
            $game->events()->delete();
            $game->players()->sync($parsed['squad']);

            foreach ($parsed['events'] as $event) {
                if ($event['type'] === StatType::Substitution) {
                    if ($event['playerId'] === null || $event['outgoingId'] === null) {
                        $this->reject(__('A substitution needs the player who leaves.'));
                    }

                    $this->recordGameEvent->substitute(
                        $game,
                        $event['playerId'],
                        $event['outgoingId'],
                        $event['quarter'],
                        $event['clock'],
                    );

                    continue;
                }

                $this->recordGameEvent->handle(
                    $game,
                    $event['playerId'],
                    $event['type'],
                    $event['quarter'],
                    $event['clock'],
                    $event['side'],
                    $event['courtX'],
                    $event['courtY'],
                );
            }
        });

        return count($parsed['events']);
    }

    /**
     * @return array{squad: array<int, array{starter: bool}>, events: list<array{type: StatType, quarter: int, clock: string, side: HomeAway|null, playerId: int|null, outgoingId: int|null, courtX: int|null, courtY: int|null}>}
     */
    private function rows(Game $game, string $csv): array
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", preg_replace('/^\xEF\xBB\xBF/', '', $csv) ?? $csv));
        $delimiter = ',';
        $map = [];
        $headerRead = false;
        $events = [];
        $period = null;
        /** @var list<int>|null $lineup */
        $lineup = null;
        /** @var list<int>|null $starters */
        $starters = null;
        /** @var array<int, int> $idsByNumber */
        $idsByNumber = [];

        foreach (Player::query()->where('team_id', $game->team_id)->whereNotNull('number')->get(['id', 'number']) as $player) {
            $idsByNumber[(int) $player->number] = $player->id;
        }

        foreach ($lines as $index => $line) {
            if (trim($line) === '') {
                continue;
            }

            $lineNumber = $index + 1;

            if (! $headerRead) {
                $delimiter = substr_count($line, ';') > substr_count($line, ',') ? ';' : ',';
                $map = $this->headerMap($this->cells($line, $delimiter));
                $headerRead = true;

                continue;
            }

            $cells = $this->cells($line, $delimiter);

            if (implode('', $cells) === '') {
                continue;
            }

            $this->assertTeams($game, $cells, $map, $lineNumber);
            $quarter = $this->quarter($this->cell($cells, $map, 'time_period'), $lineNumber);
            $opponent = $this->opponentFlag($this->cell($cells, $map, 'is_opp'), $lineNumber);
            $stat = $this->cell($cells, $map, 'stat');

            if (strcasecmp($stat, 'FloorTime') === 0) {
                if ($opponent) {
                    $this->openPeriod($game, $events, $period, $quarter);

                    continue;
                }

                $numbers = $this->lineupNumbers($this->cell($cells, $map, 'lineup'), $lineNumber);

                foreach ($numbers as $number) {
                    $this->playerId($idsByNumber, $number, $lineNumber);
                }

                $this->openPeriod($game, $events, $period, $quarter);

                if ($starters === null) {
                    $starters = $numbers;
                    $lineup = $numbers;

                    continue;
                }

                $lineup = $this->substitute($idsByNumber, $events, $lineup ?? [], $numbers, $quarter, $lineNumber);

                continue;
            }

            $this->openPeriod($game, $events, $period, $quarter);
            $type = $this->type($stat, $lineNumber);
            $side = $opponent ? $game->home_away->opposite() : $game->home_away;
            $playerId = null;

            if (! $opponent) {
                $playerId = $this->playerId($idsByNumber, $this->jersey($this->cell($cells, $map, 'player'), $lineNumber), $lineNumber);
            }

            $court = $this->court($cells, $map, $lineNumber);
            $events[] = $this->event($type, $quarter, '00:00', $side, $playerId, null, $court['x'], $court['y']);
        }

        if (! $headerRead || $events === []) {
            $this->reject(__('The CSV has no log rows.'));
        }

        if ($starters === null) {
            $this->reject(__('The CSV does not include the starting five.'));
        }

        if ($period !== null) {
            $events[] = $this->event(StatType::QuarterEnded, $period, '00:00', null, null, null);
        }

        return [
            'squad' => $this->squad($idsByNumber, $starters, $events),
            'events' => $events,
        ];
    }

    /**
     * @param  list<array{type: StatType, quarter: int, clock: string, side: HomeAway|null, playerId: int|null, outgoingId: int|null, courtX: int|null, courtY: int|null}>  $events
     *
     * @param-out int $period
     */
    private function openPeriod(Game $game, array &$events, ?int &$period, int $quarter): void
    {
        if ($period === $quarter) {
            return;
        }

        if ($period !== null) {
            $events[] = $this->event(StatType::QuarterEnded, $period, '00:00', null, null, null);
        }

        $minutes = $quarter > 4 ? $game->overtime_minutes : $game->quarter_minutes;
        $events[] = $this->event(StatType::QuarterStarted, $quarter, sprintf('%02d:00', $minutes), null, null, null);
        $period = $quarter;
    }

    /**
     * @param  array<int, int>  $idsByNumber
     * @param  list<array{type: StatType, quarter: int, clock: string, side: HomeAway|null, playerId: int|null, outgoingId: int|null, courtX: int|null, courtY: int|null}>  $events
     * @param  list<int>  $current
     * @param  list<int>  $next
     * @return list<int>
     */
    private function substitute(array $idsByNumber, array &$events, array $current, array $next, int $quarter, int $lineNumber): array
    {
        $outgoing = array_values(array_diff($current, $next));
        $incoming = array_values(array_diff($next, $current));

        if ($outgoing === []) {
            return $current;
        }

        if (count($outgoing) !== count($incoming)) {
            $this->rejectRow($lineNumber, __('The lineup change does not replace the same number of players.'));
        }

        foreach ($outgoing as $index => $number) {
            $events[] = $this->event(
                StatType::Substitution,
                $quarter,
                '00:00',
                null,
                $this->playerId($idsByNumber, $incoming[$index], $lineNumber),
                $this->playerId($idsByNumber, $number, $lineNumber),
            );
        }

        return $next;
    }

    /**
     * @param  array<int, int>  $idsByNumber
     * @param  list<int>  $starters
     * @param  list<array{type: StatType, quarter: int, clock: string, side: HomeAway|null, playerId: int|null, outgoingId: int|null, courtX: int|null, courtY: int|null}>  $events
     * @return array<int, array{starter: bool}>
     */
    private function squad(array $idsByNumber, array $starters, array $events): array
    {
        $squad = [];

        foreach ($starters as $number) {
            $squad[$this->playerId($idsByNumber, $number, 0)] = ['starter' => true];
        }

        foreach ($events as $event) {
            foreach ([$event['playerId'], $event['outgoingId']] as $id) {
                if ($id === null || isset($squad[$id])) {
                    continue;
                }

                $squad[$id] = ['starter' => false];
            }
        }

        return $squad;
    }

    /**
     * @param  list<string>  $header
     * @return array<string, int>
     */
    private function headerMap(array $header): array
    {
        $required = ['player', 'user_team', 'opp_team', 'stat', 'lineup', 'time_period', 'is_opp'];
        $columns = [...$required, 'court_pos_x', 'court_pos_y'];
        $map = [];

        foreach ($header as $index => $name) {
            $name = strtolower(trim($name));

            if (! in_array($name, $columns, true) || isset($map[$name])) {
                continue;
            }

            $map[$name] = $index;
        }

        if (array_diff($required, array_keys($map)) !== []) {
            $this->reject(__('The CSV needs the columns player, user_team, opp_team, stat, lineup, time_period, and is_opp.'));
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $map
     * @param  list<string>  $cells
     */
    private function assertTeams(Game $game, array $cells, array $map, int $lineNumber): void
    {
        $team = $this->cell($cells, $map, 'user_team');
        $opponent = $this->cell($cells, $map, 'opp_team');

        if (strcasecmp($team, $game->team->name) === 0 && strcasecmp($opponent, $game->opponent) === 0) {
            return;
        }

        $this->rejectRow($lineNumber, __('The CSV is for :team against :opponent.', [
            'team' => $team,
            'opponent' => $opponent,
        ]));
    }

    /**
     * @return list<int>
     */
    private function lineupNumbers(string $lineup, int $lineNumber): array
    {
        $numbers = [];

        foreach (explode(',', $lineup) as $part) {
            if (trim($part) === '') {
                continue;
            }

            $numbers[] = $this->jersey($part, $lineNumber);
        }

        if (count($numbers) !== 5 || count(array_unique($numbers)) !== 5) {
            $this->rejectRow($lineNumber, __('The lineup must list 5 players.'));
        }

        return $numbers;
    }

    private function type(string $stat, int $lineNumber): StatType
    {
        $type = match ($stat) {
            'TwoPointFG' => StatType::TwoPointMade,
            'TwoPointFGMiss' => StatType::TwoPointMissed,
            'ThreePointFG' => StatType::ThreePointMade,
            'ThreePointFGMiss' => StatType::ThreePointMissed,
            'OnePointFG' => StatType::FreeThrowMade,
            'OnePointFGMiss' => StatType::FreeThrowMissed,
            'DefReb' => StatType::DefensiveRebound,
            'OffReb' => StatType::OffensiveRebound,
            'Turnover' => StatType::Turnover,
            'Steal' => StatType::Steal,
            'Assist' => StatType::Assist,
            'Block' => StatType::Block,
            'Foul' => StatType::Foul,
            default => null,
        };

        if ($type instanceof StatType) {
            return $type;
        }

        $this->rejectRow($lineNumber, __('Unknown action :action.', ['action' => $stat]));
    }

    private function opponentFlag(string $value, int $lineNumber): bool
    {
        if ($value === '1') {
            return true;
        }

        if ($value === '0') {
            return false;
        }

        $this->rejectRow($lineNumber, __('The side flag must be 0 or 1.'));
    }

    private function quarter(string $value, int $lineNumber): int
    {
        if (! ctype_digit($value) || (int) $value < 1 || (int) $value > 255) {
            $this->rejectRow($lineNumber, __('The period must be a number from 1 to 255.'));
        }

        return (int) $value;
    }

    private function jersey(string $value, int $lineNumber): int
    {
        if (preg_match('/^#(\d+)/', trim($value), $matches) !== 1) {
            $this->rejectRow($lineNumber, __('Player :player is not a jersey number.', ['player' => $value]));
        }

        return (int) $matches[1];
    }

    /**
     * @param  array<int, int>  $idsByNumber
     */
    private function playerId(array $idsByNumber, int $number, int $lineNumber): int
    {
        $id = $idsByNumber[$number] ?? null;

        if ($id === null) {
            $this->rejectRow($lineNumber, __('No player wears number :number.', ['number' => $number]));
        }

        return $id;
    }

    /**
     * @param  array<string, int>  $map
     * @param  list<string>  $cells
     * @return array{x: int|null, y: int|null}
     */
    private function court(array $cells, array $map, int $lineNumber): array
    {
        $x = isset($map['court_pos_x']) ? $this->cell($cells, $map, 'court_pos_x') : '';
        $y = isset($map['court_pos_y']) ? $this->cell($cells, $map, 'court_pos_y') : '';

        if ($x === '' && $y === '') {
            return ['x' => null, 'y' => null];
        }

        if ($x === '' || $y === '') {
            $this->rejectRow($lineNumber, __('A court position needs both court_pos_x and court_pos_y.'));
        }

        $point = ShotCourt::fromFeet($this->feet($x, $lineNumber), $this->feet($y, $lineNumber));

        if ($point['x'] < 0 || $point['x'] > 65535 || $point['y'] < 0 || $point['y'] > 65535) {
            $this->rejectRow($lineNumber, __('The court position is outside the court.'));
        }

        return $point;
    }

    private function feet(string $value, int $lineNumber): float
    {
        $normalized = str_replace(',', '.', $value);

        if (! is_numeric($normalized)) {
            $this->rejectRow($lineNumber, __('The court position :position is not a number.', [
                'position' => $value,
            ]));
        }

        $feet = (float) $normalized;

        if (! is_finite($feet)) {
            $this->rejectRow($lineNumber, __('The court position :position is not a number.', [
                'position' => $value,
            ]));
        }

        return $feet;
    }

    /**
     * @param  array<string, int>  $map
     * @param  list<string>  $cells
     */
    private function cell(array $cells, array $map, string $column): string
    {
        return trim($cells[$map[$column]] ?? '');
    }

    /**
     * @return list<string>
     */
    private function cells(string $line, string $delimiter): array
    {
        return array_map(
            fn (mixed $cell): string => is_string($cell) ? $cell : '',
            str_getcsv($line, $delimiter, '"', ''),
        );
    }

    /**
     * @return array{type: StatType, quarter: int, clock: string, side: HomeAway|null, playerId: int|null, outgoingId: int|null, courtX: int|null, courtY: int|null}
     */
    private function event(StatType $type, int $quarter, string $clock, ?HomeAway $side, ?int $playerId, ?int $outgoingId, ?int $courtX = null, ?int $courtY = null): array
    {
        return [
            'type' => $type,
            'quarter' => $quarter,
            'clock' => $clock,
            'side' => $side,
            'playerId' => $playerId,
            'outgoingId' => $outgoingId,
            'courtX' => $courtX,
            'courtY' => $courtY,
        ];
    }

    private function rejectRow(int $lineNumber, string $message): never
    {
        if ($lineNumber === 0) {
            $this->reject($message);
        }

        $this->reject(__('Row :row: :message', [
            'row' => $lineNumber,
            'message' => $message,
        ]));
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages([
            'logFile' => $message,
        ]);
    }
}
