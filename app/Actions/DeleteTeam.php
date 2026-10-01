<?php

namespace App\Actions;

use App\Models\Team;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteTeam
{
    /**
     * Delete a team that has no players or games.
     */
    public function handle(Team $team): void
    {
        if ($team->players()->exists()) {
            throw ValidationException::withMessages([
                'team' => __('This team still has players.'),
            ]);
        }

        if ($team->games()->exists()) {
            throw ValidationException::withMessages([
                'team' => __('This team still has games.'),
            ]);
        }

        if (is_string($team->photo) && $team->photo !== '') {
            Storage::disk('public')->delete($team->photo);
        }

        $team->delete();
    }
}
