<?php

namespace Database\Factories;

use App\Models\FoodLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FoodLog>
 */
class FoodLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Chicken and rice', 'Protein shake', 'Oats and berries', 'Salmon salad']),
            'calories' => fake()->numberBetween(150, 800),
            'protein_g' => fake()->numberBetween(10, 60),
            'carbs_g' => fake()->numberBetween(10, 90),
            'fat_g' => fake()->numberBetween(3, 35),
            'logged_at' => now(),
        ];
    }
}
