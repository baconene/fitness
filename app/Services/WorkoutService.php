<?php

namespace App\Services;

use App\Enums\RecordType;
use App\Enums\StatChangeReason;
use App\Enums\StatType;
use App\Models\PersonalRecord;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkoutService
{
    public function __construct(
        private ExperienceService $experienceService,
        private HunterProgressionService $progressionService
    ) {}

    public function startWorkout(User $user, $programDay = null): Workout
    {
        $workout = $user->workouts()->create([
            'program_day_id' => $programDay?->id,
            'training_program_id' => $programDay?->programWeek?->trainingProgram?->id,
            'name' => $programDay?->name ?? 'Workout',
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        if ($programDay) {
            foreach ($programDay->programExercises as $progExercise) {
                $workoutExercise = $workout->workoutExercises()->create([
                    'exercise_id' => $progExercise->exercise_id,
                    'order' => $progExercise->order,
                ]);

                for ($i = 1; $i <= $progExercise->target_sets; $i++) {
                    $workoutExercise->workoutSets()->create([
                        'set_number' => $i,
                    ]);
                }
            }
        }

        return $workout;
    }

    public function completeSet(WorkoutSet $set, array $data, string $idempotencyKey): WorkoutSet
    {
        return DB::transaction(function () use ($set, $data, $idempotencyKey) {
            // Anti-cheat check: if already completed, return existing state
            if ($set->is_completed) {
                return $set;
            }

            // Use unique completion_token to prevent double-submission
            $completionToken = Str::uuid();

            try {
                $set->update([
                    'is_completed' => true,
                    'reps_completed' => $data['reps'] ?? $set->reps_completed,
                    'weight_kg' => $data['weight'] ?? $set->weight_kg,
                    'rpe' => $data['rpe'] ?? $set->rpe,
                    'completed_at' => now(),
                    'completion_token' => $completionToken,
                ]);
            } catch (\Exception $e) {
                // If unique constraint fails, this is a retry—return as-is
                return $set->fresh();
            }

            $workout = $set->workoutExercise->workout;
            $exercise = $set->workoutExercise->exercise;
            $user = $workout->user;
            $profile = $user->hunterProfile;

            // Calculate XP
            $baseXp = $exercise->xp_base_value;
            $xp = max(10, $baseXp);

            // Award XP
            $xpTransaction = $this->experienceService->awardXp(
                $profile,
                $xp,
                'WorkoutCompletion',
                $set,
                "{$idempotencyKey}:xp:{$set->id}"
            );

            $set->update(['xp_awarded' => $xp]);

            // Award stats
            if ($exercise->exercise_type === 'strength') {
                $this->progressionService->applyStatChange(
                    $profile,
                    StatType::Strength,
                    1,
                    StatChangeReason::WorkoutCompletion,
                    $set
                );
            } elseif ($exercise->exercise_type === 'cardio') {
                $this->progressionService->applyStatChange(
                    $profile,
                    StatType::Endurance,
                    1,
                    StatChangeReason::WorkoutCompletion,
                    $set
                );
            }

            // Check for PR
            $this->checkPersonalRecord($user, $exercise, $set, $data);

            return $set->fresh();
        });
    }

    public function completeWorkout(Workout $workout): Workout
    {
        return DB::transaction(function () use ($workout) {
            if ($workout->status === 'completed') {
                return $workout;
            }

            $totalXp = $workout->workoutSets()->whereNotNull('xp_awarded')->sum('xp_awarded');

            $workout->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_xp_awarded' => $totalXp,
            ]);

            return $workout;
        });
    }

    private function checkPersonalRecord(User $user, $exercise, WorkoutSet $set, array $data): void
    {
        if (! isset($data['weight']) || ! $data['weight']) {
            return;
        }

        $recordType = RecordType::MaxWeight;
        $value = $data['weight'];

        $existing = PersonalRecord::where('user_id', $user->id)
            ->where('exercise_id', $exercise->id)
            ->where('record_type', $recordType->value)
            ->where('is_current', true)
            ->first();

        if (! $existing || $value > $existing->value) {
            if ($existing) {
                $existing->update(['is_current' => false]);
            }

            PersonalRecord::create([
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'record_type' => $recordType,
                'value' => $value,
                'unit' => 'kg',
                'workout_set_id' => $set->id,
                'is_current' => true,
                'achieved_at' => now(),
            ]);

            $this->experienceService->awardXp(
                $user->hunterProfile,
                25,
                'PersonalRecord',
                $set,
                "pr:{$set->id}"
            );
        }
    }
}
