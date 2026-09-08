<?php

namespace App\Http\Controllers\Workouts;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteWorkoutSetRequest;
use App\Models\Workout;
use App\Models\WorkoutSet;
use App\Services\WorkoutService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LiveWorkoutController extends Controller
{
    public function __construct(private WorkoutService $workoutService) {}

    public function show(Workout $workout)
    {
        Gate::authorize('view', $workout);

        $workout->load([
            'workoutExercises.exercise',
            'workoutExercises.workoutSets',
            'user.hunterProfile',
        ]);

        $currentExerciseIndex = $workout->workoutExercises
            ->search(fn ($ex) => $ex->workoutSets->some(fn ($set) => ! $set->is_completed));

        if ($currentExerciseIndex === false) {
            $currentExerciseIndex = 0;
        }

        return Inertia::render('Workouts/Live', [
            'workout' => $workout,
            'currentExerciseIndex' => $currentExerciseIndex,
        ]);
    }

    public function completeSet(Workout $workout, WorkoutSet $set, CompleteWorkoutSetRequest $request)
    {
        Gate::authorize('view', $workout);

        $completed = $this->workoutService->completeSet(
            $set,
            $request->validated(),
            $request->input('idempotency_key', Str::uuid()->toString())
        );

        return response()->json([
            'set' => $completed,
            'xpAwarded' => $completed->xp_awarded,
            'hunterProfile' => $workout->user->hunterProfile->fresh(),
        ]);
    }
}
