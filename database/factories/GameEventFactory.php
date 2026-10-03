<?php

namespace Database\Factories;

use App\Enums\HomeAway;
use App\Enums\StatType;
use App\Models\Game;
use App\Models\GameEvent;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameEvent>
 */
class GameEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'player_id' => Player::factory(),
            'home_away' => HomeAway::Home,
            'type' => StatType::TwoPointMade,
            'quarter' => 1,
            'clock' => '10:00',
        ];
    }
}
