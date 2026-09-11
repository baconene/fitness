<?php

namespace App\Services;

use App\Enums\HunterRank;
use App\Models\HunterProfile;

class RankService
{
    private array $rankThresholds = [
        1 => HunterRank::ERank,
        10 => HunterRank::DRank,
        20 => HunterRank::CRank,
        35 => HunterRank::BRank,
        55 => HunterRank::ARank,
        80 => HunterRank::SRank,
    ];

    public function getRankForLevel(int $level): HunterRank
    {
        $rank = HunterRank::ERank;

        foreach ($this->rankThresholds as $levelThreshold => $rankEnum) {
            if ($level >= $levelThreshold) {
                $rank = $rankEnum;
            }
        }

        return $rank;
    }

    public function isEligibleForRank(HunterProfile $profile, HunterRank $rank): bool
    {
        foreach ($this->rankThresholds as $levelThreshold => $rankEnum) {
            if ($rankEnum === $rank) {
                return $profile->current_level >= $levelThreshold;
            }
        }

        return false;
    }

    /**
     * The level at which a rank unlocks, for "next promotion" display.
     */
    public function getLevelForRank(HunterRank $rank): int
    {
        foreach ($this->rankThresholds as $levelThreshold => $rankEnum) {
            if ($rankEnum === $rank) {
                return $levelThreshold;
            }
        }

        return 1;
    }

    public function getNextRank(HunterRank $current): ?HunterRank
    {
        $ranks = [
            HunterRank::ERank,
            HunterRank::DRank,
            HunterRank::CRank,
            HunterRank::BRank,
            HunterRank::ARank,
            HunterRank::SRank,
        ];

        $currentIndex = array_search($current, $ranks);

        return $ranks[$currentIndex + 1] ?? null;
    }
}
