<?php

namespace Database\Factories;

use App\Models\StepLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StepLog>
 */
class StepLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'counted_on' => now()->toDateString(),
            'steps' => fake()->numberBetween(2000, 14000),
        ];
    }
}
