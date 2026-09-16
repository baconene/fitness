<?php

namespace App\Http\Controllers\Workouts;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddWorkoutExerciseRequest;
use App\Http\Requests\StoreWorkoutRequest;
use App\Http\Requests\UpdateWorkoutRequest;
use App\Models\Exercise;
use App\Models\ProgramDay;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Services\ExerciseCatalogService;
use App\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WorkoutController extends Controller
{
    public function __construct(private WorkoutService $workoutService) {}

    public function index(Request $request, ExerciseCatalogService $catalog): Response
    {
        return Inertia::render('Workouts/Index', [
            'workouts' => $request->user()->workouts()->with('workoutExercises.exercise', 'workoutExercises.workoutSets')->latest()->paginate(12),
            'exercises' => $catalog->activeExercises(),
            'activeWorkout' => $request->user()->workouts()->where('status', 'in_progress')->latest()->first(['id', 'name']),
        ]);
    }

    public function store(StoreWorkoutRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $programDay = isset($data['program_day_id'])
            ? ProgramDay::with('programWeek.trainingProgram', 'programExercises.exercise')->findOrFail($data['program_day_id'])
            : null;

        if ($programDay) {
            $program = $programDay->programWeek->trainingProgram;
            abort_unless($program->is_system_program || $program->created_by_user_id === $request->user()->id, 403);
            abort_if($programDay->is_rest_day || $programDay->programExercises->isEmpty(), 422, 'Choose a training day with exercises.');
        }

        $workout = $this->workoutService->createWorkout($request->user(), $data, $programDay);

        return $workout->status === 'in_progress'
            ? to_route('workouts.live.show', $workout)
            : to_route('workouts.index')->with('success', 'Mission scheduled. Your calendar is updated.');
    }

    public function start(Request $request, Workout $workout): RedirectResponse
    {
        Gate::authorize('update', $workout);
        $workout = $this->workoutService->resumeWorkout($request->user(), $workout);

        return to_route('workouts.live.show', $workout);
    }

    public function update(UpdateWorkoutRequest $request, Workout $workout): RedirectResponse
    {
        $this->workoutService->updateWorkout($workout, $request->validated());

        return back()->with('success', 'Mission updated.');
    }

    /**
     * Appends an exercise to a mission that is planned or already under way.
     */
    public function addExercise(AddWorkoutExerciseRequest $request, Workout $workout): RedirectResponse
    {
        $data = $request->validated();
        $this->workoutService->addExercise($workout, $data['exercise_id'], $data['sets']);

        return back()->with('success', 'Exercise added to this mission.');
    }

    /**
     * Reorders an exercise within a mission. Sets and their results move with it.
     */
    public function moveExercise(Request $request, Workout $workout, WorkoutExercise $workoutExercise): RedirectResponse
    {
        Gate::authorize('update', $workout);
        abort_unless($workoutExercise->workout_id === $workout->id, 403);

        // Buttons send a direction; dragging sends the position it was dropped at.
        $data = $request->validate([
            'direction' => ['required_without:position', Rule::in(['up', 'down'])],
            'position' => ['required_without:direction', 'integer', 'min:1', 'max:20'],
        ]);

        if (isset($data['position'])) {
            $this->workoutService->moveExerciseTo($workout, $workoutExercise, $data['position']);
        } else {
            $this->workoutService->moveExercise($workout, $workoutExercise, $data['direction'] === 'up' ? -1 : 1);
        }

        return back()->with('success', 'Mission order updated.');
    }

    public function updateExerciseSets(Request $request, Workout $workout, WorkoutExercise $workoutExercise): RedirectResponse
    {
        Gate::authorize('update', $workout);
        abort_unless($workoutExercise->workout_id === $workout->id, 403);

        $data = $request->validate(['sets' => ['required', 'integer', 'between:1,10']]);
        $this->workoutService->setExerciseSetCount($workout, $workoutExercise, $data['sets']);

        return back()->with('success', 'Set count updated.');
    }

    public function removeExercise(Request $request, Workout $workout, WorkoutExercise $workoutExercise): RedirectResponse
    {
        Gate::authorize('update', $workout);
        abort_unless($workoutExercise->workout_id === $workout->id, 403);

        $this->workoutService->removeExercise($workout, $workoutExercise);

        return back()->with('success', 'Exercise removed from this mission.');
    }
}
