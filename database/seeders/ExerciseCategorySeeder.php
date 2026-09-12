<?php

namespace Database\Seeders;

use App\Models\ExerciseCategory;
use Illuminate\Database\Seeder;

class ExerciseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Chest', 'slug' => 'chest', 'muscle_group' => 'chest'],
            ['name' => 'Back', 'slug' => 'back', 'muscle_group' => 'upper_back'],
            ['name' => 'Shoulders', 'slug' => 'shoulders', 'muscle_group' => 'shoulders'],
            ['name' => 'Arms', 'slug' => 'arms', 'muscle_group' => 'biceps'],
            ['name' => 'Legs', 'slug' => 'legs', 'muscle_group' => 'quads'],
            ['name' => 'Core', 'slug' => 'core', 'muscle_group' => 'abs'],
            ['name' => 'Cardio', 'slug' => 'cardio', 'muscle_group' => 'calves'],
            ['name' => 'Plyometrics', 'slug' => 'plyometrics', 'muscle_group' => 'quads'],
            ['name' => 'Mobility', 'slug' => 'mobility', 'muscle_group' => 'lower_back'],
        ];

        foreach ($categories as $category) {
            ExerciseCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
