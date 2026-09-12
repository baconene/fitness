<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Enums\RecordType;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\HunterProfile;
use App\Models\PersonalRecord;
use App\Models\ProgramDay;
use App\Models\ProgramWeek;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LiveMissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_briefing_uses_owned_muscles_profile_and_server_rewards(): void
    {
        $user = $this->hunter();
        $user->trainingPreference()->create(['session_duration_minutes' => 45]);
        $workout = $this->mission($user, 'in_progress');
        $category = ExerciseCategory::factory()->create(['name' => 'Legs', 'slug' => 'legs', 'muscle_group' => 'legs']);
        $exercise = Exercise::factory()->create([
            'exercise_category_id' => $category->id, 'name' => 'Squat', 'slug' => 'squat',
            'exercise_type' => 'strength', 'difficulty' => 'beginner', 'xp_base_value' => 20,
            'primary_muscle' => ['quadriceps', 'glutes'], 'secondary_muscles' => ['glutes', 'hamstrings'],
        ]);
        $entry = WorkoutExercise::factory()->create(['workout_id' => $workout->id, 'exercise_id' => $exercise->id, 'order' => 1]);
        $recordSet = WorkoutSet::factory()->create(['workout_exercise_id' => $entry->id, 'set_number' => 1, 'is_completed' => true, 'xp_awarded' => 12]);
        PersonalRecord::factory()->create(['user_id' => $user->id, 'exercise_id' => $exercise->id, 'workout_set_id' => $recordSet->id, 'record_type' => RecordType::MaxWeight, 'value' => 80, 'unit' => 'kg', 'is_current' => true, 'achieved_at' => now()]);
        WorkoutSet::factory()->create(['workout_exercise_id' => $entry->id, 'set_number' => 2]);

        $this->actingAs($user)->get(route('workouts.live.show', $workout))
            ->assertInertia(fn (Assert $page) => $page->component('Workouts/Live')
                ->where('hunter.name', 'Nightbreaker')->where('hunter.level', 12)
                ->where('mission.status', 'ACTIVE')->where('mission.durationMinutes', 45)
                ->where('mission.completionPercent', 50)->where('missionRewards.xp', 32)
                ->where('missionRewards.earnedXp', 12)->where('targetMuscles.primary', ['quads', 'glutes'])
                ->where('targetMuscles.secondary', ['hamstrings'])->where('healthTargets.calorieTarget', null)
                ->where('healthTargets.stepsCurrent', null)->where('currentExerciseIndex', 0)
                ->where('workout.workout_exercises.0.workout_sets.0.is_pr', true)
                ->where('workout.workout_exercises.0.workout_sets.1.is_pr', false));
    }

    #[DataProvider('missionStates')]
    public function test_existing_workout_states_have_readable_briefings(string $status, string $expected): void
    {
        $user = $this->hunter();
        $workout = $this->mission($user, $status);

        $this->actingAs($user)->get(route('workouts.live.show', $workout))
            ->assertInertia(fn (Assert $page) => $page->component('Workouts/Live')->where('mission.status', $expected));
        $this->assertSame($status, $workout->fresh()->status);
    }

    public static function missionStates(): array
    {
        return ['ready' => ['planned', 'READY'], 'active' => ['in_progress', 'ACTIVE'], 'paused' => ['paused', 'PAUSED'], 'completed' => ['completed', 'COMPLETED'], 'missed' => ['skipped', 'MISSED']];
    }

    public function test_a_rest_day_has_recovery_objectives_and_no_training_reward(): void
    {
        $user = $this->hunter();
        $program = TrainingProgram::factory()->create(['name' => 'Recovery week', 'slug' => 'recovery-week', 'difficulty' => 'beginner', 'duration_weeks' => 1]);
        $week = ProgramWeek::factory()->create(['training_program_id' => $program->id, 'week_number' => 1]);
        $day = ProgramDay::factory()->create(['program_week_id' => $week->id, 'day_number' => 1, 'name' => 'Rest', 'is_rest_day' => true]);
        $workout = $this->mission($user, 'planned');
        $workout->update(['program_day_id' => $day->id]);

        $this->actingAs($user)->get(route('workouts.live.show', $workout))
            ->assertInertia(fn (Assert $page) => $page->component('Workouts/Live')
                ->where('mission.status', 'REST DAY')->where('mission.title', 'Recovery protocol')
                ->has('mission.recoveryObjectives', 4)->where('targetMuscles.primary', [])->where('missionRewards.xp', 0));
    }

    public function test_a_planned_briefing_is_private_and_does_not_start_the_workout(): void
    {
        $owner = $this->hunter();
        $workout = $this->mission($owner, 'planned');

        $this->get(route('workouts.live.show', $workout))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('workouts.live.show', $workout))->assertForbidden();
        $this->assertSame('planned', $workout->fresh()->status);
        $this->assertNull($workout->started_at);
    }

    private function hunter(): User
    {
        $user = User::factory()->create();
        $profile = HunterProfile::factory()->create(['user_id' => $user->id, 'codename' => 'Nightbreaker', 'rank' => HunterRank::ERank, 'current_level' => 12, 'awakened_at' => now()]);
        $profile->stats()->create();

        return $user;
    }

    private function mission(User $user, string $status): Workout
    {
        return Workout::factory()->create(['user_id' => $user->id, 'name' => 'Iron Ascent', 'status' => $status, 'scheduled_date' => today()]);
    }
}
