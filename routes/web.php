<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HunterSystemController;
use App\Http\Controllers\MissionsController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\Workouts\LiveWorkoutController;
use App\Http\Controllers\Workouts\ProgramController;
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

    // Missions (quests)
    Route::get('/missions', [MissionsController::class, 'index'])->name('missions.index');
    Route::post('/missions/{quest}/claim', [MissionsController::class, 'claim'])->name('missions.claim');

    // Workouts
    Route::get('/workouts', [WorkoutController::class, 'index'])->name('workouts.index');
    Route::post('/workouts', [WorkoutController::class, 'store'])->name('workouts.store');
    Route::patch('/workouts/{workout}', [WorkoutController::class, 'update'])->name('workouts.update');
    Route::post('/workouts/{workout}/start', [WorkoutController::class, 'start'])->name('workouts.start');
    Route::post('/workouts/{workout}/exercises', [WorkoutController::class, 'addExercise'])->name('workouts.exercises.add');
    Route::patch('/workouts/{workout}/exercises/{workoutExercise}/move', [WorkoutController::class, 'moveExercise'])->name('workouts.exercises.move');
    Route::patch('/workouts/{workout}/exercises/{workoutExercise}/sets', [WorkoutController::class, 'updateExerciseSets'])->name('workouts.exercises.sets');
    Route::delete('/workouts/{workout}/exercises/{workoutExercise}', [WorkoutController::class, 'removeExercise'])->name('workouts.exercises.remove');
    Route::get('/workouts/{workout}/live', [LiveWorkoutController::class, 'show'])->name('workouts.live.show');
    Route::post('/workouts/{workout}/sets/{set}/complete', [LiveWorkoutController::class, 'completeSet'])->name('workouts.sets.complete');
    Route::post('/workouts/{workout}/complete', [LiveWorkoutController::class, 'complete'])->name('workouts.complete');

    // Exercise library
    Route::get('/exercises', [ExerciseController::class, 'index'])->name('exercises.index');

    // Training programs
    Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
    Route::get('/programs/create', [ProgramController::class, 'create'])->name('programs.create');
    Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');
    Route::get('/programs/{program}/edit', [ProgramController::class, 'edit'])->name('programs.edit');
    Route::patch('/programs/{program}', [ProgramController::class, 'update'])->name('programs.update');
    Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');
    Route::post('/programs/{program}/duplicate', [ProgramController::class, 'duplicate'])->name('programs.duplicate');
    Route::post('/programs/{program}/enroll', [ProgramController::class, 'enroll'])->name('programs.enroll');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'show'])->name('calendar.show');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::get('/calendar/agenda', [CalendarController::class, 'getAgenda'])->name('calendar.agenda');

    // Hunter profile, skills and titles
    Route::get('/hunter', [HunterSystemController::class, 'show'])->name('hunter.show');
    Route::patch('/hunter', [HunterSystemController::class, 'update'])->name('hunter.update');
    Route::post('/hunter/stats', [HunterSystemController::class, 'allocateStat'])->name('hunter.stats.allocate');
    Route::post('/hunter/skills/{skill}', [HunterSystemController::class, 'learnSkill'])->name('hunter.skills.learn');
    Route::post('/hunter/titles/{title}', [HunterSystemController::class, 'equipTitle'])->name('hunter.titles.equip');

    // Inventory
    Route::get('/inventory', [HunterSystemController::class, 'inventory'])->name('inventory.index');
    Route::post('/inventory/{item}/equip', [HunterSystemController::class, 'equipItem'])->name('inventory.equip');
    Route::post('/inventory/{item}/unequip', [HunterSystemController::class, 'unequipItem'])->name('inventory.unequip');

    // Market (coming soon)
    Route::inertia('/market', 'Market/Index')->name('market.index');

    // Achievements
    Route::get('/achievements', [HunterSystemController::class, 'achievements'])->name('achievements.index');

    // Dungeons and bosses
    Route::get('/dungeons', [HunterSystemController::class, 'dungeons'])->name('dungeons.index');
    Route::post('/dungeons/{dungeon}/enter', [HunterSystemController::class, 'enterDungeon'])->name('dungeons.enter');
    Route::post('/dungeons/runs/{run}/abandon', [HunterSystemController::class, 'abandonDungeon'])->name('dungeons.abandon');
    Route::post('/bosses/{boss}/challenge', [HunterSystemController::class, 'challengeBoss'])->name('bosses.challenge');

    // Health measurements and goals
    Route::get('/health', [HealthController::class, 'index'])->name('health.index');
    Route::post('/health/measurements', [HealthController::class, 'storeMeasurement'])->name('health.measurements.store');
    Route::post('/health/water', [HealthController::class, 'storeWaterLog'])->name('health.water.store');
    Route::delete('/health/water/{waterLog}', [HealthController::class, 'destroyWaterLog'])->name('health.water.destroy');
    Route::post('/health/goals', [HealthController::class, 'storeGoal'])->name('health.goals.store');
    Route::patch('/health/goals/{goal}', [HealthController::class, 'updateGoal'])->name('health.goals.update');

    // Progress and system log
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
