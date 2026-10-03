<?php

namespace App\Actions;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class FinishCollectedGame
{
    public function handle(User $actor, Game $game): Game
    {
        if (! $actor->can('collect', $game)) {
            throw new AuthorizationException;
        }

        if (! $game->canFinishAfterFourthQuarter()) {
            throw new AuthorizationException;
        }

        $totals = $this->scoreTotals($game);

        $game->update([
            'home_points' => $totals['home'],
            'away_points' => $totals['away'],
            'status' => GameStatus::Played,
        ]);

        return $game->refresh();
    }

    /**
     * @return array{home: int, away: int}
     */
    private function scoreTotals(Game $game): array
    {
        $home = 0;
        $away = 0;

        foreach ($game->pointsByQuarter() as $quarter) {
            $home += $quarter['home'];
            $away += $quarter['away'];
        }

        return [
            'home' => $home,
            'away' => $away,
        ];
    }
}
