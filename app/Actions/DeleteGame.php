<?php

namespace App\Actions;

use App\Models\Game;

class DeleteGame
{
    public function handle(Game $game): void
    {
        $game->delete();
    }
}
