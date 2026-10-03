<?php

namespace App\Actions;

use App\Models\Player;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeletePlayer
{
    public function handle(Player $player): void
    {
        if ($player->events()->exists()) {
            throw ValidationException::withMessages([
                'player' => __('This player still has a statistics log.'),
            ]);
        }

        if (is_string($player->photo) && $player->photo !== '') {
            Storage::disk('public')->delete($player->photo);
        }

        $player->delete();
    }
}
