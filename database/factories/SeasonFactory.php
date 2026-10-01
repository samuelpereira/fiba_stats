<?php

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Season>
 */
class SeasonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->unique()->numberBetween(1990, 2090);

        return [
            'name' => $year.'/'.($year + 1),
            'slug' => $year.'-'.($year + 1),
            'starts_at' => $year.'-09-01',
            'ends_at' => ($year + 1).'-08-31',
            'is_current' => false,
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_current' => true,
        ]);
    }
}
