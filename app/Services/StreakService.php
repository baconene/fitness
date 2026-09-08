<?php

namespace App\Services;

use App\Models\Streak;
use App\Models\User;

class StreakService
{
    public function recordActivity(User $user): void
    {
        $today = now()->toDateString();

        $streak = $user->streak ?? $user->streak()->create([
            'current_streak_days' => 0,
            'longest_streak_days' => 0,
            'last_activity_date' => null,
        ]);

        $lastActivity = $streak->last_activity_date?->toDateString();

        if ($lastActivity === $today) {
            return;
        }

        if ($lastActivity === now()->subDay()->toDateString()) {
            $streak->increment('current_streak_days');
        } else {
            $streak->update(['current_streak_days' => 1]);
        }

        if ($streak->current_streak_days > $streak->longest_streak_days) {
            $streak->update(['longest_streak_days' => $streak->current_streak_days]);
        }

        $streak->update(['last_activity_date' => $today]);
    }

    public function evaluateAllStreaks(): void
    {
        $yesterday = now()->subDay()->toDateString();
        $today = now()->toDateString();

        $streaks = Streak::where('last_activity_date', '!=', $today)->get();

        foreach ($streaks as $streak) {
            if ($streak->last_activity_date?->toDateString() !== $yesterday) {
                $streak->update(['current_streak_days' => 0]);
            }
        }
    }
}
