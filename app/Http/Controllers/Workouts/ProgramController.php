<?php

namespace App\Http\Controllers\Workouts;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgram;
use App\Models\UserProgramEnrollment;
use App\Services\WorkoutService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Programs/Index', [
            'programs' => TrainingProgram::where(fn ($query) => $query->where('is_system_program', true)->orWhere('created_by_user_id', $request->user()->id))
                ->with('programWeeks.programDays.programExercises.exercise')->orderBy('difficulty')->get(),
            'enrollments' => UserProgramEnrollment::where('user_id', $request->user()->id)->get(),
        ]);
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
