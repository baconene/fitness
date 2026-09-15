<?php

namespace App\Services;

use App\Models\Exercise;

class ExerciseCatalogService
{
    /**
     * Every active exercise, shaped for the library cards and the exercise picker.
     * The catalogue is a few hundred small rows, so it is sent whole and
     * searched and filtered client side.
     *
     * @return list<array{id: int, name: string, slug: string, category: ?string, categorySlug: ?string, type: ?string, difficulty: ?string, equipment: list<string>, primaryMuscles: list<string>, secondaryMuscles: list<string>, contraindications: list<string>, instructions: ?string, xp: int, videoUrl: ?string, imageUrl: ?string}>
     */
    public function activeExercises(): array
    {
        return Exercise::query()
            ->where('is_active', true)
            ->with('exerciseCategory:id,name,slug')
            ->orderBy('name')
            ->get()
            ->map(fn (Exercise $exercise): array => [
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
            ])
            ->values()
            ->all();
    }

    /**
     * Array-object casts serialise awkwardly, so flatten to a plain list.
     *
     * @return list<string>
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
