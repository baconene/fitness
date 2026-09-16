<?php

namespace App\Services;

use App\Models\Food;
use App\Models\MealPlan;
use App\Models\MealPlanItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Builds a day's meals and totals their macros against the hunter's targets.
 *
 * A plan is what is intended to be eaten; food_logs record what actually was.
 * Sending a plan to the log is a deliberate step rather than automatic, so
 * planning a day never claims you ate it.
 */
class MealPlanService
{
    public function __construct(private NutritionService $nutrition) {}

    /**
     * The plan for a day, created on first use.
     *
     * The lookup goes through whereDate: plan_date is cast to a date but stored
     * with a midnight time, so matching it against a plain Y-m-d string fails
     * and firstOrCreate would insert a duplicate into a unique index.
     */
    public function planFor(User $user, ?string $date = null): MealPlan
    {
        $day = $date ?? now($user->timezone())->toDateString();

        return $user->mealPlans()->whereDate('plan_date', $day)->first()
            ?? $user->mealPlans()->create(['plan_date' => $day]);
    }

    /**
     * Adds a portion to a meal.
     *
     * Macros are resolved here and stored on the item, so later corrections to
     * a food's figures cannot silently rewrite a plan already made.
     */
    public function addItem(MealPlan $plan, array $data): MealPlanItem
    {
        if (! in_array($data['meal'], MealPlan::MEALS, true)) {
            throw ValidationException::withMessages(['meal' => 'That is not a meal of the day.']);
        }

        $food = isset($data['food_id']) ? Food::where('is_active', true)->find($data['food_id']) : null;

        if (isset($data['food_id']) && ! $food) {
            throw ValidationException::withMessages(['food_id' => 'That food is no longer available.']);
        }

        $portion = $food
            ? $food->portion($data['servings'] ?? null, $data['grams'] ?? null)
            : [
                'servings' => 1.0,
                'calories' => (int) ($data['calories'] ?? 0),
                'protein_g' => (float) ($data['protein_g'] ?? 0),
                'carbs_g' => (float) ($data['carbs_g'] ?? 0),
                'fat_g' => (float) ($data['fat_g'] ?? 0),
            ];

        return $plan->items()->create([
            'food_id' => $food?->id,
            'meal' => $data['meal'],
            'name' => $food?->name ?? $data['name'],
            'servings' => $portion['servings'],
            'calories' => $portion['calories'],
            'protein_g' => $portion['protein_g'],
            'carbs_g' => $portion['carbs_g'],
            'fat_g' => $portion['fat_g'],
            'position' => (int) $plan->items()->where('meal', $data['meal'])->max('position') + 1,
        ]);
    }

    public function removeItem(MealPlan $plan, MealPlanItem $item): void
    {
        abort_unless($item->meal_plan_id === $plan->id, 403);

        $item->delete();
    }

    /**
     * The plan grouped by meal, with per-meal and whole-day totals measured
     * against the hunter's targets.
     *
     * @return array<string, mixed>
     */
    public function summary(User $user, ?string $date = null): array
    {
        $plan = $this->planFor($user, $date);
        $plan->load('items');
        $targets = $this->nutrition->targetsFor($user);

        $meals = collect(MealPlan::MEALS)->mapWithKeys(function (string $meal) use ($plan): array {
            $items = $plan->items->where('meal', $meal)->values();

            return [$meal => [
                'items' => $items->map(fn (MealPlanItem $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'servings' => $item->servings,
                    'calories' => $item->calories,
                    'protein' => $item->protein_g,
                    'carbs' => $item->carbs_g,
                    'fat' => $item->fat_g,
                ])->all(),
                'calories' => (int) $items->sum('calories'),
            ]];
        })->all();

        $totals = [
            'calories' => (int) $plan->items->sum('calories'),
            'protein' => round($plan->items->sum('protein_g'), 1),
            'carbs' => round($plan->items->sum('carbs_g'), 1),
            'fat' => round($plan->items->sum('fat_g'), 1),
        ];

        return [
            'date' => $plan->plan_date->toDateString(),
            'loggedAt' => $plan->logged_at?->toIso8601String(),
            'meals' => $meals,
            'totals' => $totals,
            'targets' => $targets,
            'remaining' => [
                'calories' => $targets['calories'] - $totals['calories'],
                'protein' => round($targets['protein'] - $totals['protein'], 1),
                'carbs' => round($targets['carbs'] - $totals['carbs'], 1),
                'fat' => round($targets['fat'] - $totals['fat'], 1),
            ],
            'percent' => $targets['calories'] > 0
                ? min(100, (int) round($totals['calories'] / $targets['calories'] * 100))
                : 0,
        ];
    }

    /**
     * Copies a plan into the food log, so a planned day becomes recorded intake.
     *
     * Guarded against a second run: logging twice would double the day's
     * intake, and the plan remembers when it was sent.
     */
    public function sendToLog(User $user, MealPlan $plan): int
    {
        abort_unless($plan->user_id === $user->id, 403);

        if ($plan->logged_at) {
            throw ValidationException::withMessages(['plan' => 'This plan has already been logged.']);
        }

        $plan->load('items');

        if ($plan->items->isEmpty()) {
            throw ValidationException::withMessages(['plan' => 'There is nothing planned to log.']);
        }

        return DB::transaction(function () use ($user, $plan): int {
            // Meals land at midday on the planned date rather than "now", so a
            // plan logged the next morning still counts for the day it was for.
            $at = Carbon::parse($plan->plan_date->toDateString(), $user->timezone())->setTime(12, 0)->utc();

            foreach ($plan->items as $item) {
                $user->foodLogs()->create([
                    'name' => $item->name,
                    'calories' => $item->calories,
                    'protein_g' => (int) round($item->protein_g),
                    'carbs_g' => (int) round($item->carbs_g),
                    'fat_g' => (int) round($item->fat_g),
                    'logged_at' => $at,
                ]);
            }

            $plan->update(['logged_at' => now()]);

            return $plan->items->count();
        });
    }
}
