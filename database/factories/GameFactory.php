<?php

namespace Database\Factories;

use App\Enums\GameStatus;
use App\Enums\HomeAway;
use App\Models\Game;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'fpb_id' => null,
            'opponent' => fake()->unique()->company(),
            'date_time' => now()->addDay(),
            'place' => null,
            'home_away' => HomeAway::Home,
            'competition' => null,
            'journey' => null,
            'home_points' => null,
            'away_points' => null,
            ...Game::defaultAttributes(),
            'status' => GameStatus::Scheduled,
        ];
    }
}
