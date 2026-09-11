<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private function awakenedUser(): User
    {
        $user = User::factory()->create();

        $profile = $user->hunterProfile()->create([
            'codename' => 'Nightbreaker',
            'rank' => HunterRank::ERank,
            'current_level' => 1,
            'current_xp' => 0,
            'total_xp_earned' => 0,
            'awakened_at' => now(),
        ]);

        $profile->stats()->create();

        return $user;
    }

    private function exercise(string $name = 'Bench Press'): Exercise
    {
        $category = ExerciseCategory::firstOrCreate(
            ['slug' => 'chest'],
            ['name' => 'Chest', 'muscle_group' => 'chest']
        );

        return Exercise::create([
            'exercise_category_id' => $category->id,
            'name' => $name,
            'slug' => str($name)->slug()->value(),
            'exercise_type' => 'strength',
            'difficulty' => 'beginner',
            'primary_muscle' => ['chest'],
            'xp_base_value' => 20,
            'is_active' => true,
        ]);
    }

    public function test_creating_a_mission_without_a_date_starts_it_immediately(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $response = $this->actingAs($user)->post(route('workouts.store'), [
            'name' => 'Push Day',
            'exercises' => [['exercise_id' => $exercise->id, 'sets' => 3]],
        ]);

        $workout = $user->workouts()->firstOrFail();

        $response->assertRedirect(route('workouts.live.show', $workout));
        $this->assertSame('in_progress', $workout->status);
        $this->assertNotNull($workout->started_at);
        $this->assertSame(3, $workout->workoutSets()->count());
    }

    public function test_a_scheduled_mission_is_planned_and_can_be_started_later(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $this->actingAs($user)->post(route('workouts.store'), [
            'name' => 'Leg Day',
            'scheduled_date' => now()->addDay()->toDateString(),
            'exercises' => [['exercise_id' => $exercise->id, 'sets' => 2]],
        ])->assertRedirect(route('workouts.index'));

        $workout = $user->workouts()->firstOrFail();
        $this->assertSame('planned', $workout->status);

        // A planned mission cannot be entered directly; it has to be started.
        $this->actingAs($user)
            ->get(route('workouts.live.show', $workout))
            ->assertRedirect(route('workouts.index'));

        $this->actingAs($user)
            ->post(route('workouts.start', $workout))
            ->assertRedirect(route('workouts.live.show', $workout));

        $this->assertSame('in_progress', $workout->fresh()->status);
    }

    public function test_enrolling_in_a_program_schedules_its_training_days(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $program = TrainingProgram::create([
            'name' => 'Foundation',
            'slug' => 'foundation',
            'difficulty' => 'beginner',
            'focus' => 'general_fitness',
            'duration_weeks' => 1,
            'is_system_program' => true,
        ]);

        $week = $program->programWeeks()->create(['week_number' => 1]);
        $day = $week->programDays()->create(['day_number' => 1, 'name' => 'Full Body A']);
        $day->programExercises()->create(['exercise_id' => $exercise->id, 'order' => 1, 'target_sets' => 3]);
        $week->programDays()->create(['day_number' => 2, 'name' => 'Rest', 'is_rest_day' => true]);

        $this->actingAs($user)->post(route('programs.enroll', $program), [
            'start_date' => now()->toDateString(),
        ])->assertRedirect(route('workouts.index'));

        $this->assertDatabaseHas('user_program_enrollments', [
            'user_id' => $user->id,
            'training_program_id' => $program->id,
        ]);

        // Only the training day is scheduled, never the rest day.
        $this->assertSame(1, $user->workouts()->count());
        $this->assertSame('planned', $user->workouts()->firstOrFail()->status);
    }

    public function test_enrolling_requires_a_start_date(): void
    {
        $user = $this->awakenedUser();

        $program = TrainingProgram::create([
            'name' => 'Foundation',
            'slug' => 'foundation',
            'difficulty' => 'beginner',
            'focus' => 'general_fitness',
            'duration_weeks' => 1,
            'is_system_program' => true,
        ]);

        $this->actingAs($user)
            ->post(route('programs.enroll', $program), [])
            ->assertSessionHasErrors('start_date');

        $this->assertDatabaseCount('user_program_enrollments', 0);
    }
}
