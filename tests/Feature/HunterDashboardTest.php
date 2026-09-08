<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HunterDashboardTest extends TestCase
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

    public function test_dashboard_renders_hunter_identity(): void
    {
        $user = $this->awakenedUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('hunter.name', 'Nightbreaker')
            ->where('hunter.rank', 'E')
            ->where('hunter.level', 1)
            ->has('hunter.currentXp')
            ->has('hunter.requiredXp')
            ->has('health')
            ->has('todayWorkout')
            ->has('targetMuscles')
            ->has('weeklyProgress')
        );
    }

    public function test_user_without_a_profile_is_sent_to_onboarding(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('onboarding.show'));
    }

    public function test_bmi_is_derived_from_the_latest_measurement(): void
    {
        $user = $this->awakenedUser();

        // Older measurement should be ignored in favour of the newest one.
        $user->healthMeasurements()->create([
            'measured_at' => now()->subMonth(),
            'height_cm' => 180,
            'weight_kg' => 110,
        ]);

        $user->healthMeasurements()->create([
            'measured_at' => now(),
            'height_cm' => 180,
            'weight_kg' => 75,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        // 75 / 1.8^2 = 23.1
        $response->assertInertia(fn ($page) => $page
            ->where('health.bmi', 23.1)
            ->where('health.bmiCategory', 'Normal')
        );
    }

    public function test_bmi_is_null_when_no_measurement_exists(): void
    {
        $user = $this->awakenedUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('health.bmi', null)
            ->where('health.bmiCategory', null)
        );
    }

    public function test_target_muscles_are_derived_from_todays_workout(): void
    {
        $user = $this->awakenedUser();

        $category = ExerciseCategory::create([
            'name' => 'Chest',
            'slug' => 'chest',
            'muscle_group' => 'chest',
        ]);

        $exercise = Exercise::create([
            'exercise_category_id' => $category->id,
            'name' => 'Bench Press',
            'slug' => 'bench-press',
            'exercise_type' => 'strength',
            'difficulty' => 'beginner',
            'primary_muscle' => ['Chest'],
            'secondary_muscles' => ['Shoulders', 'Triceps'],
            'xp_base_value' => 20,
        ]);

        $workout = $user->workouts()->create([
            'name' => 'Push Day',
            'scheduled_date' => today(),
            'status' => 'Scheduled',
        ]);

        $workoutExercise = $workout->workoutExercises()->create([
            'exercise_id' => $exercise->id,
            'order' => 1,
        ]);

        $workoutExercise->workoutSets()->create(['set_number' => 1]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('todayWorkout.name', 'Push Day')
            ->where('targetMuscles.focusArea', 'Chest')
            ->where('targetMuscles.primary', ['chest'])
            ->where('targetMuscles.secondary', ['shoulders', 'triceps'])
        );
    }

    public function test_rest_day_is_shown_when_nothing_is_scheduled(): void
    {
        $user = $this->awakenedUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('todayWorkout.name', 'Rest Day')
            ->where('todayWorkout.exercises', [])
            ->where('targetMuscles.primary', [])
        );
    }
}
