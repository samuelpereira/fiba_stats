<?php

namespace App\Actions;

use App\Models\Game;
use Illuminate\Validation\ValidationException;

class DeleteGame
{
    public function handle(Game $game): void
    {
        if ($game->events()->exists()) {
            throw ValidationException::withMessages([
                'game' => __('This game still has a statistics log.'),
            ]);
        }

        $game->delete();
    }
}
