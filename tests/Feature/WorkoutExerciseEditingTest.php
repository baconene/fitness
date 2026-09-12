<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\User;
use App\Models\Workout;
use App\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutExerciseEditingTest extends TestCase
{
    use RefreshDatabase;

    private function awakenedUser(): User
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'awakened_at' => now()]);
        $profile->stats()->create();

        return $user;
    }

    private function exercise(string $name): Exercise
    {
        $category = ExerciseCategory::firstOrCreate(
            ['slug' => 'chest'],
            ['name' => 'Chest', 'muscle_group' => 'chest'],
        );

        return Exercise::create([
            'exercise_category_id' => $category->id,
            'name' => $name,
            'slug' => str($name)->slug('_')->value(),
            'exercise_type' => 'strength',
            'difficulty' => 'beginner',
            'xp_base_value' => 20,
            'is_active' => true,
        ]);
    }

    private function activeWorkout(User $user, Exercise $exercise, int $sets = 2): Workout
    {
        $workout = app(WorkoutService::class)->startWorkout($user);

        $workoutExercise = $workout->workoutExercises()->create(['exercise_id' => $exercise->id, 'order' => 1]);

        for ($number = 1; $number <= $sets; $number++) {
            $workoutExercise->workoutSets()->create(['set_number' => $number]);
        }

        return $workout->refresh();
    }

    public function test_an_exercise_can_be_added_while_the_mission_is_active(): void
    {
        $user = $this->awakenedUser();
        $workout = $this->activeWorkout($user, $this->exercise('Bench Press'));
        $extra = $this->exercise('Barbell Row');

        $this->assertSame('in_progress', $workout->status);

        $this->actingAs($user)
            ->post(route('workouts.exercises.add', $workout), ['exercise_id' => $extra->id, 'sets' => 3])
            ->assertRedirect();

        $added = $workout->workoutExercises()->where('exercise_id', $extra->id)->firstOrFail();

        $this->assertSame(2, $added->order);
        $this->assertCount(3, $added->workoutSets);
        $this->assertSame([1, 2, 3], $added->workoutSets->pluck('set_number')->all());
    }

    public function test_adding_an_exercise_keeps_already_logged_sets(): void
    {
        $user = $this->awakenedUser();
        $first = $this->exercise('Bench Press');
        $workout = $this->activeWorkout($user, $first);
        $set = $workout->workoutExercises()->first()->workoutSets()->first();

        $this->actingAs($user)->postJson(
            route('workouts.sets.complete', ['workout' => $workout, 'set' => $set]),
            ['reps' => 10, 'weight' => 60, 'rpe' => 7, 'idempotency_key' => fake()->uuid()],
        )->assertStatus(200);

        $this->actingAs($user)
            ->post(route('workouts.exercises.add', $workout), ['exercise_id' => $this->exercise('Barbell Row')->id, 'sets' => 2])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('workout_sets', ['id' => $set->id, 'is_completed' => true, 'reps_completed' => 10]);
        $this->assertSame(2, $workout->workoutExercises()->count());
    }

    public function test_the_same_exercise_cannot_be_added_twice(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise('Bench Press');
        $workout = $this->activeWorkout($user, $exercise);

        $this->actingAs($user)
            ->post(route('workouts.exercises.add', $workout), ['exercise_id' => $exercise->id, 'sets' => 3])
            ->assertSessionHasErrors('exercise_id');

        $this->assertSame(1, $workout->workoutExercises()->count());
    }

    public function test_an_untrained_exercise_can_be_removed_and_order_stays_contiguous(): void
    {
        $user = $this->awakenedUser();
        $workout = $this->activeWorkout($user, $this->exercise('Bench Press'));

        foreach (['Barbell Row', 'Overhead Press'] as $name) {
            $this->actingAs($user)->post(
                route('workouts.exercises.add', $workout),
                ['exercise_id' => $this->exercise($name)->id, 'sets' => 2],
            );
        }

        $middle = $workout->workoutExercises()->where('order', 2)->firstOrFail();

        $this->actingAs($user)
            ->delete(route('workouts.exercises.remove', ['workout' => $workout, 'workoutExercise' => $middle]))
            ->assertRedirect();

        $this->assertDatabaseMissing('workout_exercises', ['id' => $middle->id]);
        $this->assertDatabaseMissing('workout_sets', ['workout_exercise_id' => $middle->id]);
        $this->assertSame([1, 2], $workout->workoutExercises()->orderBy('order')->pluck('order')->all());
    }

    public function test_an_exercise_with_logged_sets_cannot_be_removed(): void
    {
        $user = $this->awakenedUser();
        $workout = $this->activeWorkout($user, $this->exercise('Bench Press'));

        $this->actingAs($user)->post(
            route('workouts.exercises.add', $workout),
            ['exercise_id' => $this->exercise('Barbell Row')->id, 'sets' => 2],
        );

        $trained = $workout->workoutExercises()->where('order', 1)->firstOrFail();

        $this->actingAs($user)->postJson(
            route('workouts.sets.complete', ['workout' => $workout, 'set' => $trained->workoutSets()->first()]),
            ['reps' => 8, 'weight' => 50, 'rpe' => 6, 'idempotency_key' => fake()->uuid()],
        )->assertStatus(200);

        $this->actingAs($user)
            ->delete(route('workouts.exercises.remove', ['workout' => $workout, 'workoutExercise' => $trained]))
            ->assertSessionHasErrors('exercise');

        $this->assertDatabaseHas('workout_exercises', ['id' => $trained->id]);
    }

    public function test_the_last_exercise_cannot_be_removed(): void
    {
        $user = $this->awakenedUser();
        $workout = $this->activeWorkout($user, $this->exercise('Bench Press'));
        $only = $workout->workoutExercises()->firstOrFail();

        $this->actingAs($user)
            ->delete(route('workouts.exercises.remove', ['workout' => $workout, 'workoutExercise' => $only]))
            ->assertSessionHasErrors('exercise');

        $this->assertDatabaseHas('workout_exercises', ['id' => $only->id]);
    }

    public function test_a_completed_mission_can_no_longer_be_changed(): void
    {
        $user = $this->awakenedUser();
        $workout = $this->activeWorkout($user, $this->exercise('Bench Press'), 1);

        $this->actingAs($user)->postJson(
            route('workouts.sets.complete', [
                'workout' => $workout,
                'set' => $workout->workoutExercises()->first()->workoutSets()->first(),
            ]),
            ['reps' => 10, 'weight' => 60, 'rpe' => 7, 'idempotency_key' => fake()->uuid()],
        )->assertStatus(200);

        $this->actingAs($user)->post(route('workouts.complete', $workout))->assertRedirect();
        $this->assertSame('completed', $workout->refresh()->status);

        $this->actingAs($user)
            ->post(route('workouts.exercises.add', $workout), ['exercise_id' => $this->exercise('Barbell Row')->id, 'sets' => 2])
            ->assertSessionHasErrors('workout');
    }

    public function test_a_hunter_cannot_change_someone_elses_mission(): void
    {
        $owner = $this->awakenedUser();
        $intruder = $this->awakenedUser();
        $workout = $this->activeWorkout($owner, $this->exercise('Bench Press'));

        $this->actingAs($intruder)
            ->post(route('workouts.exercises.add', $workout), ['exercise_id' => $this->exercise('Barbell Row')->id, 'sets' => 2])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('workouts.exercises.remove', [
                'workout' => $workout,
                'workoutExercise' => $workout->workoutExercises()->first(),
            ]))
            ->assertForbidden();
    }

    public function test_the_live_page_sends_the_exercise_catalogue_for_the_add_picker(): void
    {
        $user = $this->awakenedUser();
        $workout = $this->activeWorkout($user, $this->exercise('Bench Press'));
        $this->exercise('Barbell Row');

        $this->actingAs($user)
            ->get(route('workouts.live.show', $workout))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Workouts/Live')->has('exerciseOptions', 2));
    }
}
