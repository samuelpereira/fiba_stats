<?php

namespace App\Actions;

use App\Models\Season;
use Illuminate\Validation\ValidationException;

class DeleteSeason
{
    /**
     * Delete a season that has no teams.
     */
    public function handle(Season $season): void
    {
        if ($season->teams()->exists()) {
            throw ValidationException::withMessages([
                'season' => __('This season still has teams.'),
            ]);
        }

        $season->delete();
    }
}
