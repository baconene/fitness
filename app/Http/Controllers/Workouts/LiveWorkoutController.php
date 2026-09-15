<?php

namespace App\Http\Controllers\Workouts;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteWorkoutSetRequest;
use App\Models\PersonalRecord;
use App\Models\Workout;
use App\Models\WorkoutSet;
use App\Services\ExerciseCatalogService;
use App\Services\LiveMissionService;
use App\Services\WorkoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LiveWorkoutController extends Controller
{
    public function __construct(private WorkoutService $workoutService, private LiveMissionService $liveMissionService) {}

    public function show(Workout $workout, ExerciseCatalogService $catalog): Response|RedirectResponse
    {
        Gate::authorize('view', $workout);

        if (! in_array($workout->status, ['planned', 'in_progress', 'paused', 'completed', 'skipped'], true)) {
            return to_route('workouts.index')->with('success', 'Start this mission from your training log.');
        }

        $workout->load([
            'workoutExercises' => fn ($query) => $query->orderBy('order'),
            'workoutExercises.exercise',
            'workoutExercises.workoutSets' => fn ($query) => $query->orderBy('set_number'),
            'programDay.programExercises',
        ]);

        $recordSetIds = PersonalRecord::where('user_id', $workout->user_id)
            ->whereIn('workout_set_id', $workout->workoutExercises->flatMap(fn ($exercise) => $exercise->workoutSets->pluck('id')))
            ->pluck('workout_set_id');

        foreach ($workout->workoutExercises as $exercise) {
            foreach ($exercise->workoutSets as $set) {
                $set->setAttribute('is_pr', $recordSetIds->contains($set->id));
            }
            $target = $workout->programDay?->programExercises->firstWhere('exercise_id', $exercise->exercise_id);
            $exercise->setAttribute('rest_seconds', $target?->rest_seconds ?? 60);
            $exercise->setAttribute('target_reps', $target ? "{$target->target_reps_min}–{$target->target_reps_max}" : null);
            $previous = WorkoutSet::whereHas('workoutExercise', fn ($query) => $query->where('exercise_id', $exercise->exercise_id)
                ->whereHas('workout', fn ($query) => $query->where('user_id', $workout->user_id)->where('id', '!=', $workout->id)))
                ->where('is_completed', true)->latest('completed_at')->first(['reps_completed', 'weight_kg', 'duration_seconds', 'distance_km']);
            $exercise->setAttribute('previous_set', $previous);
        }

        $currentExerciseIndex = $workout->workoutExercises->search(fn ($exercise) => $exercise->workoutSets->contains('is_completed', false));

        return Inertia::render('Workouts/Live', [
            ...$this->liveMissionService->forWorkout($workout),
            'workout' => $workout,
            'currentExerciseIndex' => $currentExerciseIndex === false ? 0 : $currentExerciseIndex,
            'exerciseOptions' => $catalog->activeExercises(),
        ]);
    }

    public function completeSet(Workout $workout, WorkoutSet $set, CompleteWorkoutSetRequest $request): JsonResponse
    {
        Gate::authorize('update', $workout);
        abort_unless($set->workoutExercise->workout_id === $workout->id, 403);
        $completed = $this->workoutService->completeSet($set, $request->validated(), $request->input('idempotency_key', Str::uuid()->toString()));
        $completed->setAttribute('is_pr', PersonalRecord::where('user_id', $workout->user_id)->where('workout_set_id', $completed->id)->exists());

        return response()->json([
            'set' => $completed,
            'xpAwarded' => $completed->xp_awarded,
            'hunterProfile' => $workout->user->hunterProfile->fresh(),
            'hunter' => $this->liveMissionService->hunter($workout->user->hunterProfile->fresh()),
        ]);
    }

    public function complete(Workout $workout): RedirectResponse
    {
        Gate::authorize('update', $workout);
        $this->workoutService->completeWorkout($workout);

        return to_route('workouts.live.show', $workout)->with('success', 'Mission cleared. Your Hunter progress is saved.');
    }
}
