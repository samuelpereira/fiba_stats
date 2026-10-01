<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'team_id' => Team::factory(),
            'fpb_id' => null,
            'name' => $name,
            'slug' => Player::slugFromName($name),
            'date_of_birth' => null,
            'number' => null,
            'position' => null,
            'photo' => null,
            'order' => 0,
            'enabled' => true,
        ];
    }
}
