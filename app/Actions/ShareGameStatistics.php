<?php

namespace App\Actions;

use App\Models\Game;
use Illuminate\Support\Str;

class ShareGameStatistics
{
    public function publish(Game $game): Game
    {
        if ($game->share_token !== null) {
            return $game;
        }

        return $this->storeToken($game);
    }

    public function rotate(Game $game): Game
    {
        return $this->storeToken($game);
    }

    public function revoke(Game $game): Game
    {
        $game->forceFill(['share_token' => null])->save();

        return $game;
    }

    private function storeToken(Game $game): Game
    {
        $game->forceFill(['share_token' => Str::random(40)])->save();

        return $game;
    }
}
