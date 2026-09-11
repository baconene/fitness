<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function evaluateForUser(User $user): array
    {
        $unlocked = [];
        $achievements = Achievement::where('is_active', true)->get();

        foreach ($achievements as $achievement) {
            if ($this->checkCriteria($user, $achievement)) {
                $ua = DB::transaction(function () use ($user, $achievement) {
                    $profile = $user->hunterProfile()->lockForUpdate()->first();
                    $userAchievement = UserAchievement::firstOrCreate(
                        ['user_id' => $user->id, 'achievement_id' => $achievement->id],
                        ['unlocked_at' => now()]
                    );

                    if ($profile && $achievement->xp_reward > 0) {
                        app(ExperienceService::class)->awardXp(
                            $profile,
                            $achievement->xp_reward,
                            'Achievement',
                            $achievement,
                            "achievement:{$user->id}:{$achievement->id}:xp",
                        );
                    }

                    return $userAchievement;
                });

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
