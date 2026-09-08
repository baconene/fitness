<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;

class AchievementService
{
    public function evaluateForUser(User $user): array
    {
        $unlocked = [];
        $achievements = Achievement::where('is_active', true)->get();

        foreach ($achievements as $achievement) {
            if ($this->checkCriteria($user, $achievement)) {
                $ua = UserAchievement::firstOrCreate(
                    ['user_id' => $user->id, 'achievement_id' => $achievement->id],
                    ['unlocked_at' => now()]
                );

                if ($ua->wasRecentlyCreated) {
                    $unlocked[] = $achievement;
                }
            }
        }

        return $unlocked;
    }

    public function checkCriteria(User $user, Achievement $achievement): bool
    {
        return match ($achievement->criteria_type) {
            'WorkoutsCompleted' => $user->workouts()->where('status', 'completed')->count() >= $achievement->criteria_value,
            'QuestsCompleted' => $user->userQuests()->where('status', 'Completed')->count() >= $achievement->criteria_value,
            'TotalXpEarned' => $user->hunterProfile?->total_xp_earned >= $achievement->criteria_value,
            'LevelReached' => $user->hunterProfile?->current_level >= $achievement->criteria_value,
            'BossesDefeated' => $user->bossEncounters()->where('status', 'Defeated')->count() >= $achievement->criteria_value,
            default => false,
        };
    }
}
