<?php

namespace App\Http\Controllers;

use App\Models\ExerciseCategory;
use App\Services\ExerciseCatalogService;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    /**
     * The exercise library. Everything is sent up front — the catalogue is a
     * few hundred small rows — so search and filtering stay instant client
     * side without a round trip per keystroke.
     */
    public function index(ExerciseCatalogService $catalog): Response
    {
        $exercises = collect($catalog->activeExercises());

        return Inertia::render('Exercises/Index', [
            'exercises' => $exercises->all(),
            'categories' => ExerciseCategory::orderBy('name')->get(['id', 'name', 'slug'])
                ->map(fn (ExerciseCategory $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $exercises->where('categorySlug', $category->slug)->count(),
                ])
                ->filter(fn (array $category): bool => $category['count'] > 0)
                ->values()
                ->all(),
        ]);
    }
}
