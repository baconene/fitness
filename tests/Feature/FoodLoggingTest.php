<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\FoodLog;
use App\Models\User;
use App\Services\NutritionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodLoggingTest extends TestCase
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

    private function day(User $user): array
    {
        return app(NutritionService::class)->dayFor($user->fresh());
    }

    public function test_a_meal_can_be_logged(): void
    {
        $user = $this->hunter();

        $this->actingAs($user)
            ->post(route('health.food.store'), [
                'name' => 'Chicken and rice',
                'calories' => 620,
                'protein_g' => 48,
                'carbs_g' => 70,
                'fat_g' => 12,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('food_logs', ['user_id' => $user->id, 'name' => 'Chicken and rice', 'calories' => 620]);
        $this->assertSame(620, $this->day($user)['calories']);
    }

    public function test_macros_are_optional(): void
    {
        $user = $this->hunter();

        $this->actingAs($user)
            ->post(route('health.food.store'), ['name' => 'Coffee and a banana', 'calories' => 140])
            ->assertSessionHasNoErrors();

        $day = $this->day($user);
        $this->assertSame(140, $day['calories']);
        $this->assertSame(0, $day['protein'], 'A calorie-only entry contributes nothing to the macro totals.');
    }

    public function test_entries_accumulate_and_reduce_what_is_left(): void
    {
        $user = $this->hunter();
        $target = $this->day($user)['targets']['calories'];

        foreach ([300, 450, 250] as $calories) {
            $this->actingAs($user)->post(route('health.food.store'), ['name' => 'Meal', 'calories' => $calories]);
        }

        $day = $this->day($user);
        $this->assertSame(1000, $day['calories']);
        $this->assertSame($target - 1000, $day['remaining']);
        $this->assertCount(3, $day['entries']);
    }

    public function test_going_over_the_target_reads_as_a_negative_remainder(): void
    {
        $user = $this->hunter();
        $target = $this->day($user)['targets']['calories'];

        $this->actingAs($user)->post(route('health.food.store'), ['name' => 'Feast', 'calories' => $target + 500]);

        $day = $this->day($user);
        $this->assertSame(-500, $day['remaining'], 'Being over should be visible, not clamped away.');
        $this->assertSame(100, $day['percent'], 'The bar still caps at full.');
    }

    public function test_an_entry_can_be_removed(): void
    {
        $user = $this->hunter();
        $log = FoodLog::factory()->for($user)->create(['calories' => 500]);

        $this->actingAs($user)->delete(route('health.food.destroy', $log))->assertRedirect();

        $this->assertDatabaseMissing('food_logs', ['id' => $log->id]);
        $this->assertSame(0, $this->day($user)['calories']);
    }

    public function test_a_hunter_cannot_remove_someone_elses_entry(): void
    {
        $owner = $this->hunter();
        $intruder = $this->hunter();
        $log = FoodLog::factory()->for($owner)->create();

        $this->actingAs($intruder)->delete(route('health.food.destroy', $log))->assertForbidden();
        $this->assertDatabaseHas('food_logs', ['id' => $log->id]);
    }

    public function test_yesterdays_meals_do_not_count_towards_today(): void
    {
        $user = $this->hunter();
        FoodLog::factory()->for($user)->create(['calories' => 900, 'logged_at' => now()->subDay()]);
        FoodLog::factory()->for($user)->create(['calories' => 400, 'logged_at' => now()]);

        $this->assertSame(400, $this->day($user)['calories']);
    }

    public function test_the_entry_is_validated(): void
    {
        $user = $this->hunter();

        $this->actingAs($user)->post(route('health.food.store'), ['calories' => 300])
            ->assertSessionHasErrors('name');

        $this->actingAs($user)->post(route('health.food.store'), ['name' => 'Air', 'calories' => 0])
            ->assertSessionHasErrors('calories');

        $this->assertSame(0, $this->day($user)['calories']);
    }

    public function test_the_dashboard_serves_intake_against_the_target(): void
    {
        $user = $this->hunter();
        $this->actingAs($user)->post(route('health.food.store'), ['name' => 'Oats', 'calories' => 400, 'protein_g' => 20]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->has('health.nutrition', fn ($nutrition) => $nutrition
                    ->hasAll(['calories', 'protein', 'carbs', 'fat', 'percent', 'remaining', 'targets', 'entries'])
                    ->where('calories', 400)
                    ->where('protein', 20)
                    ->has('entries', 1)
                    ->has('targets.calories')
                )
            );
    }
}
