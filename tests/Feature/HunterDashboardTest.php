<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Enums\WorkoutStatus;
use App\Models\Achievement;
use App\Models\Dungeon;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\QuestTemplate;
use App\Models\Title;
use App\Models\User;
use App\Services\DungeonService;
use App\Services\TitleService;
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

    public function test_attributes_and_next_rank_are_exposed(): void
    {
        $user = $this->awakenedUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('stats.attributes', 5)
            ->where('stats.attributes.0.key', 'strength')
            ->where('stats.attributes.0.value', 10)
            // An E-Rank hunter is promoted to D-Rank at level 10.
            ->where('stats.nextRank', 'D')
            ->where('stats.nextRankLevel', 10)
        );
    }

    public function test_daily_quests_are_generated_on_visit(): void
    {
        $user = $this->awakenedUser();

        QuestTemplate::create([
            'name' => 'Drink 3L of water',
            'slug' => 'drink-3l-of-water',
            'quest_type' => 'Daily',
            'category' => 'Recovery',
            'difficulty' => 'Easy',
            'target_metric' => 'WaterLitres',
            'target_value' => 3,
            'xp_reward_base' => 25,
            'is_active' => true,
        ]);

        $this->assertDatabaseCount('user_quests', 0);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('dailyQuests', 1)
            ->where('dailyQuests.0.name', 'Drink 3L of water')
            ->where('dailyQuests.0.target', 3)
        );

        $this->assertDatabaseHas('user_quests', [
            'user_id' => $user->id,
            'status' => 'Active',
        ]);
    }

    public function test_revisiting_does_not_duplicate_daily_quests(): void
    {
        $user = $this->awakenedUser();

        QuestTemplate::create([
            'name' => 'Reach 8,000 steps',
            'slug' => 'reach-8000-steps',
            'quest_type' => 'Daily',
            'category' => 'Conditioning',
            'difficulty' => 'Medium',
            'target_metric' => 'Steps',
            'target_value' => 8000,
            'xp_reward_base' => 40,
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('dashboard'));
        $this->actingAs($user)->get(route('dashboard'));

        $this->assertDatabaseCount('user_quests', 1);
    }

    public function test_achievements_unlock_when_criteria_are_met(): void
    {
        $user = $this->awakenedUser();

        Achievement::create([
            'name' => 'Awakened',
            'slug' => 'awakened',
            'description' => 'Reach level 1.',
            'category' => 'Progression',
            'criteria_type' => 'LevelReached',
            'criteria_value' => 1,
            'xp_reward' => 60,
            'is_hidden' => false,
            'is_active' => true,
        ]);

        Achievement::create([
            'name' => 'Elite Hunter',
            'slug' => 'elite-hunter',
            'description' => 'Reach level 35.',
            'category' => 'Progression',
            'criteria_type' => 'LevelReached',
            'criteria_value' => 35,
            'xp_reward' => 1000,
            'is_hidden' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('achievements.unlockedCount', 1)
            ->where('achievements.totalCount', 2)
            ->where('achievements.recent.0.name', 'Awakened')
            // The remaining locked achievement is surfaced as the next target.
            ->where('achievements.next.name', 'Elite Hunter')
            ->where('achievements.next.current', 1)
            ->where('achievements.next.target', 35)
        );
    }

    public function test_weekly_progress_counts_completed_workouts(): void
    {
        $user = $this->awakenedUser();

        $user->trainingPreference()->create([
            'experience_level' => 'beginner',
            'days_per_week' => 4,
        ]);

        // Status casing must match the WorkoutStatus enum's lowercase values.
        $user->workouts()->create([
            'name' => 'Push Day',
            'scheduled_date' => today(),
            'status' => WorkoutStatus::Completed->value,
            'started_at' => now()->subHour(),
            'completed_at' => now()->subMinutes(15),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('weeklyProgress.workoutsCompleted', 1)
            ->where('weeklyProgress.workoutsTarget', 4)
            ->where('weeklyProgress.completionPercent', 25)
            ->where('weeklyProgress.trainingTime', '45m')
        );
    }

    public function test_active_title_is_surfaced_on_the_hunter(): void
    {
        $user = $this->awakenedUser();

        $title = Title::create([
            'name' => 'The Awakened',
            'slug' => 'the-awakened',
            'rarity' => 'common',
            'is_active' => true,
        ]);

        app(TitleService::class)->activateTitle($user, $title);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('hunter.title', 'The Awakened')
            ->where('systems.titlesUnlocked', 1)
        );
    }

    public function test_active_dungeon_run_is_surfaced(): void
    {
        $user = $this->awakenedUser();

        $dungeon = Dungeon::create([
            'name' => 'Hollow Vault',
            'slug' => 'hollow-vault',
            'floor_count' => 5,
            'difficulty' => 'normal',
            'rank_requirement' => 'E',
            'entry_fee' => 0,
            'is_active' => true,
        ]);

        app(DungeonService::class)->startRun($user, $dungeon);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('systems.activeDungeon.name', 'Hollow Vault')
            ->where('systems.activeDungeon.floor', 1)
            ->where('systems.activeDungeon.floorCount', 5)
        );
    }
}
