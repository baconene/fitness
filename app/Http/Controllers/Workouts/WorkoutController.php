<?php

namespace App\Http\Controllers\Workouts;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WorkoutController extends Controller
{
    public function index()
    {
        $workouts = Auth::user()->workouts()->with('workoutExercises.exercise')->latest()->paginate(10);

        return Inertia::render('Workouts/Index', [
            'workouts' => $workouts,
        ]);
    }
}
