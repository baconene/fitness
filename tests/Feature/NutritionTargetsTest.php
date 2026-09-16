<?php

namespace Tests\Feature;

use App\Enums\GoalType;
use App\Enums\HunterRank;
use App\Models\User;
use App\Services\NutritionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NutritionTargetsTest extends TestCase
{
    use RefreshDatabase;

    private function hunter(): User
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'awakened_at' => now()]);
        $profile->stats()->create();

        return $user;
    }

    private function measure(User $user, float $weight, ?float $bodyFat = null): void
    {
        $user->healthMeasurements()->create([
            'measured_at' => now()->toDateString(),
            'weight_kg' => $weight,
            'height_cm' => 178,
            'body_fat_pct' => $bodyFat,
        ]);
    }

    private function targets(User $user): array
    {
        return app(NutritionService::class)->targetsFor($user->fresh());
    }

    public function test_targets_fall_back_to_a_flat_default_with_no_measurement(): void
    {
        $targets = $this->targets($this->hunter());

        $this->assertSame(2450, $targets['calories']);
        $this->assertTrue($targets['isEstimated']);
        $this->assertSame('No measurement recorded yet', $targets['basis']);
    }

    public function test_body_fat_switches_the_calculation_to_lean_mass(): void
    {
        $user = $this->hunter();
        $this->measure($user, 80, 20);

        $targets = $this->targets($user);

        // 80kg at 20% fat = 64kg lean -> 370 + 21.6*64 = 1752 BMR, x1.25 sedentary.
        $this->assertSame(2190, $targets['calories']);
        $this->assertSame('Lean mass and training volume', $targets['basis']);
        $this->assertFalse($targets['isEstimated']);
    }

    public function test_without_body_fat_the_result_is_marked_as_an_estimate(): void
    {
        $user = $this->hunter();
        $this->measure($user, 80);

        $targets = $this->targets($user);

        $this->assertSame('Bodyweight and training volume', $targets['basis']);
        $this->assertTrue($targets['isEstimated'], 'The UI should be able to say this is rough.');
    }

    public function test_training_more_days_raises_the_target(): void
    {
        $user = $this->hunter();
        $this->measure($user, 80, 20);
        $sedentary = $this->targets($user)['calories'];

        $user->trainingPreference()->create([
            'experience_level' => 'intermediate',
            'days_per_week' => 6,
            'session_duration_minutes' => 60,
            'training_focus' => 'strength',
        ]);

        $this->assertGreaterThan($sedentary, $this->targets($user)['calories']);
    }

    public function test_the_goal_moves_the_target_in_the_expected_direction(): void
    {
        $user = $this->hunter();
        $this->measure($user, 80, 20);
        $maintenance = $this->targets($user)['calories'];

        $goal = $user->fitnessGoals()->create([
            'goal_type' => GoalType::WeightLoss,
            'is_primary' => true,
            'status' => 'active',
        ]);
        $this->assertLessThan($maintenance, $this->targets($user)['calories'], 'Fat loss should be a deficit.');

        $goal->update(['goal_type' => GoalType::MuscleGain]);
        $this->assertGreaterThan($maintenance, $this->targets($user)['calories'], 'Gaining should be a surplus.');
    }

    /**
     * The bars are drawn from the percentages and the numbers read from the
     * grams, so the two must describe the same plan.
     */
    public function test_macro_energy_reconciles_with_the_calorie_target(): void
    {
        $user = $this->hunter();
        $this->measure($user, 80, 20);

        $targets = $this->targets($user);
        $energy = $targets['protein'] * 4 + $targets['carbs'] * 4 + $targets['fat'] * 9;

        $this->assertEqualsWithDelta($targets['calories'], $energy, 12, 'Macros must add up to the target.');
        $this->assertSame(
            100,
            $targets['carbPercent'] + $targets['proteinPercent'] + $targets['fatPercent'],
            'The split must total 100 or the bars misrepresent it.',
        );
    }

    /**
     * Regression: protein was set per kilogram of total bodyweight, which
     * overshot badly at higher body fat — a 30% body fat hunter was given 44%
     * of their calories as protein.
     */
    public function test_protein_scales_with_lean_mass_not_total_weight(): void
    {
        $lean = $this->hunter();
        $this->measure($lean, 80, 12);

        $heavier = $this->hunter();
        $this->measure($heavier, 80, 32);

        $this->assertGreaterThan(
            $this->targets($heavier)['protein'],
            $this->targets($lean)['protein'],
            'At equal weight, the leaner hunter carries more muscle and needs more protein.',
        );

        $this->assertLessThan(40, $this->targets($heavier)['proteinPercent'], 'Protein should not dominate the split.');
    }

    public function test_the_dashboard_serves_real_targets_rather_than_placeholders(): void
    {
        $user = $this->hunter();
        $this->measure($user, 95, 25);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->has(
                    'health.nutrition',
                    fn ($nutrition) => $nutrition->hasAll([
                        'calories', 'protein', 'carbs', 'fat',
                        'carbPercent', 'proteinPercent', 'fatPercent',
                        'basis', 'goal', 'isEstimated',
                    ])->where('calories', fn (int $calories): bool => $calories !== 2450)
                )
            );
    }
}
