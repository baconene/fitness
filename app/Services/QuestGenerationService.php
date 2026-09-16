<?php

namespace App\Services;

use App\Enums\StatChangeReason;
use App\Enums\StatType;
use App\Models\PersonalRecord;
use App\Models\QuestReward;
use App\Models\QuestTemplate;
use App\Models\User;
use App\Models\UserQuest;
use App\Models\WorkoutSet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class QuestGenerationService
{
    public function generateDailyQuests(User $user, $date = null): array
    {
        $date = $date ? Carbon::parse($date)->toDateString() : now($user->timezone())->toDateString();

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
                        'expires_at' => $date,
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
        $date = $date ? Carbon::parse($date) : now($user->timezone());
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
            $userQuest = UserQuest::query()->lockForUpdate()->findOrFail($userQuest->id);

            if ($userQuest->status === 'Completed') {
                return true;
            }

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
                    $statType = StatType::tryFrom($stat);
                    if ($statType) {
                        $progressionService->applyStatChange($profile, $statType, (int) $amount, StatChangeReason::QuestReward, $userQuest);
                    }
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
            ->whereDate('assigned_date', '<=', now($user->timezone())->toDateString())
            ->whereDate('expires_at', '>=', now($user->timezone())->toDateString())
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

    public function synchronizeProgress(User $user): void
    {
        $today = now($user->timezone())->toDateString();
        $user->userQuests()->where('status', 'Active')->whereDate('expires_at', '<', $today)->update(['status' => 'Expired']);

        $quests = $user->userQuests()->where('status', 'Active')->whereDate('assigned_date', '<=', $today)
            ->with('questTemplate')->get();

        foreach ($quests as $quest) {
            $start = Carbon::parse($quest->assigned_date->toDateString(), $user->timezone())->startOfDay()->utc();
            $end = Carbon::parse(($quest->expires_at ?? now())->toDateString(), $user->timezone())->endOfDay()->utc();
            $metric = strtolower(str_replace('_', '', $quest->questTemplate->target_metric));
            $value = match ($metric) {
                'workoutscompleted' => $user->workouts()->where('status', 'completed')->whereBetween('completed_at', [$start, $end])->count(),
                'measurementslogged' => $user->healthMeasurements()->whereBetween('measured_at', [$quest->assigned_date->toDateString(), ($quest->expires_at ?? now())->toDateString()])->distinct()->count('measured_at'),
                'personalrecords' => PersonalRecord::query()->where('user_id', $user->id)->whereBetween('achieved_at', [$start, $end])->count(),
                'waterlitres' => (int) floor($user->waterLogs()->whereBetween('logged_at', [$start, $end])->sum('amount_ml') / 1000),
                'activedays' => $user->workouts()->where('status', 'completed')
                    ->whereBetween('completed_at', [$start, $end])
                    ->selectRaw('date(completed_at) d')->distinct()->get()->count(),
                'trainingminutes' => (int) floor($this->completedSets($user, $start, $end)->sum('duration_seconds') / 60),
                'distancekm' => (int) floor($this->completedSets($user, $start, $end)->sum('distance_km')),
                'steps' => app(StepTrackingService::class)->stepsBetween(
                    $user,
                    $quest->assigned_date->toDateString(),
                    ($quest->expires_at ?? now())->toDateString(),
                ),
                default => null,
            };

            if ($value !== null) {
                $quest->progress()->updateOrCreate([], ['current_value' => $value, 'last_updated_at' => now()]);
            }
        }
    }

    /**
     * Completed sets belonging to the user, inside a window. Used by the
     * duration and distance metrics.
     *
     * @return Builder<WorkoutSet>
     */
    private function completedSets(User $user, Carbon $start, Carbon $end)
    {
        return WorkoutSet::query()
            ->where('is_completed', true)
            ->whereBetween('completed_at', [$start, $end])
            ->whereHas('workoutExercise.workout', fn ($query) => $query->where('user_id', $user->id));
    }

    public function expireStaleQuests(): int
    {
        return UserQuest::where('status', 'Active')
            ->where('expires_at', '<', now()->toDateString())
            ->update(['status' => 'Expired']);
    }
}
