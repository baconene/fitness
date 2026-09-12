<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function awakenedUser(): User
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'awakened_at' => now()]);
        $profile->stats()->create();

        return $user;
    }

    private function exercise(string $name = 'Bench Press'): Exercise
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

    /**
     * @return array<string, mixed>
     */
    private function payload(Exercise $exercise, array $overrides = []): array
    {
        return array_merge([
            'name' => 'My Split',
            'description' => 'Built by hand.',
            'difficulty' => 'beginner',
            'focus' => 'strength',
            'weeks' => [
                [
                    'deload' => false,
                    'days' => [
                        [
                            'name' => 'Upper',
                            'is_rest_day' => false,
                            'exercises' => [[
                                'exercise_id' => $exercise->id,
                                'target_sets' => 3,
                                'target_reps_min' => 8,
                                'target_reps_max' => 12,
                                'target_weight_pct' => null,
                                'rest_seconds' => 90,
                            ]],
                        ],
                        ['name' => 'Rest', 'is_rest_day' => true, 'exercises' => []],
                    ],
                ],
            ],
        ], $overrides);
    }

    public function test_a_hunter_can_build_a_program(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $this->actingAs($user)
            ->post(route('programs.store'), $this->payload($exercise))
            ->assertRedirect(route('programs.index'));

        $program = TrainingProgram::where('created_by_user_id', $user->id)->firstOrFail();

        $this->assertSame('My Split', $program->name);
        $this->assertFalse((bool) $program->is_system_program);
        $this->assertSame(1, $program->duration_weeks);
        $this->assertCount(2, $program->programWeeks()->first()->programDays);
        $this->assertSame(
            $exercise->id,
            $program->programWeeks()->first()->programDays()->first()->programExercises()->first()->exercise_id,
        );
    }

    public function test_week_day_and_exercise_order_follows_array_position(): void
    {
        $user = $this->awakenedUser();
        $first = $this->exercise('Bench Press');
        $second = $this->exercise('Barbell Row');

        $payload = $this->payload($first);
        $payload['weeks'][0]['days'][0]['exercises'][] = [
            'exercise_id' => $second->id,
            'target_sets' => 4,
            'target_reps_min' => 5,
            'target_reps_max' => 5,
            'target_weight_pct' => 80,
            'rest_seconds' => 120,
        ];

        $this->actingAs($user)->post(route('programs.store'), $payload)->assertRedirect();

        $day = TrainingProgram::where('created_by_user_id', $user->id)
            ->firstOrFail()->programWeeks()->first()->programDays()->first();

        $this->assertSame([1, 2], $day->programExercises->pluck('order')->all());
        $this->assertSame($second->id, $day->programExercises->firstWhere('order', 2)->exercise_id);
    }

    public function test_editing_a_program_replaces_its_structure(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $this->actingAs($user)->post(route('programs.store'), $this->payload($exercise));
        $program = TrainingProgram::where('created_by_user_id', $user->id)->firstOrFail();

        $payload = $this->payload($exercise, ['name' => 'Renamed Split']);
        $payload['weeks'][] = $payload['weeks'][0];

        $this->actingAs($user)
            ->patch(route('programs.update', $program), $payload)
            ->assertRedirect(route('programs.index'));

        $program->refresh();

        $this->assertSame('Renamed Split', $program->name);
        $this->assertSame(2, $program->duration_weeks);
        $this->assertSame([1, 2], $program->programWeeks->pluck('week_number')->all());
    }

    public function test_a_rest_day_never_keeps_exercises(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $payload = $this->payload($exercise);
        $payload['weeks'][0]['days'][1]['exercises'] = [[
            'exercise_id' => $exercise->id,
            'target_sets' => 3,
            'target_reps_min' => 8,
            'target_reps_max' => 12,
            'target_weight_pct' => null,
            'rest_seconds' => 90,
        ]];

        $this->actingAs($user)->post(route('programs.store'), $payload)->assertRedirect();

        $restDay = TrainingProgram::where('created_by_user_id', $user->id)
            ->firstOrFail()->programWeeks()->first()->programDays()->where('is_rest_day', true)->first();

        $this->assertCount(0, $restDay->programExercises);
    }

    public function test_a_program_needs_a_training_day_with_an_exercise(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $payload = $this->payload($exercise);
        $payload['weeks'][0]['days'] = [['name' => 'Rest', 'is_rest_day' => true, 'exercises' => []]];

        $this->actingAs($user)
            ->post(route('programs.store'), $payload)
            ->assertSessionHasErrors('weeks');

        $this->assertSame(0, TrainingProgram::where('created_by_user_id', $user->id)->count());
    }

    public function test_maximum_reps_cannot_be_below_the_minimum(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $payload = $this->payload($exercise);
        $payload['weeks'][0]['days'][0]['exercises'][0]['target_reps_min'] = 12;
        $payload['weeks'][0]['days'][0]['exercises'][0]['target_reps_max'] = 8;

        $this->actingAs($user)
            ->post(route('programs.store'), $payload)
            ->assertSessionHasErrors('weeks.0.days.0.exercises.0.target_reps_max');
    }

    public function test_system_programs_cannot_be_edited_or_deleted(): void
    {
        $user = $this->awakenedUser();
        $program = TrainingProgram::create([
            'name' => 'Seeded Plan',
            'slug' => 'seeded-plan',
            'difficulty' => 'beginner',
            'focus' => 'strength',
            'duration_weeks' => 1,
            'is_system_program' => true,
        ]);

        $this->actingAs($user)->get(route('programs.edit', $program))->assertForbidden();
        $this->actingAs($user)->patch(route('programs.update', $program), $this->payload($this->exercise()))->assertForbidden();
        $this->actingAs($user)->delete(route('programs.destroy', $program))->assertForbidden();
    }

    public function test_a_system_program_can_be_copied_into_an_editable_one(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $source = TrainingProgram::create([
            'name' => 'Seeded Plan',
            'slug' => 'seeded-plan',
            'difficulty' => 'beginner',
            'focus' => 'strength',
            'duration_weeks' => 1,
            'is_system_program' => true,
        ]);
        $week = $source->programWeeks()->create(['week_number' => 1, 'deload' => false]);
        $day = $week->programDays()->create(['day_number' => 1, 'name' => 'Upper', 'is_rest_day' => false]);
        $day->programExercises()->create([
            'exercise_id' => $exercise->id,
            'order' => 1,
            'target_sets' => 3,
            'target_reps_min' => 8,
            'target_reps_max' => 12,
            'rest_seconds' => 90,
        ]);

        $this->actingAs($user)->post(route('programs.duplicate', $source))->assertRedirect();

        $copy = TrainingProgram::where('created_by_user_id', $user->id)->firstOrFail();

        $this->assertFalse((bool) $copy->is_system_program);
        $this->assertNotSame($source->slug, $copy->slug);
        $this->assertSame(
            $exercise->id,
            $copy->programWeeks()->first()->programDays()->first()->programExercises()->first()->exercise_id,
        );
    }

    public function test_a_program_is_locked_while_an_enrollment_is_active(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $this->actingAs($user)->post(route('programs.store'), $this->payload($exercise));
        $program = TrainingProgram::where('created_by_user_id', $user->id)->firstOrFail();

        $program->enrollments()->create([
            'user_id' => $user->id,
            'started_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($user)->get(route('programs.edit', $program))->assertForbidden();
    }

    public function test_a_hunter_cannot_edit_someone_elses_program(): void
    {
        $owner = $this->awakenedUser();
        $intruder = $this->awakenedUser();
        $exercise = $this->exercise();

        $this->actingAs($owner)->post(route('programs.store'), $this->payload($exercise));
        $program = TrainingProgram::where('created_by_user_id', $owner->id)->firstOrFail();

        $this->actingAs($intruder)->get(route('programs.edit', $program))->assertForbidden();
        $this->actingAs($intruder)->delete(route('programs.destroy', $program))->assertForbidden();
    }

    public function test_deleting_a_program_removes_its_structure(): void
    {
        $user = $this->awakenedUser();
        $exercise = $this->exercise();

        $this->actingAs($user)->post(route('programs.store'), $this->payload($exercise));
        $program = TrainingProgram::where('created_by_user_id', $user->id)->firstOrFail();

        $this->actingAs($user)
            ->delete(route('programs.destroy', $program))
            ->assertRedirect(route('programs.index'));

        $this->assertDatabaseMissing('training_programs', ['id' => $program->id]);
        $this->assertDatabaseMissing('program_weeks', ['training_program_id' => $program->id]);
    }

    public function test_the_builder_page_renders(): void
    {
        $this->actingAs($this->awakenedUser())
            ->get(route('programs.create'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Programs/Edit')->where('program', null)->has('exercises'));
    }
}
