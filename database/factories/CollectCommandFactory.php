<?php

namespace Database\Factories;

use App\Models\CollectCommand;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectCommand>
 */
class CollectCommandFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'game_id' => Game::factory(),
        ];
    }
}
