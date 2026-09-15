<?php

namespace App\Http\Controllers\Workouts;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingProgramRequest;
use App\Http\Requests\UpdateTrainingProgramRequest;
use App\Models\TrainingProgram;
use App\Models\UserProgramEnrollment;
use App\Services\ExerciseCatalogService;
use App\Services\ProgramBuilderService;
use App\Services\WorkoutService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function index(Request $request): Response
    {
        $programs = TrainingProgram::where(fn ($query) => $query->where('is_system_program', true)->orWhere('created_by_user_id', $request->user()->id))
            ->with('programWeeks.programDays.programExercises.exercise')->orderBy('difficulty')->get();

        return Inertia::render('Programs/Index', [
            'programs' => $programs->map(fn (TrainingProgram $program) => array_merge($program->toArray(), [
                'can_edit' => $request->user()->can('update', $program),
                'can_delete' => $request->user()->can('delete', $program),
            ])),
            'enrollments' => UserProgramEnrollment::where('user_id', $request->user()->id)->get(),
            'today' => now($request->user()->timezone())->toDateString(),
        ]);
    }

    public function create(Request $request, ExerciseCatalogService $catalog): Response
    {
        Gate::authorize('create', TrainingProgram::class);

        return Inertia::render('Programs/Edit', [
            'program' => null,
            'exercises' => $catalog->activeExercises(),
        ]);
    }

    public function store(StoreTrainingProgramRequest $request, ProgramBuilderService $builder): RedirectResponse
    {
        $program = $builder->create($request->user(), $request->validated());

        return to_route('programs.index')->with('success', "\"{$program->name}\" is ready. Enroll when you want the missions scheduled.");
    }

    public function edit(Request $request, TrainingProgram $program, ExerciseCatalogService $catalog): Response
    {
        Gate::authorize('update', $program);
        $program->load('programWeeks.programDays.programExercises');

        return Inertia::render('Programs/Edit', [
            'program' => $program,
            'exercises' => $catalog->activeExercises(),
        ]);
    }

    public function update(UpdateTrainingProgramRequest $request, TrainingProgram $program, ProgramBuilderService $builder): RedirectResponse
    {
        $builder->update($program, $request->validated());

        return to_route('programs.index')->with('success', "\"{$program->name}\" has been updated.");
    }

    public function duplicate(Request $request, TrainingProgram $program, ProgramBuilderService $builder): RedirectResponse
    {
        Gate::authorize('duplicate', $program);
        $copy = $builder->duplicate($request->user(), $program);

        return to_route('programs.edit', $copy)->with('success', 'Copied. This version is yours to edit.');
    }

    public function destroy(Request $request, TrainingProgram $program): RedirectResponse
    {
        Gate::authorize('delete', $program);
        $program->delete();

        return to_route('programs.index')->with('success', 'Program deleted.');
    }

    public function enroll(Request $request, TrainingProgram $program, WorkoutService $workoutService): RedirectResponse
    {
        abort_unless($program->is_system_program || $program->created_by_user_id === $request->user()->id, 403);
        abort_unless($request->user()->hunterProfile, 403);
        $data = $request->validate(['start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today']]);
        $program->load('programWeeks.programDays.programExercises');
        abort_unless($program->programWeeks->flatMap->programDays->contains(fn ($day) => ! $day->is_rest_day && $day->programExercises->isNotEmpty()), 422, 'This program has no training days yet.');

        DB::transaction(function () use ($request, $program, $data, $workoutService): void {
            $request->user()->newQuery()->whereKey($request->user()->id)->lockForUpdate()->first();
            $enrollment = UserProgramEnrollment::firstOrCreate(
                ['user_id' => $request->user()->id, 'training_program_id' => $program->id],
                ['started_at' => $data['start_date'], 'status' => 'active']
            );

            if (! $enrollment->wasRecentlyCreated) {
                return;
            }

            foreach ($program->programWeeks as $week) {
                foreach ($week->programDays as $day) {
                    if ($day->is_rest_day || $day->programExercises->isEmpty()) {
                        continue;
                    }

                    $date = Carbon::parse($data['start_date'])->addDays(($week->week_number - 1) * 7 + $day->day_number - 1);
                    $workoutService->createWorkout($request->user(), ['scheduled_date' => $date->toDateString()], $day);
                }
            }
        });

        return to_route('workouts.index')->with('success', 'Program missions are ready in your training log and calendar.');
    }
}
