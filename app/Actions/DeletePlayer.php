<?php

namespace App\Actions;

use App\Models\Player;
use Illuminate\Support\Facades\Storage;

class DeletePlayer
{
    public function handle(Player $player): void
    {
        if (is_string($player->photo) && $player->photo !== '') {
            Storage::disk('public')->delete($player->photo);
        }

        $player->delete();
    }
}
