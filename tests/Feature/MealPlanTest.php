<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Food;
use App\Models\MealPlan;
use App\Models\User;
use App\Services\MealPlanService;
use Database\Seeders\FilipinoFoodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealPlanTest extends TestCase
{
    use RefreshDatabase;

    private function hunter(): User
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'awakened_at' => now()]);
        $profile->stats()->create();
        $user->healthMeasurements()->create([
            'measured_at' => now()->toDateString(),
            'weight_kg' => 80,
            'height_cm' => 178,
            'body_fat_pct' => 20,
        ]);

        return $user;
    }

    private function rice(): Food
    {
        return Food::create([
            'name' => 'Steamed White Rice',
            'slug' => 'steamed-white-rice',
            'cuisine' => 'filipino',
            'category' => 'grain',
            'serving_label' => '1 cup cooked',
            'serving_grams' => 158,
            'calories' => 205,
            'protein_g' => 4.3,
            'carbs_g' => 44.5,
            'fat_g' => 0.4,
        ]);
    }

    private function plan(User $user): array
    {
        return app(MealPlanService::class)->summary($user->fresh());
    }

    public function test_the_filipino_catalogue_seeds(): void
    {
        $this->seed(FilipinoFoodSeeder::class);

        $this->assertGreaterThanOrEqual(60, Food::count());
        $this->assertGreaterThanOrEqual(8, Food::distinct()->count('category'));
        $this->assertSame(Food::count(), Food::distinct()->count('slug'), 'Slugs must be unique.');
        $this->assertSame(0, Food::where('serving_grams', 0)->count(), 'A serving needs a weight to convert from grams.');
    }

    public function test_the_seeder_is_idempotent(): void
    {
        $this->seed(FilipinoFoodSeeder::class);
        $before = Food::count();
        $this->seed(FilipinoFoodSeeder::class);

        $this->assertSame($before, Food::count());
    }

    public function test_a_portion_scales_by_servings(): void
    {
        $portion = $this->rice()->portion(1.5);

        $this->assertSame(308, $portion['calories']);
        $this->assertSame(66.8, $portion['carbs_g']);
    }

    /**
     * Weight is the more precise way to portion, and the serving weight is
     * what converts between the two.
     */
    public function test_a_portion_can_be_given_in_grams(): void
    {
        $portion = $this->rice()->portion(null, 316);

        $this->assertSame(2.0, $portion['servings'], '316g is two 158g servings.');
        $this->assertSame(410, $portion['calories']);
    }

    public function test_food_can_be_planned_into_a_meal(): void
    {
        $user = $this->hunter();
        $rice = $this->rice();

        $this->actingAs($user)
            ->post(route('meals.items.store'), ['meal' => 'lunch', 'food_id' => $rice->id, 'servings' => 2])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $plan = $this->plan($user);
        $this->assertSame(410, $plan['totals']['calories']);
        $this->assertCount(1, $plan['meals']['lunch']['items']);
        $this->assertSame(410, $plan['meals']['lunch']['calories']);
        $this->assertEmpty($plan['meals']['breakfast']['items']);
    }

    public function test_the_plan_is_measured_against_the_hunters_targets(): void
    {
        $user = $this->hunter();
        $this->actingAs($user)->post(route('meals.items.store'), ['meal' => 'lunch', 'food_id' => $this->rice()->id, 'servings' => 1]);

        $plan = $this->plan($user);

        $this->assertGreaterThan(0, $plan['targets']['calories']);
        $this->assertSame($plan['targets']['calories'] - 205, $plan['remaining']['calories']);
    }

    public function test_a_custom_entry_needs_its_own_figures(): void
    {
        $user = $this->hunter();

        $this->actingAs($user)
            ->post(route('meals.items.store'), ['meal' => 'snack'])
            ->assertSessionHasErrors(['name', 'calories']);

        $this->actingAs($user)
            ->post(route('meals.items.store'), ['meal' => 'snack', 'name' => 'Home cooking', 'calories' => 350, 'protein_g' => 20])
            ->assertSessionHasNoErrors();

        $this->assertSame(350, $this->plan($user)['totals']['calories']);
    }

    public function test_an_item_can_be_removed(): void
    {
        $user = $this->hunter();
        $this->actingAs($user)->post(route('meals.items.store'), ['meal' => 'dinner', 'food_id' => $this->rice()->id, 'servings' => 1]);
        $item = MealPlan::where('user_id', $user->id)->firstOrFail()->items()->firstOrFail();

        $this->actingAs($user)->delete(route('meals.items.destroy', $item))->assertRedirect();

        $this->assertSame(0, $this->plan($user)['totals']['calories']);
    }

    public function test_a_hunter_cannot_touch_someone_elses_plan(): void
    {
        $owner = $this->hunter();
        $intruder = $this->hunter();
        $this->actingAs($owner)->post(route('meals.items.store'), ['meal' => 'lunch', 'food_id' => $this->rice()->id, 'servings' => 1]);
        $item = MealPlan::where('user_id', $owner->id)->firstOrFail()->items()->firstOrFail();

        $this->actingAs($intruder)->delete(route('meals.items.destroy', $item))->assertForbidden();
    }

    /**
     * A plan is intent; the food log is what was eaten. Sending one to the
     * other is deliberate, so planning a day never claims you ate it.
     */
    public function test_a_plan_is_not_intake_until_it_is_logged(): void
    {
        $user = $this->hunter();
        $this->actingAs($user)->post(route('meals.items.store'), ['meal' => 'lunch', 'food_id' => $this->rice()->id, 'servings' => 2]);

        $this->assertSame(0, $user->foodLogs()->count(), 'Planning alone records no intake.');

        $this->actingAs($user)->post(route('meals.log'))->assertRedirect();

        $this->assertSame(1, $user->foodLogs()->count());
        $this->assertSame(410, (int) $user->foodLogs()->sum('calories'));
    }

    public function test_a_plan_cannot_be_logged_twice(): void
    {
        $user = $this->hunter();
        $this->actingAs($user)->post(route('meals.items.store'), ['meal' => 'lunch', 'food_id' => $this->rice()->id, 'servings' => 1]);
        $this->actingAs($user)->post(route('meals.log'));

        $this->actingAs($user)->post(route('meals.log'))->assertSessionHasErrors('plan');

        $this->assertSame(1, $user->foodLogs()->count(), 'Logging twice would double the day.');
    }

    public function test_an_empty_plan_cannot_be_logged(): void
    {
        $user = $this->hunter();

        $this->actingAs($user)->post(route('meals.log'))->assertSessionHasErrors('plan');
    }

    public function test_each_day_has_its_own_plan(): void
    {
        $user = $this->hunter();
        $rice = $this->rice();
        $tomorrow = now()->addDay()->toDateString();

        $this->actingAs($user)->post(route('meals.items.store'), ['meal' => 'lunch', 'food_id' => $rice->id, 'servings' => 1]);
        $this->actingAs($user)->post(route('meals.items.store'), ['date' => $tomorrow, 'meal' => 'lunch', 'food_id' => $rice->id, 'servings' => 3]);

        $service = app(MealPlanService::class);
        $this->assertSame(205, $service->summary($user->fresh())['totals']['calories']);
        $this->assertSame(615, $service->summary($user->fresh(), $tomorrow)['totals']['calories']);
    }

    public function test_the_planner_page_renders_with_the_catalogue(): void
    {
        $this->seed(FilipinoFoodSeeder::class);

        $this->actingAs($this->hunter())
            ->get(route('meals.index'))
            ->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->component('Meals/Index')
                    ->has('foods')
                    ->has('categories')
                    ->has('meals', 4)
                    ->has('plan.meals.breakfast')
                    ->has('plan.targets.calories')
            );
    }
}
