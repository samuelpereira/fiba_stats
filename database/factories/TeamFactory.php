<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Club;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $words = fake()->unique()->words(2, true);
        $name = is_array($words) ? implode(' ', $words) : $words;

        return [
            'club_id' => Club::factory(),
            'season_id' => Season::factory(),
            'name' => $name,
            'slug' => Team::slugFromName($name),
            'description' => null,
            'competition' => null,
            'age_group' => null,
            'gender' => Gender::Male,
            'fpb_url' => null,
            'photo' => null,
            'order' => 0,
            'enabled' => true,
        ];
    }
}
