<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\MealPlan;
use App\Models\MealPlanItem;
use App\Services\MealPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MealPlanController extends Controller
{
    public function __construct(private MealPlanService $plans) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $date = $request->query('date');

        return Inertia::render('Meals/Index', [
            'plan' => $this->plans->summary($user, $date),
            'meals' => MealPlan::MEALS,
            // The catalogue is a few hundred small rows, so it ships whole and
            // the picker filters instantly without a request per keystroke.
            'foods' => Food::where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'category', 'serving_label', 'serving_grams', 'calories', 'protein_g', 'carbs_g', 'fat_g', 'notes'])
                ->map(fn (Food $food): array => [
                    'id' => $food->id,
                    'name' => $food->name,
                    'category' => $food->category,
                    'servingLabel' => $food->serving_label,
                    'servingGrams' => $food->serving_grams,
                    'calories' => $food->calories,
                    'protein' => $food->protein_g,
                    'carbs' => $food->carbs_g,
                    'fat' => $food->fat_g,
                    'notes' => $food->notes,
                ]),
            'categories' => Food::where('is_active', true)->distinct()->orderBy('category')->pluck('category'),
            'today' => now($user->timezone())->toDateString(),
        ]);
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'meal' => ['required', Rule::in(MealPlan::MEALS)],
            'food_id' => ['nullable', 'integer', 'exists:foods,id'],
            'servings' => ['nullable', 'numeric', 'between:0.25,20'],
            'grams' => ['nullable', 'numeric', 'between:1,5000'],
            // A custom entry needs its own name and figures.
            'name' => ['required_without:food_id', 'nullable', 'string', 'max:120'],
            'calories' => ['required_without:food_id', 'nullable', 'integer', 'between:0,10000'],
            'protein_g' => ['nullable', 'numeric', 'between:0,1000'],
            'carbs_g' => ['nullable', 'numeric', 'between:0,1000'],
            'fat_g' => ['nullable', 'numeric', 'between:0,1000'],
        ]);

        $plan = $this->plans->planFor($request->user(), $data['date'] ?? null);
        $this->plans->addItem($plan, $data);

        return back()->with('success', 'Added to the plan.');
    }

    public function destroyItem(Request $request, MealPlanItem $item): RedirectResponse
    {
        $plan = $item->mealPlan;
        abort_unless($plan->user_id === $request->user()->id, 403);

        $this->plans->removeItem($plan, $item);

        return back()->with('success', 'Removed from the plan.');
    }

    /** Turns the plan into recorded intake for the day it was planned for. */
    public function log(Request $request): RedirectResponse
    {
        $data = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);
        $plan = $this->plans->planFor($request->user(), $data['date'] ?? null);

        $count = $this->plans->sendToLog($request->user(), $plan);

        return back()->with('success', "{$count} meals logged.");
    }
}
