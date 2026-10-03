<?php

namespace App\Actions;

use App\Models\Game;

class DeleteLatestGameEvent
{
    public function handle(Game $game): void
    {
        $game->latestUndoableEvent()?->delete();
    }
}
