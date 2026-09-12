<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    /**
     * The exercise library. Everything is sent up front — the catalogue is a
     * few hundred small rows — so search and filtering stay instant client
     * side without a round trip per keystroke.
     */
    public function index(Request $request): Response
    {
        $exercises = Exercise::query()
            ->where('is_active', true)
            ->with('exerciseCategory:id,name,slug')
            ->orderBy('name')
            ->get();

        return Inertia::render('Exercises/Index', [
            'exercises' => $exercises->map(fn (Exercise $exercise): array => [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'slug' => $exercise->slug,
                'category' => $exercise->exerciseCategory?->name,
                'categorySlug' => $exercise->exerciseCategory?->slug,
                'type' => $exercise->exercise_type?->value,
                'difficulty' => $exercise->difficulty?->value,
                'equipment' => $this->toList($exercise->equipment_required),
                'primaryMuscles' => $this->toList($exercise->primary_muscle),
                'secondaryMuscles' => $this->toList($exercise->secondary_muscles),
                'contraindications' => $this->toList($exercise->contraindications),
                'instructions' => $exercise->instructions,
                'xp' => (int) $exercise->xp_base_value,
                'videoUrl' => $exercise->video_url,
                'imageUrl' => $exercise->image_url,
            ])->all(),
            'categories' => ExerciseCategory::orderBy('name')->get(['id', 'name', 'slug'])
                ->map(fn (ExerciseCategory $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $exercises->where('exercise_category_id', $category->id)->count(),
                ])
                ->filter(fn (array $category): bool => $category['count'] > 0)
                ->values()
                ->all(),
        ]);
    }

    /**
     * Array-object casts serialise awkwardly, so flatten to a plain list.
     *
     * @return array<int, string>
     */
    private function toList(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        return collect($value instanceof \Traversable ? iterator_to_array($value) : (array) $value)
            ->filter(fn ($item) => is_string($item) && $item !== '')
            ->values()
            ->all();
    }
}
