<?php

namespace App\Actions;

use App\Enums\GameStatus;
use App\Enums\HomeAway;
use App\Enums\PermissionName;
use App\Models\Game;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateGame
{
    public function handle(
        User $actor,
        Game $game,
        int $teamId,
        ?string $fpbId,
        string $opponent,
        CarbonImmutable $dateTime,
        ?string $place,
        HomeAway $homeAway,
        ?string $competition,
        ?string $journey,
        ?int $homePoints,
        ?int $awayPoints,
        GameStatus $status,
    ): Game {
        $team = $this->teamFor($actor, $teamId);

        $game->update([
            'team_id' => $team->id,
            'fpb_id' => $this->blankToNull($fpbId),
            'opponent' => $opponent,
            'date_time' => $dateTime,
            'place' => $this->blankToNull($place),
            'home_away' => $homeAway,
            'competition' => $this->blankToNull($competition),
            'journey' => $this->blankToNull($journey),
            'home_points' => $homePoints,
            'away_points' => $awayPoints,
            'status' => $status,
        ]);

        return $game;
    }

    private function teamFor(User $actor, int $teamId): Team
    {
        $team = Team::query()->findOrFail($teamId);

        if ($actor->can(PermissionName::CreateClubs->value)) {
            return $team;
        }

        if ($actor->club_id !== $team->club_id) {
            throw new AuthorizationException;
        }

        return $team;
    }

    private function blankToNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
