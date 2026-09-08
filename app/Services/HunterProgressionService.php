<?php

namespace App\Services;

use App\Enums\StatChangeReason;
use App\Enums\StatType;
use App\Models\HunterProfile;
use App\Models\HunterStatHistory;
use Illuminate\Database\Eloquent\Model;

class HunterProgressionService
{
    public function __construct(private RankService $rankService) {}

    public function applyStatChange(
        HunterProfile $profile,
        StatType $stat,
        int $delta,
        StatChangeReason $reason,
        ?Model $source = null
    ): HunterStatHistory {
        $stats = $profile->stats;
        $statColumn = $stat->value;

        $previousValue = $stats->{$statColumn};
        $newValue = max(0, $previousValue + $delta);

        $stats->update([$statColumn => $newValue]);

        return HunterStatHistory::create([
            'hunter_profile_id' => $profile->id,
            'stat' => $stat,
            'previous_value' => $previousValue,
            'new_value' => $newValue,
            'delta' => $newValue - $previousValue,
            'reason' => $reason,
            'source_type' => $source ? $source::class : null,
            'source_id' => $source?->id,
        ]);
    }

    public function evaluateRankPromotion(HunterProfile $profile): ?string
    {
        $newRank = $this->rankService->getRankForLevel($profile->current_level);

        if ($newRank->value !== $profile->rank->value) {
            $profile->update(['rank' => $newRank]);

            return $newRank->value;
        }

        return null;
    }

    public function getCurrentStats(HunterProfile $profile)
    {
        return $profile->stats;
    }
}
