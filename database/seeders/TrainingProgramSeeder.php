<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\TrainingProgram;
use Illuminate\Database\Seeder;

class TrainingProgramSeeder extends Seeder
{
    /**
     * System training programs, each expanded into weeks, days and exercises.
     * Idempotent, so it is safe to re-run.
     */
    public function run(): void
    {
        $exercises = Exercise::pluck('id', 'slug');

        if ($exercises->isEmpty()) {
            return;
        }

        foreach ($this->programs() as $definition) {
            $program = TrainingProgram::updateOrCreate(
                ['slug' => str($definition['name'])->slug()->value()],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'difficulty' => $definition['difficulty'],
                    'focus' => $definition['focus'],
                    'duration_weeks' => $definition['weeks'],
                    'is_system_program' => true,
                ]
            );

            for ($week = 1; $week <= $definition['weeks']; $week++) {
                $programWeek = $program->programWeeks()->updateOrCreate(
                    ['week_number' => $week],
                    ['deload' => $week === $definition['weeks']]
                );

                foreach ($definition['days'] as $dayNumber => $day) {
                    $programDay = $programWeek->programDays()->updateOrCreate(
                        ['day_number' => $dayNumber + 1],
                        ['name' => $day['name'], 'is_rest_day' => empty($day['exercises'])]
                    );

                    foreach (array_values($day['exercises']) as $order => $slug) {
                        if (! isset($exercises[$slug])) {
                            continue;
                        }

                        $programDay->programExercises()->updateOrCreate(
                            ['exercise_id' => $exercises[$slug]],
                            [
                                'order' => $order + 1,
                                'target_sets' => 3,
                                'target_reps_min' => 8,
                                'target_reps_max' => 12,
                                'rest_seconds' => 90,
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * @return list<array{name: string, description: string, difficulty: string, focus: string, weeks: int, days: list<array{name: string, exercises: list<string>}>}>
     */
    private function programs(): array
    {
        return [
            [
                'name' => 'Foundation Protocol',
                'description' => 'Three full-body days a week. Built for a hunter who is just awakening.',
                'difficulty' => 'beginner',
                'focus' => 'general_fitness',
                'weeks' => 4,
                'days' => [
                    ['name' => 'Full Body A', 'exercises' => ['bodyweight-squat', 'push-up', 'bent-over-row', 'plank']],
                    ['name' => 'Rest', 'exercises' => []],
                    ['name' => 'Full Body B', 'exercises' => ['romanian-deadlift', 'shoulder-press', 'lat-pulldown', 'dead-bug']],
                    ['name' => 'Rest', 'exercises' => []],
                    ['name' => 'Full Body C', 'exercises' => ['walking-lunge', 'dips', 'seated-cable-row', 'side-plank']],
                ],
            ],
            [
                'name' => 'Bodyweight Ascent',
                'description' => 'No equipment required. Four days a week, entirely bodyweight.',
                'difficulty' => 'beginner',
                'focus' => 'endurance',
                'weeks' => 4,
                'days' => [
                    ['name' => 'Push', 'exercises' => ['push-up', 'pike-push-up', 'close-grip-push-up']],
                    ['name' => 'Legs', 'exercises' => ['bodyweight-squat', 'walking-lunge', 'glute-bridge', 'calf-raise']],
                    ['name' => 'Rest', 'exercises' => []],
                    ['name' => 'Core', 'exercises' => ['plank', 'russian-twist', 'dead-bug', 'side-plank']],
                    ['name' => 'Conditioning', 'exercises' => ['burpee', 'jump-rope']],
                ],
            ],
            [
                'name' => 'Upper Lower Split',
                'description' => 'Four heavier days for a hunter who has the basics down.',
                'difficulty' => 'intermediate',
                'focus' => 'strength',
                'weeks' => 6,
                'days' => [
                    ['name' => 'Upper Power', 'exercises' => ['bench-press', 'bent-over-row', 'overhead-press', 'barbell-curl']],
                    ['name' => 'Lower Power', 'exercises' => ['back-squat', 'romanian-deadlift', 'calf-raise']],
                    ['name' => 'Rest', 'exercises' => []],
                    ['name' => 'Upper Volume', 'exercises' => ['incline-dumbbell-press', 'pull-up', 'lateral-raise', 'triceps-pushdown']],
                    ['name' => 'Lower Volume', 'exercises' => ['front-squat', 'walking-lunge', 'glute-bridge', 'hanging-leg-raise']],
                ],
            ],
        ];
    }
}
