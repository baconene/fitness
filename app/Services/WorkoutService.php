<?php

namespace App\Services;

use App\Enums\ExerciseType;
use App\Enums\RecordType;
use App\Enums\StatChangeReason;
use App\Enums\StatType;
use App\Models\DungeonRun;
use App\Models\Exercise;
use App\Models\PersonalRecord;
use App\Models\ProgramDay;
use App\Models\User;
use App\Models\UserProgramEnrollment;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkoutService
{
    public function __construct(
        private ExperienceService $experienceService,
        private HunterProgressionService $progressionService
    ) {}

    public function startWorkout(User $user, ?ProgramDay $programDay = null): Workout
    {
        return $this->createWorkout($user, [], $programDay);
    }

    /** @param array{name?: string, scheduled_date?: ?string, exercises?: array<int, array{exercise_id: int, sets: int}>} $data */
    public function createWorkout(User $user, array $data, ?ProgramDay $programDay = null): Workout
    {
        return DB::transaction(function () use ($user, $data, $programDay): Workout {
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();
            $scheduled = ! empty($data['scheduled_date']);

            if (! $scheduled) {
                $active = $user->workouts()->where('status', 'in_progress')->first();

                if ($active) {
                    return $active;
                }
            }

            $workout = $user->workouts()->create([
                'program_day_id' => $programDay?->id,
                'training_program_id' => $programDay?->programWeek?->training_program_id,
                'name' => $data['name'] ?? $programDay?->name ?? 'Training mission',
                'scheduled_date' => $data['scheduled_date'] ?? now()->toDateString(),
                'status' => $scheduled ? 'planned' : 'in_progress',
                'started_at' => $scheduled ? null : now(),
            ]);

            $exercises = $programDay
                ? $programDay->programExercises->map(fn ($exercise) => ['exercise_id' => $exercise->exercise_id, 'sets' => $exercise->target_sets])->all()
                : ($data['exercises'] ?? []);
            $this->addExercises($workout, $exercises);

            return $workout;
        });
    }

    public function resumeWorkout(User $user, Workout $workout): Workout
    {
        return DB::transaction(function () use ($user, $workout): Workout {
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();
            $workout = $workout->newQuery()->lockForUpdate()->findOrFail($workout->id);

            if ($workout->status === 'completed') {
                return $workout;
            }

            $active = $user->workouts()->where('status', 'in_progress')->first();

            if ($active) {
                return $active;
            }

            if (! $workout->workoutSets()->exists()) {
                throw ValidationException::withMessages(['workout' => 'Add at least one exercise before starting.']);
            }

            $workout->update(['status' => 'in_progress', 'started_at' => now()]);

            return $workout;
        });
    }

    /** @param array<string, mixed> $data */
    public function updateWorkout(Workout $workout, array $data): void
    {
        DB::transaction(function () use ($workout, $data): void {
            $workout = $workout->newQuery()->lockForUpdate()->findOrFail($workout->id);

            if ($workout->status === 'completed') {
                throw ValidationException::withMessages(['workout' => 'Completed missions are part of your permanent training history.']);
            }

            if (isset($data['exercises']) && $workout->workoutSets()->where('is_completed', true)->exists()) {
                throw ValidationException::withMessages(['exercises' => 'Exercises cannot be replaced after sets have been logged.']);
            }

            if (isset($data['exercises'])) {
                $workout->workoutExercises()->delete();
                $this->addExercises($workout, $data['exercises']);
                $workout->program_day_id = null;
                $workout->training_program_id = null;
            }

            $workout->fill(collect($data)->only(['name', 'scheduled_date', 'status'])->all());
            $workout->save();
        });
    }

    /**
     * Appends a single exercise to a planned or in-progress mission.
     *
     * Unlike updateWorkout(), this leaves existing exercises and their logged sets
     * untouched, so it stays safe once a mission is under way.
     */
    public function addExercise(Workout $workout, int $exerciseId, int $sets): WorkoutExercise
    {
        return DB::transaction(function () use ($workout, $exerciseId, $sets): WorkoutExercise {
            $workout = $workout->newQuery()->lockForUpdate()->findOrFail($workout->id);

            if ($workout->status === 'completed') {
                throw ValidationException::withMessages(['workout' => 'Completed missions are part of your permanent training history.']);
            }

            if ($workout->workoutExercises()->where('exercise_id', $exerciseId)->exists()) {
                throw ValidationException::withMessages(['exercise_id' => 'That exercise is already part of this mission.']);
            }

            if ($workout->workoutExercises()->count() >= 20) {
                throw ValidationException::withMessages(['exercise_id' => 'A mission can hold at most 20 exercises.']);
            }

            $workoutExercise = $workout->workoutExercises()->create([
                'exercise_id' => $exerciseId,
                'order' => (int) $workout->workoutExercises()->max('order') + 1,
            ]);

            for ($setNumber = 1; $setNumber <= $sets; $setNumber++) {
                $workoutExercise->workoutSets()->create(['set_number' => $setNumber]);
            }

            return $workoutExercise->load('exercise', 'workoutSets');
        });
    }

    /**
     * Drops an exercise that has not been trained yet, keeping `order` contiguous.
     */
    public function removeExercise(Workout $workout, WorkoutExercise $workoutExercise): void
    {
        DB::transaction(function () use ($workout, $workoutExercise): void {
            $workout = $workout->newQuery()->lockForUpdate()->findOrFail($workout->id);

            if ($workout->status === 'completed') {
                throw ValidationException::withMessages(['workout' => 'Completed missions are part of your permanent training history.']);
            }

            if ($workoutExercise->workoutSets()->where('is_completed', true)->exists()) {
                throw ValidationException::withMessages(['exercise' => 'Sets are already logged for this exercise.']);
            }

            if ($workout->workoutExercises()->count() <= 1) {
                throw ValidationException::withMessages(['exercise' => 'A mission needs at least one exercise.']);
            }

            $workoutExercise->delete();

            $workout->workoutExercises()->orderBy('order')->get()
                ->each(fn (WorkoutExercise $remaining, int $index) => $remaining->update(['order' => $index + 1]));
        });
    }

    /** @param array<int, array{exercise_id: int, sets: int}> $exercises */
    private function addExercises(Workout $workout, array $exercises): void
    {
        foreach ($exercises as $index => $exercise) {
            $workoutExercise = $workout->workoutExercises()->create([
                'exercise_id' => $exercise['exercise_id'],
                'order' => $index + 1,
            ]);

            for ($setNumber = 1; $setNumber <= $exercise['sets']; $setNumber++) {
                $workoutExercise->workoutSets()->create(['set_number' => $setNumber]);
            }
        }
    }

    /** @param array{reps?: ?int, weight?: ?float, rpe?: ?int, duration_seconds?: ?int, distance_km?: ?float} $data */
    public function completeSet(WorkoutSet $set, array $data, string $idempotencyKey): WorkoutSet
    {
        return DB::transaction(function () use ($set, $data): WorkoutSet {
            $workout = $set->workoutExercise->workout()->lockForUpdate()->firstOrFail();
            $set = $set->newQuery()->lockForUpdate()->findOrFail($set->id);

            if ($set->is_completed) {
                return $set;
            }

            if ($workout->status !== 'in_progress') {
                throw ValidationException::withMessages(['workout' => 'Start this mission before recording a set.']);
            }

            $exercise = $set->workoutExercise->exercise;
            $user = $workout->user;
            $profile = $user->hunterProfile;
            abort_unless($profile, 422, 'Complete your Hunter awakening before logging training.');

            $xp = max(10, $exercise->xp_base_value);
            $set->update([
                'is_completed' => true,
                'reps_completed' => $data['reps'] ?? null,
                'weight_kg' => $data['weight'] ?? 0,
                'rpe' => $data['rpe'] ?? null,
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'distance_km' => $data['distance_km'] ?? null,
                'completed_at' => now(),
                'completion_token' => Str::uuid()->toString(),
                'xp_awarded' => $xp,
            ]);

            $this->experienceService->awardXp($profile, $xp, 'WorkoutCompletion', $set, "workout-set:{$set->id}:xp");

            $stat = match ($exercise->exercise_type) {
                ExerciseType::Strength, ExerciseType::Bodyweight => StatType::Strength,
                ExerciseType::Cardio => StatType::Endurance,
                ExerciseType::Mobility, ExerciseType::Stretching, ExerciseType::Plyometric => StatType::Agility,
            };
            $this->progressionService->applyStatChange($profile, $stat, 1, StatChangeReason::WorkoutCompletion, $set);
            $this->checkPersonalRecord($user, $exercise, $set, $data);

            if (! $set->workoutExercise->workoutSets()->where('is_completed', false)->exists()) {
                $set->workoutExercise->update(['status' => 'completed']);
            }

            return $set->fresh();
        });
    }

    public function completeWorkout(Workout $workout): Workout
    {
        return DB::transaction(function () use ($workout): Workout {
            $workout = $workout->newQuery()->lockForUpdate()->findOrFail($workout->id);

            if ($workout->status === 'completed') {
                return $workout;
            }

            if ($workout->status !== 'in_progress' || ! $workout->workoutSets()->exists() || $workout->workoutSets()->where('is_completed', false)->exists()) {
                throw ValidationException::withMessages(['workout' => 'Complete every set before finishing the mission.']);
            }

            $workout->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_xp_awarded' => $workout->workoutSets()->sum('xp_awarded'),
            ]);

            $user = $workout->user;
            app(QuestGenerationService::class)->updateProgress($user, 'workout', 1);
            app(StreakService::class)->recordActivity($user);
            $this->advanceProgram($workout);

            $run = DungeonRun::where('user_id', $user->id)->where('status', 'Active')
                ->where('started_at', '<=', $workout->started_at)->first();

            if ($run) {
                app(DungeonService::class)->completeFloor($run, 1, "workout:{$workout->id}:dungeon");
            }

            $encounter = $user->bossEncounters()->where('status', 'Active')
                ->where('started_at', '<=', $workout->started_at)->first();

            if ($encounter) {
                $damage = max(1, (int) $workout->total_xp_awarded);
                app(BossBattleService::class)->applyDamage($encounter, $damage, "workout:{$workout->id}:boss");
            }

            app(AchievementService::class)->evaluateForUser($user->fresh());

            return $workout->fresh();
        });
    }

    private function advanceProgram(Workout $workout): void
    {
        if (! $workout->training_program_id) {
            return;
        }

        $enrollment = UserProgramEnrollment::where('user_id', $workout->user_id)
            ->where('training_program_id', $workout->training_program_id)->first();

        if (! $enrollment) {
            return;
        }

        $next = $workout->user->workouts()->where('training_program_id', $workout->training_program_id)
            ->whereIn('status', ['planned', 'in_progress'])->orderBy('scheduled_date')->with('programDay.programWeek')->first();

        if ($next?->programDay) {
            $enrollment->update([
                'current_week_number' => $next->programDay->programWeek->week_number,
                'current_day_number' => $next->programDay->day_number,
            ]);
        } else {
            $enrollment->update(['status' => 'completed']);
        }
    }

    /** @param array<string, mixed> $data */
    private function checkPersonalRecord(User $user, Exercise $exercise, WorkoutSet $set, array $data): void
    {
        if (empty($data['weight'])) {
            return;
        }

        $existing = PersonalRecord::where('user_id', $user->id)
            ->where('exercise_id', $exercise->id)->where('record_type', RecordType::MaxWeight->value)
            ->where('is_current', true)->lockForUpdate()->first();

        if (! $existing || $data['weight'] > $existing->value) {
            $existing?->update(['is_current' => false]);
            PersonalRecord::create([
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'record_type' => RecordType::MaxWeight,
                'value' => $data['weight'],
                'unit' => 'kg',
                'workout_set_id' => $set->id,
                'is_current' => true,
                'achieved_at' => now(),
            ]);
            $this->experienceService->awardXp($user->hunterProfile, 25, 'PersonalRecord', $set, "pr:{$set->id}");
        }
    }
}
