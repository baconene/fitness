<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseCategory;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    /**
     * Muscle keys must match the dashboard body map:
     * chest, shoulders, biceps, triceps, forearms, abs, obliques,
     * upper_back, lats, lower_back, glutes, quads, hamstrings, calves.
     */
    public function run(): void
    {
        $categories = ExerciseCategory::pluck('id', 'slug');

        $exercises = [
            // Chest
            ['chest', 'Bench Press', ['chest'], ['shoulders', 'triceps'], 'intermediate', 22, ['barbell', 'bench']],
            ['chest', 'Incline Dumbbell Press', ['chest'], ['shoulders', 'triceps'], 'intermediate', 20, ['dumbbells', 'bench']],
            ['chest', 'Push-Up', ['chest'], ['shoulders', 'triceps', 'abs'], 'beginner', 12, []],
            ['chest', 'Chest Fly', ['chest'], ['shoulders'], 'beginner', 14, ['dumbbells', 'bench']],
            ['chest', 'Dips', ['chest'], ['triceps', 'shoulders'], 'intermediate', 20, ['pull_up_bar']],

            // Back
            ['back', 'Pull-Up', ['lats'], ['biceps', 'upper_back', 'forearms'], 'advanced', 25, ['pull_up_bar']],
            ['back', 'Bent-Over Row', ['upper_back', 'lats'], ['biceps', 'lower_back'], 'intermediate', 22, ['barbell']],
            ['back', 'Lat Pulldown', ['lats'], ['biceps', 'upper_back'], 'beginner', 16, ['machine']],
            ['back', 'Seated Cable Row', ['upper_back'], ['lats', 'biceps'], 'beginner', 16, ['machine']],
            ['back', 'Deadlift', ['lower_back', 'hamstrings'], ['glutes', 'upper_back', 'forearms'], 'advanced', 30, ['barbell']],
            ['back', 'Superman Hold', ['lower_back'], ['glutes'], 'beginner', 10, []],

            // Shoulders
            ['shoulders', 'Overhead Press', ['shoulders'], ['triceps', 'abs'], 'intermediate', 20, ['barbell']],
            ['shoulders', 'Shoulder Press', ['shoulders'], ['triceps'], 'beginner', 18, ['dumbbells']],
            ['shoulders', 'Lateral Raise', ['shoulders'], [], 'beginner', 12, ['dumbbells']],
            ['shoulders', 'Face Pull', ['shoulders', 'upper_back'], [], 'beginner', 12, ['resistance_bands']],
            ['shoulders', 'Pike Push-Up', ['shoulders'], ['triceps'], 'intermediate', 15, []],

            // Arms
            ['arms', 'Barbell Curl', ['biceps'], ['forearms'], 'beginner', 14, ['barbell']],
            ['arms', 'Hammer Curl', ['biceps', 'forearms'], [], 'beginner', 14, ['dumbbells']],
            ['arms', 'Triceps Pushdown', ['triceps'], [], 'beginner', 13, ['machine']],
            ['arms', 'Skull Crusher', ['triceps'], [], 'intermediate', 16, ['dumbbells', 'bench']],
            ['arms', 'Close-Grip Push-Up', ['triceps'], ['chest', 'shoulders'], 'beginner', 12, []],
            ['arms', 'Farmer Carry', ['forearms'], ['abs', 'upper_back'], 'beginner', 15, ['dumbbells']],

            // Legs
            ['legs', 'Back Squat', ['quads'], ['glutes', 'hamstrings', 'lower_back'], 'intermediate', 26, ['barbell']],
            ['legs', 'Front Squat', ['quads'], ['glutes', 'abs'], 'advanced', 26, ['barbell']],
            ['legs', 'Romanian Deadlift', ['hamstrings'], ['glutes', 'lower_back'], 'intermediate', 24, ['barbell']],
            ['legs', 'Walking Lunge', ['quads', 'glutes'], ['hamstrings', 'calves'], 'beginner', 18, ['dumbbells']],
            ['legs', 'Bodyweight Squat', ['quads'], ['glutes'], 'beginner', 10, []],
            ['legs', 'Glute Bridge', ['glutes'], ['hamstrings', 'lower_back'], 'beginner', 12, []],
            ['legs', 'Calf Raise', ['calves'], [], 'beginner', 10, []],

            // Core
            ['core', 'Plank', ['abs'], ['obliques', 'lower_back'], 'beginner', 12, []],
            ['core', 'Hanging Leg Raise', ['abs'], ['obliques', 'forearms'], 'advanced', 20, ['pull_up_bar']],
            ['core', 'Russian Twist', ['obliques'], ['abs'], 'beginner', 12, []],
            ['core', 'Dead Bug', ['abs'], ['obliques'], 'beginner', 10, []],
            ['core', 'Side Plank', ['obliques'], ['abs', 'shoulders'], 'beginner', 12, []],

            // Cardio
            ['cardio', 'Steady-State Run', ['calves'], ['quads', 'hamstrings'], 'beginner', 18, []],
            ['cardio', 'Jump Rope', ['calves'], ['quads'], 'beginner', 14, ['jump_rope']],
            ['cardio', 'Burpee', ['quads'], ['chest', 'shoulders', 'abs'], 'intermediate', 20, []],
            ['cardio', 'Rowing Machine', ['upper_back'], ['lats', 'quads', 'biceps'], 'beginner', 18, ['machine']],
        ];

        foreach ($exercises as [$category, $name, $primary, $secondary, $difficulty, $xp, $equipment]) {
            Exercise::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                [
                    'exercise_category_id' => $categories[$category],
                    'name' => $name,
                    'exercise_type' => $category === 'cardio' ? 'cardio' : 'strength',
                    'equipment_required' => $equipment,
                    'difficulty' => $difficulty,
                    'primary_muscle' => $primary,
                    'secondary_muscles' => $secondary,
                    'xp_base_value' => $xp,
                    'is_active' => true,
                ]
            );
        }
    }
}
