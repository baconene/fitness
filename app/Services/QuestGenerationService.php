<?php

namespace App\Services;

use App\Models\QuestReward;
use App\Models\QuestTemplate;
use App\Models\User;
use App\Models\UserQuest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuestGenerationService
{
    public function generateDailyQuests(User $user, $date = null): array
    {
        $date = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        $dailyTemplates = QuestTemplate::where('quest_type', 'Daily')
            ->where('is_active', true)
            ->get();

        $created = [];

        foreach ($dailyTemplates as $template) {
            try {
                $userQuest = UserQuest::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'quest_template_id' => $template->id,
                        'assigned_date' => $date,
                    ],
                    [
                        'status' => 'Active',
                        'expires_at' => now()->addDay()->toDateString(),
                    ]
                );

                if ($userQuest->wasRecentlyCreated) {
                    $userQuest->progress()->create(['current_value' => 0]);
                    $created[] = $userQuest;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $created;
    }

    public function generateWeeklyQuests(User $user, $date = null): array
    {
        $date = $date ? Carbon::parse($date) : now();
        $startOfWeek = $date->startOfWeek()->toDateString();

        $weeklyTemplates = QuestTemplate::where('quest_type', 'Weekly')
            ->where('is_active', true)
            ->get();

        $created = [];

        foreach ($weeklyTemplates as $template) {
            try {
                $userQuest = UserQuest::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'quest_template_id' => $template->id,
                        'assigned_date' => $startOfWeek,
                    ],
                    [
                        'status' => 'Active',
                        'expires_at' => $date->endOfWeek()->toDateString(),
                    ]
                );

                if ($userQuest->wasRecentlyCreated) {
                    $userQuest->progress()->create(['current_value' => 0]);
                    $created[] = $userQuest;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $created;
    }

    public function completeQuest(UserQuest $userQuest, ExperienceService $experienceService, HunterProgressionService $progressionService): bool
    {
        return DB::transaction(function () use ($userQuest, $experienceService, $progressionService) {
            $userQuest->update([
                'status' => 'Completed',
                'completed_at' => now(),
            ]);

            $template = $userQuest->questTemplate;
            $user = $userQuest->user;
            $profile = $user->hunterProfile;

            $xpKey = "quest:{$userQuest->id}:xp";
            $experienceService->awardXp($profile, $template->xp_reward_base, 'QuestCompletion', $userQuest, $xpKey);

            if ($template->stat_reward) {
                foreach ($template->stat_reward as $stat => $amount) {
                    $progressionService->applyStatChange($profile, $stat, $amount, 'QuestCompletion', $userQuest);
                }
            }

            QuestReward::firstOrCreate(
                [
                    'user_quest_id' => $userQuest->id,
                    'reward_type' => 'Xp',
                ],
                [
                    'reward_payload' => ['amount' => $template->xp_reward_base],
                    'granted_at' => now(),
                ]
            );

            return true;
        });
    }

    public function updateProgress(User $user, string $category, int $increment = 1): void
    {
        $userQuests = $user->userQuests()
            ->where('status', 'Active')
            ->whereHas('questTemplate', fn ($q) => $q->where('category', $category))
            ->with('progress')
            ->get();

        foreach ($userQuests as $userQuest) {
            if ($userQuest->progress) {
                $newValue = $userQuest->progress->current_value + $increment;
                $userQuest->progress->update([
                    'current_value' => $newValue,
                    'last_updated_at' => now(),
                ]);
            }
        }
    }

    public function expireStaleQuests(): int
    {
        return UserQuest::where('status', 'Active')
            ->where('expires_at', '<', now()->toDateString())
            ->update(['status' => 'Expired']);
    }
}
