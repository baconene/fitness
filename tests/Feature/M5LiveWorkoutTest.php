<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\User;
use App\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M5LiveWorkoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_live_workout_page()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create([
            'rank' => HunterRank::ERank,
        ]);
        $profile->stats()->create();

        $workoutService = app(WorkoutService::class);
        $workout = $workoutService->startWorkout($user);

        $category = ExerciseCategory::create([
            'name' => 'Chest',
            'slug' => 'chest',
            'muscle_group' => 'chest',
        ]);

        $exercise = Exercise::create([
            'exercise_category_id' => $category->id,
            'name' => 'Bench Press',
            'slug' => 'bench_press',
            'exercise_type' => 'strength',
            'difficulty' => 'beginner',
            'xp_base_value' => 20,
        ]);

        $workoutExercise = $workout->workoutExercises()->create([
            'exercise_id' => $exercise->id,
            'order' => 1,
        ]);

        $workoutExercise->workoutSets()->create([
            'set_number' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('workouts.live.show', $workout));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Workouts/Live')
            ->has('workout')
            ->has('currentExerciseIndex')
        );
    }

    public function test_complete_set_from_live_workout()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create([
            'rank' => HunterRank::ERank,
        ]);
        $profile->stats()->create();

        $workoutService = app(WorkoutService::class);
        $workout = $workoutService->startWorkout($user);

        $category = ExerciseCategory::create([
            'name' => 'Chest',
            'slug' => 'chest',
            'muscle_group' => 'chest',
        ]);

        $exercise = Exercise::create([
            'exercise_category_id' => $category->id,
            'name' => 'Bench Press',
            'slug' => 'bench_press',
            'exercise_type' => 'strength',
            'difficulty' => 'beginner',
            'xp_base_value' => 20,
        ]);

        $workoutExercise = $workout->workoutExercises()->create([
            'exercise_id' => $exercise->id,
            'order' => 1,
        ]);

        $set = $workoutExercise->workoutSets()->create([
            'set_number' => 1,
        ]);

        $response = $this->actingAs($user)->postJson(
            route('workouts.sets.complete', ['workout' => $workout, 'set' => $set]),
            [
                'reps' => 10,
                'weight' => 80,
                'rpe' => 7,
                'idempotency_key' => fake()->uuid(),
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'set',
            'xpAwarded',
            'hunterProfile',
        ]);

        $this->assertDatabaseHas('workout_sets', [
            'id' => $set->id,
            'is_completed' => true,
            'reps_completed' => 10,
            'weight_kg' => 80,
        ]);
    }

    public function test_unauthorized_user_cannot_view_others_workout()
    {
        $owner = User::factory()->create();
        $ownerProfile = $owner->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $ownerProfile->stats()->create();

        $other = User::factory()->create();

        $workoutService = app(WorkoutService::class);
        $workout = $workoutService->startWorkout($owner);

        $response = $this->actingAs($other)->get(route('workouts.live.show', $workout));

        $response->assertStatus(403);
    }
}
