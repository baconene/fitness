<?php

namespace App\Services;

use App\Models\HunterProfile;
use App\Models\Workout;

class LiveMissionService
{
    public function __construct(private ExperienceService $experienceService) {}

    /** @return array{name: string, rank: string, level: int, currentXp: int, requiredXp: int} */
    public function hunter(HunterProfile $profile): array
    {
        $progress = $this->experienceService->getLevelProgress($profile);

        return [
            'name' => $profile->codename ?: $profile->user->name,
            'rank' => $profile->rank->value,
            'level' => (int) $profile->current_level,
            'currentXp' => $progress['current'],
            'requiredXp' => $progress['required'],
        ];
    }

    /** @return array<string, mixed> */
    public function forWorkout(Workout $workout): array
    {
        $user = $workout->user;
        $exercises = $workout->workoutExercises;
        $sets = $exercises->flatMap(fn ($exercise) => $exercise->workoutSets);
        $isRecovery = (bool) $workout->programDay?->is_rest_day;
        $state = match ($workout->status) {
            'completed' => 'COMPLETED',
            'skipped' => 'MISSED',
            'paused' => 'PAUSED',
            default => $isRecovery ? 'REST DAY' : ($workout->status === 'in_progress' ? 'ACTIVE' : 'READY'),
        };
        $primary = $exercises->flatMap(fn ($entry) => $this->muscles($entry->exercise?->primary_muscle))->unique()->values();
        $secondary = $exercises->flatMap(fn ($entry) => $this->muscles($entry->exercise?->secondary_muscles))->unique()->diff($primary)->values();
        $goal = $user->fitnessGoals()->where('status', 'active')->orderByDesc('is_primary')->latest()->first();
        $measurement = $user->healthMeasurements()->orderByDesc('measured_at')->orderByDesc('id')->first();
        $types = $exercises->map(fn ($entry) => $entry->exercise?->exercise_type?->value)->filter()->unique();

        return [
            'hunter' => $user->hunterProfile ? $this->hunter($user->hunterProfile) : null,
            'mission' => [
                'id' => $workout->id,
                'title' => $isRecovery ? 'Recovery protocol' : $workout->name,
                'type' => $isRecovery ? 'Recovery' : ($types->count() === 1 ? ucfirst($types->first()) : 'Training'),
                'status' => $state,
                'date' => $workout->scheduled_date?->toDateString(),
                'durationMinutes' => $isRecovery ? null : $user->trainingPreference?->session_duration_minutes,
                'description' => $isRecovery
                    ? 'Recovery is part of progression. Make room for rest, gentle movement and your recovery routine.'
                    : 'Your training sequence is ready. Complete each assigned set and record your effort to advance your Hunter status.',
                'attributes' => $isRecovery ? ['VIT', 'WIL'] : $types->map(fn (string $type) => match ($type) {
                    'strength', 'bodyweight' => 'STR',
                    'cardio' => 'END',
                    default => 'AGI',
                })->unique()->values()->all(),
                'recoveryObjectives' => $isRecovery ? ['Light stretching', 'Gentle mobility', 'Follow your hydration target', 'Make time for sleep'] : [],
                'goal' => $goal ? ucwords(str_replace('_', ' ', $goal->goal_type->value)) : null,
                'recentWorkouts' => $user->workouts()->where('status', 'completed')->where('completed_at', '>=', now($user->timezone())->subDays(7))->count(),
                'completionPercent' => $sets->isEmpty() ? 0 : (int) round($sets->where('is_completed', true)->count() / $sets->count() * 100),
            ],
            'targetMuscles' => ['primary' => $isRecovery ? [] : $primary->all(), 'secondary' => $isRecovery ? [] : $secondary->all()],
            'missionRewards' => [
                'xp' => $isRecovery ? 0 : (int) $exercises->sum(fn ($entry) => $entry->workoutSets->sum(
                    fn ($set) => $set->is_completed ? $set->xp_awarded : max(10, $entry->exercise?->xp_base_value ?? 10)
                )),
                'earnedXp' => (int) $sets->where('is_completed', true)->sum('xp_awarded'),
                'description' => 'Base set XP. Personal records and quest rewards are awarded separately.',
            ],
            'healthTargets' => [
                'calorieTarget' => null, 'calorieCurrent' => null,
                'waterTargetLiters' => null, 'waterCurrentLiters' => null,
                'stepTarget' => null, 'stepsCurrent' => null,
                'sleepTargetMinutes' => null, 'sleepCurrentMinutes' => null,
                'weightKg' => $measurement?->weight_kg,
                'goal' => $goal ? ['name' => ucwords(str_replace('_', ' ', $goal->goal_type->value)), 'target' => $goal->target_value, 'unit' => $goal->target_unit] : null,
            ],
            'dailyQuests' => $user->userQuests()->whereDate('assigned_date', now($user->timezone())->toDateString())
                ->with(['questTemplate', 'progress'])->get()->map(fn ($quest) => [
                    'id' => $quest->id,
                    'name' => $quest->questTemplate?->name ?? 'Daily quest',
                    'current' => (float) ($quest->progress?->current_value ?? 0),
                    'target' => (float) ($quest->questTemplate?->target_value ?? 1),
                    'xpReward' => (int) ($quest->questTemplate?->xp_reward_base ?? 0),
                    'status' => $quest->status,
                ])->all(),
            'systemMessage' => 'One objective at a time. Record your effort. Let consistency build your next level.',
        ];
    }

    /** @return list<string> */
    private function muscles(mixed $muscles): array
    {
        $aliases = ['quadriceps' => 'quads', 'back' => 'upper_back', 'rear_delts' => 'shoulders', 'deltoids' => 'shoulders', 'core' => 'abs'];

        return collect($muscles ? (array) $muscles : [])->filter(fn ($muscle) => is_string($muscle))
            ->map(function (string $muscle) use ($aliases): string {
                $key = str_replace([' ', '-'], '_', strtolower(trim($muscle)));

                return $aliases[$key] ?? $key;
            })->filter()->values()->all();
    }
}
