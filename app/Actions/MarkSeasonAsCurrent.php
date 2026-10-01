<?php

namespace App\Actions;

use App\Models\Season;
use Illuminate\Support\Facades\DB;

class MarkSeasonAsCurrent
{
    /**
     * Leave exactly one season marked as current.
     */
    public function handle(Season $season): Season
    {
        DB::transaction(function () use ($season): void {
            Season::query()->lockForUpdate()->update(['is_current' => false]);

            $season->is_current = true;
            $season->save();
        });

        return $season;
    }
}
