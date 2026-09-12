<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\User;
use Database\Seeders\ExerciseCategorySeeder;
use Database\Seeders\ExerciseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseLibraryTest extends TestCase
{
    use RefreshDatabase;

    private function awakenedUser(): User
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'awakened_at' => now()]);
        $profile->stats()->create();

        return $user;
    }

    private function exercise(array $overrides = []): Exercise
    {
        $category = ExerciseCategory::firstOrCreate(
            ['slug' => 'chest'],
            ['name' => 'Chest', 'muscle_group' => 'chest'],
        );

        return Exercise::create(array_merge([
            'exercise_category_id' => $category->id,
            'name' => 'Bench Press',
            'slug' => 'bench-press',
            'exercise_type' => 'strength',
            'difficulty' => 'intermediate',
            'primary_muscle' => ['chest'],
            'secondary_muscles' => ['triceps', 'shoulders'],
            'equipment_required' => ['barbell', 'bench'],
            'instructions' => 'Lower the bar to your chest, then press it back up.',
            'xp_base_value' => 22,
            'is_active' => true,
        ], $overrides));
    }

    public function test_the_library_page_renders_with_the_shape_the_cards_need(): void
    {
        $this->exercise();

        $this->actingAs($this->awakenedUser())
            ->get(route('exercises.index'))
            ->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->component('Exercises/Index')
                    ->has('categories')
                    ->has('exercises', 1, fn ($exercise) => $exercise->hasAll([
                        'id', 'name', 'slug', 'category', 'categorySlug', 'type', 'difficulty',
                        'equipment', 'primaryMuscles', 'secondaryMuscles', 'contraindications',
                        'instructions', 'xp', 'videoUrl', 'imageUrl',
                    ])
                        ->where('name', 'Bench Press')
                        ->where('slug', 'bench-press')
                        ->where('category', 'Chest')
                        ->where('primaryMuscles', ['chest'])
                        ->where('equipment', ['barbell', 'bench'])
                    )
            );
    }

    public function test_array_casts_are_flattened_to_plain_lists(): void
    {
        // AsArrayObject serialises as an object unless flattened, which would
        // break v-for in the card and modal.
        $this->exercise(['contraindications' => ['shoulder injury']]);

        $this->actingAs($this->awakenedUser())
            ->get(route('exercises.index'))
            ->assertInertia(
                fn ($page) => $page->where('exercises.0.secondaryMuscles', ['triceps', 'shoulders'])
                    ->where('exercises.0.contraindications', ['shoulder injury'])
            );
    }

    public function test_an_exercise_without_array_data_still_renders(): void
    {
        $this->exercise([
            'primary_muscle' => null,
            'secondary_muscles' => null,
            'equipment_required' => null,
            'contraindications' => null,
        ]);

        $this->actingAs($this->awakenedUser())
            ->get(route('exercises.index'))
            ->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->where('exercises.0.primaryMuscles', [])
                    ->where('exercises.0.equipment', [])
                    ->where('exercises.0.contraindications', [])
            );
    }

    public function test_inactive_exercises_are_hidden(): void
    {
        $this->exercise();
        $this->exercise(['name' => 'Retired Lift', 'slug' => 'retired-lift', 'is_active' => false]);

        $this->actingAs($this->awakenedUser())
            ->get(route('exercises.index'))
            ->assertInertia(fn ($page) => $page->has('exercises', 1));
    }

    public function test_category_counts_match_the_seeded_library(): void
    {
        $this->seed(ExerciseCategorySeeder::class);
        $this->seed(ExerciseSeeder::class);

        $this->actingAs($this->awakenedUser())
            ->get(route('exercises.index'))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->has('exercises', Exercise::where('is_active', true)->count());

                $categories = collect($page->toArray()['props']['categories']);

                $this->assertGreaterThan(0, $categories->count());
                $this->assertSame(
                    Exercise::where('is_active', true)->count(),
                    $categories->sum('count'),
                    'Category counts should account for every listed exercise.',
                );
            });
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('exercises.index'))->assertRedirect(route('login'));
    }
}
