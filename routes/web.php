<?php

use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Workouts\LiveWorkoutController;
use App\Http\Controllers\Workouts\WorkoutController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding/step', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    Route::get('/workouts', [WorkoutController::class, 'index'])->name('workouts.index');
    Route::get('/workouts/{workout}/live', [LiveWorkoutController::class, 'show'])->name('workouts.live.show');
    Route::post('/workouts/{workout}/sets/{set}/complete', [LiveWorkoutController::class, 'completeSet'])->name('workouts.sets.complete');

    Route::get('/calendar', [CalendarController::class, 'show'])->name('calendar.show');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::get('/calendar/agenda', [CalendarController::class, 'getAgenda'])->name('calendar.agenda');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
