<?php

namespace App\Services;

use App\Models\DungeonRun;
use App\Models\PersonalRecord;
use App\Models\Skill;
use App\Models\Title;
use App\Models\User;

class HunterMilestoneService
{
    /** @return array{eligible: bool, description: string, current: int, target: int} */
    public function skillRequirement(User $user, Skill $skill): array
    {
        $target = match ($skill->slug) {
            'power-surge', 'second-wind', 'quickstep' => 3,
            'ironclad', 'focus' => 5,
            'perfect-form' => 10,
            'recovery-protocol' => 20,
            default => null,
        };

        $current = $user->workouts()->where('status', 'completed')->count();

        return [
            'eligible' => $target !== null && $current >= $target,
            'description' => $target ? "Complete {$target} workouts to unlock." : 'Earned through a future system milestone.',
            'current' => $current,
            'target' => $target ?? 0,
        ];
    }

    public function unlockEarnedTitles(User $user): void
    {
        foreach (Title::where('is_active', true)->get() as $title) {
            if ($this->titleRequirement($user, $title)['eligible']) {
                app(TitleService::class)->unlockTitle($user, $title);
            }
        }
    }

    /** @return array{eligible: bool, description: string} */
    public function titleRequirement(User $user, Title $title): array
    {
        $longestStreak = $user->streak?->longest_streak_days ?? 0;
        $eligible = match ($title->slug) {
            'the-awakened' => $user->hunterProfile?->awakened_at !== null,
            'iron-willed' => $longestStreak >= 7,
            'unbroken' => $longestStreak >= 30,
            'monarch-of-discipline' => $longestStreak >= 365,
            'gate-breaker' => DungeonRun::where('user_id', $user->id)->where('status', 'Completed')->exists(),
            'recordsmith' => PersonalRecord::where('user_id', $user->id)->count() >= 25,
            'dawn-riser' => $user->workouts()->where('status', 'completed')->whereNotNull('started_at')->get()
                ->filter(fn ($workout): bool => $workout->started_at->timezone($user->timezone())->hour < 6)->count() >= 10,
            default => false,
        };

        return ['eligible' => $eligible, 'description' => $title->description ?? 'Earn this title through training milestones.'];
    }
}
