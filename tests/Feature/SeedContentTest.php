<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\ProgramExercise;
use App\Models\QuestTemplate;
use App\Models\TrainingProgram;
use Database\Seeders\ExerciseCategorySeeder;
use Database\Seeders\ExerciseSeeder;
use Database\Seeders\QuestTemplateSeeder;
use Database\Seeders\TrainingProgramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedContentTest extends TestCase
{
    use RefreshDatabase;

    /** Metrics QuestGenerationService::synchronizeProgress() can actually compute. */
    private const SUPPORTED_METRICS = [
        'WorkoutsCompleted',
        'MeasurementsLogged',
        'PersonalRecords',
        'WaterLitres',
        'ActiveDays',
        'TrainingMinutes',
        'DistanceKm',
    ];

    private function seedContent(): void
    {
        $this->seed(ExerciseCategorySeeder::class);
        $this->seed(ExerciseSeeder::class);
        $this->seed(TrainingProgramSeeder::class);
        $this->seed(QuestTemplateSeeder::class);
    }

    public function test_the_exercise_library_is_seeded_at_scale(): void
    {
        $this->seedContent();

        $this->assertGreaterThanOrEqual(100, Exercise::count());
        $this->assertSame(Exercise::count(), Exercise::distinct()->count('slug'), 'Slugs must be unique.');
        $this->assertSame(Exercise::count(), Exercise::distinct()->count('name'), 'Names must not duplicate.');
    }

    public function test_every_exercise_is_usable(): void
    {
        $this->seedContent();

        $this->assertSame(0, Exercise::where('is_active', false)->count());
        $this->assertSame(0, Exercise::whereNull('exercise_category_id')->count());
        $this->assertSame(
            0,
            Exercise::where(fn ($query) => $query->whereNull('primary_muscle')->orWhere('primary_muscle', '[]'))->count(),
            'Every exercise needs a primary muscle for the body map.',
        );
    }

    public function test_the_seeders_are_idempotent(): void
    {
        $this->seedContent();
        $before = [Exercise::count(), TrainingProgram::count(), ProgramExercise::count(), QuestTemplate::count()];

        $this->seedContent();

        $this->assertSame($before, [Exercise::count(), TrainingProgram::count(), ProgramExercise::count(), QuestTemplate::count()]);
    }

    public function test_every_program_has_a_trainable_day(): void
    {
        $this->seedContent();

        $this->assertGreaterThanOrEqual(8, TrainingProgram::count());

        TrainingProgram::with('programWeeks.programDays.programExercises')->get()
            ->each(function (TrainingProgram $program): void {
                $trainable = $program->programWeeks->flatMap->programDays
                    ->contains(fn ($day) => ! $day->is_rest_day && $day->programExercises->isNotEmpty());

                $this->assertTrue($trainable, "{$program->name} has no trainable day, so it can never be enrolled in.");
            });
    }

    public function test_program_days_only_reference_seeded_exercises(): void
    {
        $this->seedContent();

        $this->assertSame(
            0,
            ProgramExercise::whereNotIn('exercise_id', Exercise::pluck('id'))->count(),
            'A program day points at an exercise that does not exist.',
        );
    }

    /**
     * A mission whose metric the progress sync cannot compute would be handed
     * out and then sit at zero forever, so none may ship active.
     */
    public function test_active_missions_only_use_metrics_that_can_progress(): void
    {
        $this->seedContent();

        $unsupported = QuestTemplate::where('is_active', true)
            ->whereNotIn('target_metric', self::SUPPORTED_METRICS)
            ->pluck('target_metric', 'slug');

        $this->assertEmpty($unsupported, 'Unprogressable missions are active: '.$unsupported->keys()->implode(', '));
    }

    public function test_the_step_mission_is_retired_because_steps_are_not_tracked(): void
    {
        $this->seedContent();

        QuestTemplate::where('slug', 'reach-8000-steps')->update(['is_active' => true]);
        $this->seed(QuestTemplateSeeder::class);

        $this->assertFalse((bool) QuestTemplate::where('slug', 'reach-8000-steps')->value('is_active'));
    }

    public function test_missions_cover_both_daily_and_weekly_cadences(): void
    {
        $this->seedContent();

        $this->assertGreaterThanOrEqual(5, QuestTemplate::where('is_active', true)->where('quest_type', 'Daily')->count());
        $this->assertGreaterThanOrEqual(5, QuestTemplate::where('is_active', true)->where('quest_type', 'Weekly')->count());
    }
}
