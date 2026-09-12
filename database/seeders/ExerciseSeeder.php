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
     *
     * Rows are [category, name, primary, secondary, difficulty, xp, equipment, type].
     * `type` is optional and defaults to the category's natural type; it only
     * needs stating when an exercise breaks from its category, such as a
     * bodyweight movement sitting in a barbell-heavy group.
     *
     * Note: cardio, mobility and stretching types are logged by duration rather
     * than reps (see CompleteWorkoutSetRequest), so isometric holds stay on a
     * rep-based type deliberately.
     */
    public function run(): void
    {
        $categories = ExerciseCategory::pluck('id', 'slug');

        foreach ($this->exercises() as $row) {
            [$category, $name, $primary, $secondary, $difficulty, $xp, $equipment] = $row;

            if (! isset($categories[$category])) {
                continue;
            }

            Exercise::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                [
                    'exercise_category_id' => $categories[$category],
                    'name' => $name,
                    'exercise_type' => $row[7] ?? $this->defaultType($category, $equipment),
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

    /**
     * @param  array<int, string>  $equipment
     */
    private function defaultType(string $category, array $equipment): string
    {
        return match ($category) {
            'cardio' => 'cardio',
            'mobility' => 'mobility',
            'plyometrics' => 'plyometric',
            default => $equipment === [] ? 'bodyweight' : 'strength',
        };
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: array<int, string>, 3: array<int, string>, 4: string, 5: int, 6: array<int, string>, 7?: string}>
     */
    private function exercises(): array
    {
        return [
            // ---- Chest (12) ----
            ['chest', 'Bench Press', ['chest'], ['shoulders', 'triceps'], 'intermediate', 22, ['barbell', 'bench']],
            ['chest', 'Incline Bench Press', ['chest'], ['shoulders', 'triceps'], 'intermediate', 22, ['barbell', 'bench']],
            ['chest', 'Decline Bench Press', ['chest'], ['triceps'], 'intermediate', 21, ['barbell', 'bench']],
            ['chest', 'Incline Dumbbell Press', ['chest'], ['shoulders', 'triceps'], 'intermediate', 20, ['dumbbells', 'bench']],
            ['chest', 'Machine Chest Press', ['chest'], ['shoulders', 'triceps'], 'beginner', 16, ['machine']],
            ['chest', 'Push-Up', ['chest'], ['shoulders', 'triceps', 'abs'], 'beginner', 12, []],
            ['chest', 'Incline Push-Up', ['chest'], ['shoulders', 'triceps'], 'beginner', 10, ['bench']],
            ['chest', 'Chest Fly', ['chest'], ['shoulders'], 'beginner', 14, ['dumbbells', 'bench']],
            ['chest', 'Cable Crossover', ['chest'], ['shoulders'], 'intermediate', 16, ['machine']],
            ['chest', 'Pec Deck', ['chest'], ['shoulders'], 'beginner', 14, ['machine']],
            ['chest', 'Dumbbell Pullover', ['chest'], ['lats', 'triceps'], 'intermediate', 17, ['dumbbells', 'bench']],
            ['chest', 'Dips', ['chest'], ['triceps', 'shoulders'], 'intermediate', 20, ['pull_up_bar']],

            // ---- Back (14) ----
            ['back', 'Pull-Up', ['lats'], ['biceps', 'upper_back', 'forearms'], 'advanced', 25, ['pull_up_bar']],
            ['back', 'Chin-Up', ['lats'], ['biceps', 'upper_back'], 'advanced', 24, ['pull_up_bar']],
            ['back', 'Inverted Row', ['upper_back'], ['lats', 'biceps'], 'beginner', 14, ['barbell']],
            ['back', 'Bent-Over Row', ['upper_back', 'lats'], ['biceps', 'lower_back'], 'intermediate', 22, ['barbell']],
            ['back', 'Pendlay Row', ['upper_back'], ['lats', 'biceps', 'lower_back'], 'advanced', 24, ['barbell']],
            ['back', 'T-Bar Row', ['upper_back', 'lats'], ['biceps'], 'intermediate', 22, ['barbell']],
            ['back', 'Single-Arm Dumbbell Row', ['lats'], ['upper_back', 'biceps'], 'beginner', 18, ['dumbbells', 'bench']],
            ['back', 'Lat Pulldown', ['lats'], ['biceps', 'upper_back'], 'beginner', 16, ['machine']],
            ['back', 'Straight-Arm Pulldown', ['lats'], ['triceps'], 'beginner', 14, ['machine']],
            ['back', 'Seated Cable Row', ['upper_back'], ['lats', 'biceps'], 'beginner', 16, ['machine']],
            ['back', 'Deadlift', ['lower_back', 'hamstrings'], ['glutes', 'upper_back', 'forearms'], 'advanced', 30, ['barbell']],
            ['back', 'Rack Pull', ['lower_back'], ['upper_back', 'glutes', 'forearms'], 'intermediate', 26, ['barbell']],
            ['back', 'Good Morning', ['lower_back'], ['hamstrings', 'glutes'], 'advanced', 22, ['barbell']],
            ['back', 'Superman Hold', ['lower_back'], ['glutes'], 'beginner', 10, []],

            // ---- Shoulders (11) ----
            ['shoulders', 'Overhead Press', ['shoulders'], ['triceps', 'abs'], 'intermediate', 20, ['barbell']],
            ['shoulders', 'Shoulder Press', ['shoulders'], ['triceps'], 'beginner', 18, ['dumbbells']],
            ['shoulders', 'Arnold Press', ['shoulders'], ['triceps'], 'intermediate', 19, ['dumbbells']],
            ['shoulders', 'Landmine Press', ['shoulders'], ['chest', 'triceps', 'abs'], 'intermediate', 19, ['barbell']],
            ['shoulders', 'Lateral Raise', ['shoulders'], [], 'beginner', 12, ['dumbbells']],
            ['shoulders', 'Front Raise', ['shoulders'], [], 'beginner', 12, ['dumbbells']],
            ['shoulders', 'Rear Delt Fly', ['shoulders', 'upper_back'], [], 'beginner', 13, ['dumbbells']],
            ['shoulders', 'Face Pull', ['shoulders', 'upper_back'], [], 'beginner', 12, ['resistance_bands']],
            ['shoulders', 'Upright Row', ['shoulders'], ['upper_back', 'biceps'], 'intermediate', 16, ['barbell']],
            ['shoulders', 'Pike Push-Up', ['shoulders'], ['triceps'], 'intermediate', 15, []],
            ['shoulders', 'Handstand Hold', ['shoulders'], ['triceps', 'abs'], 'advanced', 20, []],

            // ---- Arms (13) ----
            ['arms', 'Barbell Curl', ['biceps'], ['forearms'], 'beginner', 14, ['barbell']],
            ['arms', 'Dumbbell Curl', ['biceps'], ['forearms'], 'beginner', 13, ['dumbbells']],
            ['arms', 'Hammer Curl', ['biceps', 'forearms'], [], 'beginner', 14, ['dumbbells']],
            ['arms', 'Incline Dumbbell Curl', ['biceps'], [], 'intermediate', 15, ['dumbbells', 'bench']],
            ['arms', 'Preacher Curl', ['biceps'], ['forearms'], 'intermediate', 15, ['barbell', 'bench']],
            ['arms', 'Concentration Curl', ['biceps'], [], 'beginner', 13, ['dumbbells']],
            ['arms', 'Cable Curl', ['biceps'], ['forearms'], 'beginner', 13, ['machine']],
            ['arms', 'Reverse Curl', ['forearms', 'biceps'], [], 'beginner', 13, ['barbell']],
            ['arms', 'Wrist Curl', ['forearms'], [], 'beginner', 10, ['dumbbells']],
            ['arms', 'Triceps Pushdown', ['triceps'], [], 'beginner', 13, ['machine']],
            ['arms', 'Overhead Triceps Extension', ['triceps'], ['shoulders'], 'beginner', 14, ['dumbbells']],
            ['arms', 'Skull Crusher', ['triceps'], [], 'intermediate', 16, ['dumbbells', 'bench']],
            ['arms', 'Close-Grip Push-Up', ['triceps'], ['chest', 'shoulders'], 'beginner', 12, []],

            // ---- Legs (16) ----
            ['legs', 'Back Squat', ['quads'], ['glutes', 'hamstrings', 'lower_back'], 'intermediate', 26, ['barbell']],
            ['legs', 'Front Squat', ['quads'], ['glutes', 'abs'], 'advanced', 26, ['barbell']],
            ['legs', 'Goblet Squat', ['quads'], ['glutes', 'abs'], 'beginner', 16, ['dumbbells']],
            ['legs', 'Bodyweight Squat', ['quads'], ['glutes'], 'beginner', 10, []],
            ['legs', 'Bulgarian Split Squat', ['quads', 'glutes'], ['hamstrings'], 'intermediate', 22, ['dumbbells', 'bench']],
            ['legs', 'Cossack Squat', ['quads'], ['glutes', 'hamstrings'], 'intermediate', 18, []],
            ['legs', 'Leg Press', ['quads'], ['glutes', 'hamstrings'], 'beginner', 20, ['machine']],
            ['legs', 'Leg Extension', ['quads'], [], 'beginner', 14, ['machine']],
            ['legs', 'Romanian Deadlift', ['hamstrings'], ['glutes', 'lower_back'], 'intermediate', 24, ['barbell']],
            ['legs', 'Sumo Deadlift', ['glutes', 'hamstrings'], ['quads', 'lower_back'], 'advanced', 28, ['barbell']],
            ['legs', 'Lying Leg Curl', ['hamstrings'], ['calves'], 'beginner', 14, ['machine']],
            ['legs', 'Nordic Hamstring Curl', ['hamstrings'], ['glutes'], 'advanced', 22, []],
            ['legs', 'Hip Thrust', ['glutes'], ['hamstrings'], 'intermediate', 22, ['barbell', 'bench']],
            ['legs', 'Glute Bridge', ['glutes'], ['hamstrings', 'lower_back'], 'beginner', 12, []],
            ['legs', 'Walking Lunge', ['quads', 'glutes'], ['hamstrings', 'calves'], 'beginner', 18, ['dumbbells']],
            ['legs', 'Step-Up', ['quads', 'glutes'], ['calves'], 'beginner', 16, ['dumbbells', 'bench']],

            // ---- Core (12) ----
            ['core', 'Plank', ['abs'], ['obliques', 'lower_back'], 'beginner', 12, []],
            ['core', 'Side Plank', ['obliques'], ['abs', 'shoulders'], 'beginner', 12, []],
            ['core', 'Hollow Body Hold', ['abs'], ['obliques'], 'intermediate', 15, []],
            ['core', 'Dead Bug', ['abs'], ['obliques'], 'beginner', 10, []],
            ['core', 'Bicycle Crunch', ['obliques'], ['abs'], 'beginner', 11, []],
            ['core', 'Toe Touch Crunch', ['abs'], [], 'beginner', 10, []],
            ['core', 'Russian Twist', ['obliques'], ['abs'], 'beginner', 12, []],
            ['core', 'Hanging Leg Raise', ['abs'], ['obliques', 'forearms'], 'advanced', 20, ['pull_up_bar']],
            ['core', 'Ab Wheel Rollout', ['abs'], ['lats', 'lower_back'], 'advanced', 20, ['ab_wheel']],
            ['core', 'Cable Woodchop', ['obliques'], ['abs', 'shoulders'], 'intermediate', 16, ['machine']],
            ['core', 'Pallof Press', ['obliques'], ['abs'], 'intermediate', 15, ['resistance_bands']],
            ['core', 'Mountain Climber', ['abs'], ['quads', 'shoulders'], 'beginner', 14, []],

            // ---- Cardio (10) ----
            ['cardio', 'Steady-State Run', ['calves'], ['quads', 'hamstrings'], 'beginner', 18, []],
            ['cardio', 'Sprint Intervals', ['hamstrings'], ['quads', 'calves', 'glutes'], 'advanced', 26, []],
            ['cardio', 'Incline Treadmill Walk', ['calves'], ['glutes', 'hamstrings'], 'beginner', 14, ['machine']],
            ['cardio', 'Cycling', ['quads'], ['calves', 'glutes'], 'beginner', 16, ['machine']],
            ['cardio', 'Rowing Machine', ['upper_back'], ['lats', 'quads', 'biceps'], 'beginner', 18, ['machine']],
            ['cardio', 'Stair Climber', ['glutes'], ['quads', 'calves'], 'beginner', 17, ['machine']],
            ['cardio', 'Elliptical Trainer', ['quads'], ['glutes', 'calves'], 'beginner', 14, ['machine']],
            ['cardio', 'Swimming', ['lats'], ['shoulders', 'quads', 'abs'], 'intermediate', 22, []],
            ['cardio', 'Jump Rope', ['calves'], ['quads'], 'beginner', 14, ['jump_rope']],
            ['cardio', 'Battle Ropes', ['shoulders'], ['abs', 'forearms'], 'intermediate', 20, ['battle_ropes']],

            // ---- Plyometrics (6) ----
            ['plyometrics', 'Burpee', ['quads'], ['chest', 'shoulders', 'abs'], 'intermediate', 20, []],
            ['plyometrics', 'Box Jump', ['quads'], ['glutes', 'calves'], 'intermediate', 20, ['plyo_box']],
            ['plyometrics', 'Jump Squat', ['quads'], ['glutes', 'calves'], 'intermediate', 18, []],
            ['plyometrics', 'Broad Jump', ['quads', 'glutes'], ['hamstrings', 'calves'], 'intermediate', 19, []],
            ['plyometrics', 'Kettlebell Swing', ['glutes', 'hamstrings'], ['lower_back', 'abs'], 'intermediate', 22, ['kettlebell']],
            ['plyometrics', 'Clap Push-Up', ['chest'], ['triceps', 'shoulders'], 'advanced', 20, []],

            // ---- Mobility (6) ----
            ['mobility', 'Cat-Cow', ['lower_back'], ['abs'], 'beginner', 8, []],
            ['mobility', 'World’s Greatest Stretch', ['hamstrings'], ['glutes', 'lower_back'], 'beginner', 10, []],
            ['mobility', 'Hip Flexor Stretch', ['quads'], ['glutes'], 'beginner', 8, []],
            ['mobility', 'Thoracic Rotation', ['upper_back'], ['obliques'], 'beginner', 8, []],
            ['mobility', 'Hamstring Stretch', ['hamstrings'], ['calves'], 'beginner', 8, [], 'stretching'],
            ['mobility', 'Shoulder Dislocate', ['shoulders'], ['upper_back'], 'beginner', 8, ['resistance_bands']],

            // ---- Loaded carries / conditioning (2) ----
            ['arms', 'Farmer Carry', ['forearms'], ['abs', 'upper_back'], 'beginner', 15, ['dumbbells']],
            ['legs', 'Calf Raise', ['calves'], [], 'beginner', 10, []],

            // ---- Seated isolation (2) ----
            ['legs', 'Seated Calf Raise', ['calves'], [], 'beginner', 11, ['machine']],
            ['arms', 'Triceps Bench Dip', ['triceps'], ['chest', 'shoulders'], 'beginner', 13, ['bench']],

            // ---- Anti-rotation / stability (2) ----
            ['core', 'Bird Dog', ['lower_back'], ['abs', 'glutes'], 'beginner', 10, []],
            ['core', 'Suitcase Carry', ['obliques'], ['forearms', 'abs'], 'beginner', 14, ['dumbbells']],

            // ---- Posterior chain finishers (2) ----
            ['back', 'Reverse Hyperextension', ['lower_back'], ['glutes', 'hamstrings'], 'intermediate', 18, ['bench']],
            ['legs', 'Single-Leg Romanian Deadlift', ['hamstrings'], ['glutes', 'lower_back'], 'intermediate', 20, ['dumbbells']],
        ];
    }
}
