<?php

namespace App\Actions;

use App\Models\Season;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateSeason
{
    /**
     * Create a season from a name such as 2026/2027. The first season becomes current.
     */
    public function handle(string $name): Season
    {
        $attributes = Season::attributesFromName($name);

        if ($attributes === null) {
            throw new InvalidArgumentException('The season name is invalid.');
        }

        return DB::transaction(function () use ($name, $attributes): Season {
            $isCurrent = ! Season::query()->where('is_current', true)->lockForUpdate()->exists();

            return Season::query()->create([
                'name' => $name,
                'slug' => $attributes['slug'],
                'starts_at' => $attributes['starts_at'],
                'ends_at' => $attributes['ends_at'],
                'is_current' => $isCurrent,
            ]);
        });
    }
}
