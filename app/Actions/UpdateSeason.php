<?php

namespace App\Actions;

use App\Models\Season;
use InvalidArgumentException;

class UpdateSeason
{
    /**
     * Rename a season and regenerate its slug and dates. The current flag stays unchanged.
     */
    public function handle(Season $season, string $name): Season
    {
        $attributes = Season::attributesFromName($name);

        if ($attributes === null) {
            throw new InvalidArgumentException('The season name is invalid.');
        }

        $season->name = $name;
        $season->slug = $attributes['slug'];
        $season->starts_at = $attributes['starts_at'];
        $season->ends_at = $attributes['ends_at'];
        $season->save();

        return $season;
    }
}
