<?php

namespace App\Services;

use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgramBuilderService
{
    /**
     * @param  array{name: string, description: ?string, difficulty: string, focus: string, weeks: array<int, mixed>}  $data
     */
    public function create(User $user, array $data): TrainingProgram
    {
        return DB::transaction(function () use ($user, $data): TrainingProgram {
            $program = TrainingProgram::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
                'difficulty' => $data['difficulty'],
                'focus' => $data['focus'],
                'duration_weeks' => count($data['weeks']),
                'is_system_program' => false,
                'created_by_user_id' => $user->id,
            ]);

            $this->syncStructure($program, $data['weeks']);

            return $program;
        });
    }

    /**
     * @param  array{name: string, description: ?string, difficulty: string, focus: string, weeks: array<int, mixed>}  $data
     */
    public function update(TrainingProgram $program, array $data): TrainingProgram
    {
        return DB::transaction(function () use ($program, $data): TrainingProgram {
            $program->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'difficulty' => $data['difficulty'],
                'focus' => $data['focus'],
                'duration_weeks' => count($data['weeks']),
            ]);

            $this->syncStructure($program, $data['weeks']);

            return $program;
        });
    }

    /**
     * Copies a program (including a read-only system one) into a program the user owns.
     */
    public function duplicate(User $user, TrainingProgram $source): TrainingProgram
    {
        $source->load('programWeeks.programDays.programExercises');

        $weeks = $source->programWeeks->map(fn ($week) => [
            'deload' => (bool) $week->deload,
            'days' => $week->programDays->map(fn ($day) => [
                'name' => $day->name,
                'is_rest_day' => (bool) $day->is_rest_day,
                'exercises' => $day->programExercises->map(fn ($exercise) => [
                    'exercise_id' => $exercise->exercise_id,
                    'target_sets' => $exercise->target_sets,
                    'target_reps_min' => $exercise->target_reps_min,
                    'target_reps_max' => $exercise->target_reps_max,
                    'target_weight_pct' => $exercise->target_weight_pct,
                    'rest_seconds' => $exercise->rest_seconds,
                ])->all(),
            ])->all(),
        ])->all();

        return $this->create($user, [
            'name' => Str::limit("{$source->name} (copy)", 120, ''),
            'description' => $source->description,
            'difficulty' => $source->difficulty,
            'focus' => $source->focus,
            'weeks' => $weeks,
        ]);
    }

    /**
     * Rebuilds weeks, days and exercises from the submitted payload.
     *
     * Ordering is taken from array position, so the client never has to send
     * week/day/order numbers and reordering cannot leave gaps.
     *
     * @param  array<int, mixed>  $weeks
     */
    private function syncStructure(TrainingProgram $program, array $weeks): void
    {
        $program->programWeeks()->delete();

        foreach (array_values($weeks) as $weekIndex => $week) {
            $programWeek = $program->programWeeks()->create([
                'week_number' => $weekIndex + 1,
                'deload' => (bool) ($week['deload'] ?? false),
            ]);

            foreach (array_values($week['days'] ?? []) as $dayIndex => $day) {
                $isRestDay = (bool) ($day['is_rest_day'] ?? false);

                $programDay = $programWeek->programDays()->create([
                    'day_number' => $dayIndex + 1,
                    'name' => $day['name'] ?: ($isRestDay ? 'Rest' : 'Training day '.($dayIndex + 1)),
                    'is_rest_day' => $isRestDay,
                ]);

                if ($isRestDay) {
                    continue;
                }

                foreach (array_values($day['exercises'] ?? []) as $order => $exercise) {
                    $programDay->programExercises()->create([
                        'exercise_id' => $exercise['exercise_id'],
                        'order' => $order + 1,
                        'target_sets' => $exercise['target_sets'],
                        'target_reps_min' => $exercise['target_reps_min'],
                        'target_reps_max' => $exercise['target_reps_max'],
                        'target_weight_pct' => $exercise['target_weight_pct'] ?? null,
                        'rest_seconds' => $exercise['rest_seconds'],
                    ]);
                }
            }
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'program';
        $slug = $base;
        $suffix = 1;

        while (TrainingProgram::where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}
