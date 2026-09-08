<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class LiveWorkoutTestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@fitness.local'],
            [
                'name' => 'Test Hunter',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        $profile = $user->hunterProfile()->create([
            'codename' => 'TestHunter',
            'rank' => 'E',
            'current_level' => 1,
            'current_xp' => 0,
            'awakened_at' => now(),
        ]);

        $profile->stats()->create([
            'strength' => 10,
            'endurance' => 10,
            'agility' => 10,
            'vitality' => 10,
            'willpower' => 10,
        ]);

        $category = ExerciseCategory::firstOrCreate(
            ['slug' => 'chest'],
            ['name' => 'Chest', 'muscle_group' => 'chest']
        );

        $exercise = Exercise::firstOrCreate(
            ['slug' => 'bench_press'],
            [
                'exercise_category_id' => $category->id,
                'name' => 'Bench Press',
                'exercise_type' => 'strength',
                'difficulty' => 'beginner',
                'xp_base_value' => 20,
            ]
        );

        $workout = $user->workouts()->create([
            'name' => 'Chest Day',
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $workoutExercise = $workout->workoutExercises()->create([
            'exercise_id' => $exercise->id,
            'order' => 1,
        ]);

        $workoutExercise->workoutSets()->create(['set_number' => 1]);
        $workoutExercise->workoutSets()->create(['set_number' => 2]);
        $workoutExercise->workoutSets()->create(['set_number' => 3]);

        echo "\n✅ Test user created!\n";
        echo "Email: test@fitness.local\n";
        echo "Password: password\n";
        echo "Workout ID: {$workout->id}\n";
        echo "Live Mode URL: http://127.0.0.1:8000/workouts/{$workout->id}/live\n\n";
    }
}
