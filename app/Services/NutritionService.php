<?php

namespace App\Services;

use App\Enums\GoalType;
use App\Models\User;

/**
 * Derives daily energy and macronutrient targets from the data the app already
 * records.
 *
 * These are general training estimates for planning, not dietary advice, and
 * they are only as good as the measurements behind them — `basis` says which
 * method was used so the UI can be honest about that.
 *
 * Age and sex are not collected, which rules out Mifflin-St Jeor. Where body
 * fat has been recorded, Katch-McArdle is used instead: it works from lean mass
 * alone and so needs neither. Without body fat it falls back to a flat
 * per-kilogram estimate.
 */
class NutritionService
{
    /** Katch-McArdle: BMR = 370 + 21.6 x lean body mass (kg). */
    private const KATCH_CONSTANT = 370;

    private const KATCH_PER_KG_LEAN = 21.6;

    /** Used when body fat is unknown; a mid-range resting expenditure per kg. */
    private const FALLBACK_BMR_PER_KG = 22.0;

    /** Shown when there is no weight on record at all. */
    private const DEFAULT_CALORIES = 2450;

    /** Stands in for lean mass when body fat has never been measured. */
    private const ASSUMED_LEAN_FRACTION = 0.78;

    private const CALORIES_PER_GRAM = ['protein' => 4, 'carbs' => 4, 'fat' => 9];

    /**
     * @return array{
     *     calories: int, protein: int, carbs: int, fat: int,
     *     carbPercent: int, proteinPercent: int, fatPercent: int,
     *     basis: string, goal: ?string, isEstimated: bool
     * }
     */
    public function targetsFor(User $user): array
    {
        $measurement = $user->healthMeasurements()->latest('measured_at')->latest('id')->first();
        $weight = $measurement?->weight_kg ? (float) $measurement->weight_kg : null;
        $bodyFat = $measurement?->body_fat_pct ? (float) $measurement->body_fat_pct : null;
        $goal = $this->primaryGoal($user);

        if (! $weight) {
            return $this->payload(self::DEFAULT_CALORIES, null, $goal, 'No measurement recorded yet', true);
        }

        $lean = $bodyFat ? $weight * (1 - $bodyFat / 100) : null;

        $bmr = $lean
            ? self::KATCH_CONSTANT + self::KATCH_PER_KG_LEAN * $lean
            : self::FALLBACK_BMR_PER_KG * $weight;

        $calories = $bmr * $this->activityMultiplier($user) * $this->goalMultiplier($goal);

        return $this->payload(
            (int) round($calories / 10) * 10,
            // Protein scales with the tissue that uses it. Against total
            // bodyweight the target overshoots badly at higher body fat.
            $lean ?? $weight * self::ASSUMED_LEAN_FRACTION,
            $goal,
            $lean ? 'Lean mass and training volume' : 'Bodyweight and training volume',
            ! $bodyFat,
        );
    }

    /**
     * Scales resting expenditure by how much the hunter actually trains.
     *
     * Sedentary through very active, driven by sessions per week rather than a
     * self-reported lifestyle bracket.
     */
    private function activityMultiplier(User $user): float
    {
        $days = (int) ($user->trainingPreference?->days_per_week ?? 0);

        return match (true) {
            $days >= 6 => 1.65,
            $days >= 4 => 1.55,
            $days >= 2 => 1.45,
            $days === 1 => 1.35,
            default => 1.25,
        };
    }

    /** A deficit for fat loss, a modest surplus for gaining. */
    private function goalMultiplier(?GoalType $goal): float
    {
        return match ($goal) {
            GoalType::WeightLoss => 0.82,
            GoalType::MuscleGain => 1.10,
            GoalType::StrengthGain => 1.05,
            default => 1.0,
        };
    }

    /**
     * Protein is set per kilogram of lean mass, fat as a share of the calorie
     * target, and carbohydrate takes whatever energy is left. Leaving
     * carbohydrate last keeps protein intact when the target is low.
     *
     * @param  float|null  $leanMass  Lean kilograms, or an estimate of them.
     * @return array<string, mixed>
     */
    private function payload(int $calories, ?float $leanMass, ?GoalType $goal, string $basis, bool $isEstimated): array
    {
        $reference = $leanMass ?? 60.0;
        $proteinPerKgLean = match ($goal) {
            GoalType::WeightLoss, GoalType::BodyRecomposition => 2.4,
            GoalType::MuscleGain, GoalType::StrengthGain => 2.2,
            default => 1.8,
        };

        $protein = (int) round($reference * $proteinPerKgLean);

        // A share of energy rather than g/kg, so the split stays sane at any
        // calorie level; never below a quarter, which protects hormone health.
        $fat = (int) round($calories * ($goal === GoalType::WeightLoss ? 0.28 : 0.27) / self::CALORIES_PER_GRAM['fat']);

        $remaining = $calories
            - $protein * self::CALORIES_PER_GRAM['protein']
            - $fat * self::CALORIES_PER_GRAM['fat'];

        $carbs = max(0, (int) round($remaining / self::CALORIES_PER_GRAM['carbs']));

        return array_merge(
            ['calories' => $calories, 'protein' => $protein, 'carbs' => $carbs, 'fat' => $fat],
            $this->percentages($calories, $protein, $carbs, $fat),
            ['basis' => $basis, 'goal' => $goal?->value, 'isEstimated' => $isEstimated],
        );
    }

    /**
     * Percentages of the actual macro energy rather than of the calorie target,
     * so the three always total 100 and the bars cannot disagree with the grams.
     *
     * @return array{carbPercent: int, proteinPercent: int, fatPercent: int}
     */
    private function percentages(int $calories, int $protein, int $carbs, int $fat): array
    {
        $energy = [
            'protein' => $protein * self::CALORIES_PER_GRAM['protein'],
            'carbs' => $carbs * self::CALORIES_PER_GRAM['carbs'],
            'fat' => $fat * self::CALORIES_PER_GRAM['fat'],
        ];
        $total = array_sum($energy);

        if ($total <= 0) {
            return ['carbPercent' => 0, 'proteinPercent' => 0, 'fatPercent' => 0];
        }

        $protein = (int) round($energy['protein'] / $total * 100);
        $fat = (int) round($energy['fat'] / $total * 100);

        return [
            // Carbohydrate absorbs the rounding so the three always sum to 100.
            'carbPercent' => 100 - $protein - $fat,
            'proteinPercent' => $protein,
            'fatPercent' => $fat,
        ];
    }

    private function primaryGoal(User $user): ?GoalType
    {
        $goal = $user->fitnessGoals()->where('status', 'active')
            ->orderByDesc('is_primary')->latest()->first();

        return $goal?->goal_type instanceof GoalType ? $goal->goal_type : GoalType::tryFrom((string) $goal?->goal_type);
    }
}
