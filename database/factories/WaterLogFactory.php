<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WaterLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaterLog>
 */
class WaterLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount_ml' => fake()->randomElement([250, 330, 500, 750]),
            'logged_at' => now(),
        ];
    }

    /** Logged on a specific day, at a plausible hour. */
    public function on(string $date): static
    {
        return $this->state(fn (): array => [
            'logged_at' => "{$date} ".fake()->numberBetween(7, 21).':00:00',
        ]);
    }
}
