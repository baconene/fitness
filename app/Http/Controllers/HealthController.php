<?php

namespace App\Http\Controllers;

use App\Enums\GoalType;
use App\Models\FitnessGoal;
use App\Models\FoodLog;
use App\Models\WaterLog;
use App\Services\HydrationService;
use App\Services\NutritionService;
use App\Services\QuestGenerationService;
use App\Services\StepTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HealthController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Health/Index', [
            'measurements' => $user->healthMeasurements()->orderBy('measured_at')->orderBy('id')->get(),
            'goals' => $user->fitnessGoals()->orderByDesc('is_primary')->latest()->get(),
            'goalTypes' => array_map(fn (GoalType $type): array => ['value' => $type->value, 'label' => ucwords(str_replace('_', ' ', $type->value))], GoalType::cases()),
            'today' => now($user->timezone())->toDateString(),
            'hydration' => array_merge(
                app(HydrationService::class)->summary($user),
                ['history' => app(HydrationService::class)->historySummary($user)],
            ),
            'nutrition' => array_merge(
                app(NutritionService::class)->dayFor($user),
                ['history' => app(NutritionService::class)->historySummary($user)],
            ),
            'steps' => app(StepTrackingService::class)->summary($user),
        ]);
    }

    public function storeMeasurement(Request $request, QuestGenerationService $quests): RedirectResponse
    {
        $today = now($request->user()->timezone())->toDateString();
        $data = $request->validate([
            'measured_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.$today],
            'weight_kg' => ['required', 'numeric', 'between:1,600'],
            'height_cm' => ['nullable', 'numeric', 'between:30,300'],
            'body_fat_pct' => ['nullable', 'numeric', 'between:0,80'],
            'resting_heart_rate' => ['nullable', 'integer', 'between:20,250'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $request->user()->healthMeasurements()->create($data);
        $quests->synchronizeProgress($request->user());

        return back()->with('success', 'Measurement saved to your history.');
    }

    /**
     * Records a drink. Quick-add buttons send preset sizes; the form sends a
     * custom amount, so both land here.
     */
    public function storeWaterLog(Request $request, HydrationService $hydration, QuestGenerationService $quests): RedirectResponse
    {
        $data = $request->validate([
            'amount_ml' => ['required', 'integer', 'between:1,5000'],
        ]);

        $hydration->log($request->user(), $data['amount_ml']);
        $quests->synchronizeProgress($request->user());

        return back()->with('success', 'Hydration logged.');
    }

    public function destroyWaterLog(Request $request, WaterLog $waterLog, HydrationService $hydration, QuestGenerationService $quests): RedirectResponse
    {
        $hydration->delete($request->user(), $waterLog);
        $quests->synchronizeProgress($request->user());

        return back()->with('success', 'Entry removed.');
    }

    /**
     * Records something eaten. Macros are optional — a quick entry may only
     * carry a calorie count.
     */
    public function storeFoodLog(Request $request, NutritionService $nutrition): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'calories' => ['required', 'integer', 'between:1,10000'],
            'protein_g' => ['nullable', 'integer', 'between:0,1000'],
            'carbs_g' => ['nullable', 'integer', 'between:0,1000'],
            'fat_g' => ['nullable', 'integer', 'between:0,1000'],
        ]);

        $nutrition->log($request->user(), $data);

        return back()->with('success', 'Meal logged.');
    }

    public function destroyFoodLog(Request $request, FoodLog $foodLog, NutritionService $nutrition): RedirectResponse
    {
        $nutrition->delete($request->user(), $foodLog);

        return back()->with('success', 'Entry removed.');
    }

    /**
     * Records the day's step count. A count is a running total, so logging
     * again for the same day replaces it rather than adding.
     */
    public function storeStepLog(Request $request, StepTrackingService $steps, QuestGenerationService $quests): RedirectResponse
    {
        $data = $request->validate([
            'steps' => ['required', 'integer', 'between:0,200000'],
            'counted_on' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:'.now($request->user()->timezone())->toDateString()],
        ]);

        $steps->record($request->user(), $data['steps'], $data['counted_on'] ?? null);
        $quests->synchronizeProgress($request->user());

        return back()->with('success', 'Steps recorded.');
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $this->saveGoal($request);

        return back()->with('success', 'New objective saved.');
    }

    public function updateGoal(Request $request, FitnessGoal $goal): RedirectResponse
    {
        abort_unless($goal->user_id === $request->user()->id, 403);
        $this->saveGoal($request, $goal);

        return back()->with('success', 'Objective updated.');
    }

    private function saveGoal(Request $request, ?FitnessGoal $goal = null): void
    {
        $data = $request->validate([
            'goal_type' => ['required', Rule::enum(GoalType::class)],
            'target_value' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'target_unit' => ['nullable', 'string', Rule::in(['kg', 'km', 'minutes', 'sessions', 'reps', '%'])],
            'target_date' => ['nullable', 'date_format:Y-m-d'],
            'is_primary' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['active', 'completed', 'paused'])],
        ]);

        DB::transaction(function () use ($request, $goal, $data): void {
            $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            if ($data['is_primary']) {
                $request->user()->fitnessGoals()->update(['is_primary' => false]);
            }

            if ($goal) {
                $goal->update($data);
            } else {
                $request->user()->fitnessGoals()->create($data);
            }
        });
    }
}
