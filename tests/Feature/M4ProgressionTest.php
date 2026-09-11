<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\ExperienceTransaction;
use App\Models\User;
use App\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M4ProgressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_progression_loop()
    {
        // Setup
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create([
            'rank' => HunterRank::ERank,
            'current_level' => 1,
            'current_xp' => 0,
        ]);

        $profile->stats()->create([
            'strength' => 10,
            'endurance' => 10,
            'agility' => 10,
            'vitality' => 10,
            'willpower' => 10,
        ]);

        // Create exercise
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

        // Create workout
        $workoutService = app(WorkoutService::class);
        $workout = $workoutService->startWorkout($user);

        $workoutExercise = $workout->workoutExercises()->create([
            'exercise_id' => $exercise->id,
            'order' => 1,
        ]);

        $set = $workoutExercise->workoutSets()->create([
            'set_number' => 1,
        ]);

        // Complete the set (should award XP and increase strength)
        $completedSet = $workoutService->completeSet($set, [
            'reps' => 8,
            'weight' => 80,
        ], 'test-idempotency-key');

        $this->assertTrue($completedSet->is_completed);
        $this->assertEquals(80, $completedSet->weight_kg);
        $this->assertGreaterThan(0, $completedSet->xp_awarded);

        // Verify XP was awarded
        $profile->refresh();
        $this->assertGreaterThan(0, $profile->current_xp);

        // Verify XP transaction exists
        $xpTransaction = ExperienceTransaction::where('hunter_profile_id', $profile->id)->first();
        $this->assertNotNull($xpTransaction);
        $this->assertGreaterThan(0, $xpTransaction->amount);
    }

    public function test_duplicate_set_completion_is_idempotent()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create([
            'rank' => HunterRank::ERank,
        ]);
        $profile->stats()->create();

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

        $workout = $user->workouts()->create([
            'name' => 'Test',
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $workoutExercise = $workout->workoutExercises()->create([
            'exercise_id' => $exercise->id,
            'order' => 1,
        ]);

        $set = $workoutExercise->workoutSets()->create([
            'set_number' => 1,
        ]);

        $workoutService = app(WorkoutService::class);
        $key = 'test-duplicate-key';

        // Complete once
        $workoutService->completeSet($set, ['reps' => 8, 'weight' => 80], $key);
        $xpAfterFirst = $profile->fresh()->current_xp;

        // Complete again with same key (should be idempotent)
        $workoutService->completeSet($set->fresh(), ['reps' => 8, 'weight' => 80], $key);
        $xpAfterSecond = $profile->fresh()->current_xp;

        // XP should be the same (no double-award)
        $this->assertEquals($xpAfterFirst, $xpAfterSecond);

        // The XP key is derived from the set, not the client-supplied key, so a
        // client cannot force a second award by varying its own key.
        $this->assertEquals(
            1,
            ExperienceTransaction::where('idempotency_key', "workout-set:{$set->id}:xp")->count()
        );
    }
}
