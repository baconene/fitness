<?php

namespace App\Services;

use App\Models\ExperienceTransaction;
use App\Models\HunterProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ExperienceService
{
    public function __construct(private HunterProgressionService $progressionService) {}

    public function awardXp(
        HunterProfile $profile,
        int $amount,
        string $sourceType,
        ?Model $source,
        string $idempotencyKey
    ): ExperienceTransaction {
        return DB::transaction(function () use ($profile, $amount, $sourceType, $source, $idempotencyKey) {
            try {
                $transaction = ExperienceTransaction::create([
                    'hunter_profile_id' => $profile->id,
                    'amount' => $amount,
                    'source_type' => $source ? $source::class : $sourceType,
                    'source_id' => $source?->id,
                    'idempotency_key' => $idempotencyKey,
                    'balance_after' => $profile->current_xp + $amount,
                    'awarded_at' => now(),
                ]);
            } catch (QueryException $e) {
                if ($this->isUniqueConstraintViolation($e)) {
                    $transaction = ExperienceTransaction::where('idempotency_key', $idempotencyKey)
                        ->where('hunter_profile_id', $profile->id)
                        ->first();

                    return $transaction;
                }

                throw $e;
            }

            $profile->increment('current_xp', $amount);
            $profile->increment('total_xp_earned', $amount);

            $this->recalculateLevel($profile);

            return $transaction;
        });
    }

    public function recalculateLevel(HunterProfile $profile): int
    {
        $totalXp = $profile->total_xp_earned;
        $newLevel = 1;

        for ($level = 1; $level <= 100; $level++) {
            $requiredXp = $this->calculateRequiredXp($level);
            if ($totalXp >= $requiredXp) {
                $newLevel = $level;
            } else {
                break;
            }
        }

        if ($newLevel !== $profile->current_level) {
            $profile->update(['current_level' => $newLevel]);

            app(HunterProgressionService::class)->evaluateRankPromotion($profile);
        }

        return $newLevel;
    }

    public function getXpToNextLevel(HunterProfile $profile): int
    {
        $currentRequired = $this->calculateRequiredXp($profile->current_level);
        $nextRequired = $this->calculateRequiredXp($profile->current_level + 1);

        $progressInLevel = $profile->total_xp_earned - $currentRequired;
        $xpNeededForLevel = $nextRequired - $currentRequired;

        return max(0, $xpNeededForLevel - $progressInLevel);
    }

    /**
     * Progress within the current level, for XP bar display.
     *
     * @return array{current: int, required: int}
     */
    public function getLevelProgress(HunterProfile $profile): array
    {
        $currentRequired = $this->calculateRequiredXp($profile->current_level);
        $nextRequired = $this->calculateRequiredXp($profile->current_level + 1);

        return [
            'current' => max(0, $profile->total_xp_earned - $currentRequired),
            'required' => max(1, $nextRequired - $currentRequired),
        ];
    }

    public function getLedger(HunterProfile $profile, ?int $limit = null)
    {
        $query = ExperienceTransaction::where('hunter_profile_id', $profile->id)
            ->orderByDesc('awarded_at');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    private function calculateRequiredXp(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }

        return (int) floor(100 * pow($level, 1.35));
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        return str_contains($e->getMessage(), 'UNIQUE constraint failed') ||
               str_contains($e->getMessage(), 'Duplicate entry');
    }
}
