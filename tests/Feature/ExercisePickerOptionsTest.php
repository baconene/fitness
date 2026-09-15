<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\User;
use App\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExercisePickerOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_exercise_picker_receives_active_exercises_with_their_details(): void
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'awakened_at' => now()]);
        $profile->stats()->create();

        $category = ExerciseCategory::create(['name' => 'Chest', 'slug' => 'chest', 'muscle_group' => 'chest']);
        $exercise = [
            'exercise_category_id' => $category->id,
            'exercise_type' => 'strength',
            'difficulty' => 'intermediate',
            'primary_muscle' => ['chest'],
            'equipment_required' => ['barbell', 'bench'],
            'instructions' => 'Lower the bar to your chest, then press it back up.',
            'xp_base_value' => 22,
        ];
        Exercise::create([...$exercise, 'name' => 'Bench Press', 'slug' => 'bench-press']);
        Exercise::create([...$exercise, 'name' => 'Retired Lift', 'slug' => 'retired-lift', 'is_active' => false]);

        $workout = app(WorkoutService::class)->startWorkout($user);

        $pickers = [
            [route('workouts.index'), 'Workouts/Index', 'exercises'],
            [route('programs.create'), 'Programs/Edit', 'exercises'],
            [route('missions.index'), 'Missions/Index', 'exerciseOptions'],
            [route('workouts.live.show', $workout), 'Workouts/Live', 'exerciseOptions'],
        ];

        foreach ($pickers as [$url, $component, $prop]) {
            $this->actingAs($user)->get($url)
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component($component)
                    ->has($prop, 1, fn ($option) => $option
                        ->hasAll(['id', 'slug', 'category', 'secondaryMuscles', 'contraindications', 'xp'])
                        ->where('name', 'Bench Press')
                        ->where('type', 'strength')
                        ->where('difficulty', 'intermediate')
                        ->where('primaryMuscles', ['chest'])
                        ->where('equipment', ['barbell', 'bench'])
                        ->where('instructions', 'Lower the bar to your chest, then press it back up.')
                        ->etc()));
        }
    }
}
