<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DuplicateExerciseMergeTest extends TestCase
{
    use RefreshDatabase;

    private function category(): ExerciseCategory
    {
        return ExerciseCategory::firstOrCreate(
            ['slug' => 'chest'],
            ['name' => 'Chest', 'muscle_group' => 'chest'],
        );
    }

    private function exercise(string $slug): Exercise
    {
        return Exercise::create([
            'exercise_category_id' => $this->category()->id,
            'name' => 'Bench Press',
            'slug' => $slug,
            'exercise_type' => 'strength',
            'difficulty' => 'intermediate',
            'primary_muscle' => ['chest'],
            'xp_base_value' => 22,
            'is_active' => true,
        ]);
    }

    /** Re-runs the merge migration against the current database. */
    private function runMerge(): void
    {
        $path = 'database/migrations/2026_09_16_070019_merge_duplicate_exercise_slugs.php';

        (require base_path($path))->up();
    }

    public function test_an_underscore_duplicate_is_merged_into_the_hyphenated_row(): void
    {
        $legacy = $this->exercise('bench_press');
        $canonical = $this->exercise('bench-press');

        $this->runMerge();

        $this->assertDatabaseMissing('exercises', ['id' => $legacy->id]);
        $this->assertDatabaseHas('exercises', ['id' => $canonical->id, 'slug' => 'bench-press']);
        $this->assertSame(1, Exercise::where('name', 'Bench Press')->count());
    }

    public function test_references_follow_the_merge_rather_than_being_orphaned(): void
    {
        $legacy = $this->exercise('bench_press');
        $canonical = $this->exercise('bench-press');

        $user = User::factory()->create();
        $workout = $user->workouts()->create([
            'name' => 'Push Day',
            'scheduled_date' => now()->toDateString(),
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        $workoutExercise = $workout->workoutExercises()->create(['exercise_id' => $legacy->id, 'order' => 1]);

        $this->runMerge();

        $this->assertSame(
            $canonical->id,
            (int) DB::table('workout_exercises')->where('id', $workoutExercise->id)->value('exercise_id'),
            'The logged workout must keep an exercise, repointed at the surviving row.',
        );
    }

    public function test_the_merge_is_safe_to_run_twice(): void
    {
        $this->exercise('bench_press');
        $canonical = $this->exercise('bench-press');

        $this->runMerge();
        $this->runMerge();

        $this->assertSame(1, Exercise::count());
        $this->assertDatabaseHas('exercises', ['id' => $canonical->id]);
    }

    public function test_an_underscore_slug_with_no_counterpart_is_left_alone(): void
    {
        $lonely = $this->exercise('cable_fly');

        $this->runMerge();

        $this->assertDatabaseHas('exercises', ['id' => $lonely->id, 'slug' => 'cable_fly']);
    }
}
