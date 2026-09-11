<?php

namespace App\Http\Controllers;

use App\Enums\WorkoutStatus;
use App\Models\Achievement;
use App\Models\HunterProfile;
use App\Models\User;
use App\Models\Workout;
use App\Services\AchievementService;
use App\Services\DungeonService;
use App\Services\ExperienceService;
use App\Services\InventoryService;
use App\Services\QuestGenerationService;
use App\Services\RankService;
use App\Services\SkillService;
use App\Services\TitleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Muscle keys the front-end body map can light up.
     */
    private const MUSCLE_KEYS = [
        'chest', 'shoulders', 'biceps', 'triceps', 'forearms',
        'abs', 'obliques', 'upper_back', 'lats', 'lower_back',
        'glutes', 'quads', 'hamstrings', 'calves',
    ];

    public function __construct(
        private ExperienceService $experienceService,
        private RankService $rankService,
        private QuestGenerationService $questGenerationService,
        private AchievementService $achievementService,
        private TitleService $titleService,
        private InventoryService $inventoryService,
        private SkillService $skillService,
        private DungeonService $dungeonService,
    ) {}

    public function show(): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $profile = $user->hunterProfile;

        if (! $profile) {
            return redirect()->route('onboarding.show');
        }

        $this->refreshDerivedState($user);

        $todayWorkout = $this->todayWorkout($user);

        return Inertia::render('Dashboard', [
            'hunter' => $this->hunter($profile),
            'stats' => $this->stats($profile),
            'health' => $this->health($user),
            'todayWorkout' => $this->workoutPayload($todayWorkout),
            'targetMuscles' => $this->targetMuscles($todayWorkout),
            'dailyQuests' => $this->dailyQuests($user),
            'weeklyProgress' => $this->weeklyProgress($user),
            'upcomingWorkouts' => $this->upcomingWorkouts($user),
            'achievements' => $this->achievements($user),
            'systems' => $this->systems($user),
        ]);
    }

    /**
     * Brings lazily-generated state up to date on visit, so the dashboard is
     * correct without depending on the scheduler having run.
     *
     * Both operations are idempotent — quests are unique per
     * (user, template, date) and achievement unlocks per (user, achievement).
     */
    private function refreshDerivedState(User $user): void
    {
        $this->questGenerationService->generateDailyQuests($user);
        $this->questGenerationService->generateWeeklyQuests($user);
        $this->achievementService->evaluateForUser($user);
    }

    /**
     * @return array{name: string, rank: string, level: int, currentXp: int, requiredXp: int, title: ?string}
     */
    private function hunter(HunterProfile $profile): array
    {
        $progress = $this->experienceService->getLevelProgress($profile);
        $activeTitle = $this->titleService->getActiveTitle($profile->user);

        return [
            'name' => $profile->codename ?? 'Hunter',
            'rank' => $profile->rank->value ?? (string) $profile->rank,
            'level' => (int) $profile->current_level,
            'currentXp' => $progress['current'],
            'requiredXp' => $progress['required'],
            'title' => $activeTitle?->title?->name,
        ];
    }

    /**
     * The five attributes, plus progress toward the next rank promotion.
     */
    private function stats(HunterProfile $profile): array
    {
        $stats = $profile->stats;
        $rank = $profile->rank;
        $nextRank = $this->rankService->getNextRank($rank);
        $level = (int) $profile->current_level;

        $nextRankLevel = $nextRank ? $this->rankService->getLevelForRank($nextRank) : null;
        $currentRankLevel = $this->rankService->getLevelForRank($rank);

        return [
            'attributes' => [
                ['key' => 'strength', 'label' => 'STR', 'name' => 'Strength', 'value' => (int) ($stats?->strength ?? 10)],
                ['key' => 'endurance', 'label' => 'END', 'name' => 'Endurance', 'value' => (int) ($stats?->endurance ?? 10)],
                ['key' => 'agility', 'label' => 'AGI', 'name' => 'Agility', 'value' => (int) ($stats?->agility ?? 10)],
                ['key' => 'vitality', 'label' => 'VIT', 'name' => 'Vitality', 'value' => (int) ($stats?->vitality ?? 10)],
                ['key' => 'willpower', 'label' => 'WIL', 'name' => 'Willpower', 'value' => (int) ($stats?->willpower ?? 10)],
            ],
            'pointsAvailable' => (int) ($stats?->stat_points_available ?? 0),
            'nextRank' => $nextRank?->value,
            'nextRankLevel' => $nextRankLevel,
            'rankProgress' => $nextRankLevel && $nextRankLevel > $currentRankLevel
                ? (int) round(
                    max(0, min(1, ($level - $currentRankLevel) / ($nextRankLevel - $currentRankLevel))) * 100
                )
                : 100,
        ];
    }

    private function health(User $user): array
    {
        $measurement = $user->healthMeasurements()->latest('measured_at')->first();

        $bmi = null;
        $category = null;

        if ($measurement && $measurement->height_cm && $measurement->weight_kg) {
            $metres = $measurement->height_cm / 100;
            $bmi = round($measurement->weight_kg / ($metres ** 2), 1);
            $category = $this->bmiCategory($bmi);
        }

        return [
            'bmi' => $bmi,
            'bmiCategory' => $category,
            'healthyBmiRange' => '18.5 - 24.9',
            'heightCm' => $measurement?->height_cm,
            'weightKg' => $measurement?->weight_kg,

            // Nutrition and hydration are not modelled yet — these are display
            // placeholders until the targets are derived from the user profile.
            'calorieTarget' => 2450,
            'carbPercent' => 55,
            'proteinPercent' => 25,
            'fatPercent' => 20,
            'waterConsumed' => 0.0,
            'waterTarget' => 3.0,
        ];
    }

    private function bmiCategory(float $bmi): string
    {
        return match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25.0 => 'Normal',
            $bmi < 30.0 => 'Overweight',
            $bmi < 35.0 => 'Obesity Class I',
            default => 'Obesity Class II',
        };
    }

    private function todayWorkout(User $user): ?Workout
    {
        return $user->workouts()
            ->whereDate('scheduled_date', today())
            ->with('workoutExercises.exercise', 'workoutExercises.workoutSets')
            ->orderBy('id')
            ->first();
    }

    private function workoutPayload(?Workout $workout): array
    {
        if (! $workout) {
            return [
                'name' => 'Rest Day',
                'focus' => 'Recovery',
                'durationMinutes' => 0,
                'exercises' => [],
                'startHref' => route('workouts.index'),
                'startMethod' => 'get',
                'detailsHref' => route('workouts.index'),
            ];
        }

        $exercises = $workout->workoutExercises
            ->sortBy('order')
            ->map(function ($workoutExercise) {
                $sets = $workoutExercise->workoutSets;

                return [
                    'name' => $workoutExercise->exercise?->name ?? 'Exercise',
                    'sets' => $sets->count(),
                    'reps' => (int) ($sets->first()->reps_completed ?? 0),
                    'completed' => $sets->isNotEmpty() && $sets->every(fn ($set) => (bool) $set->is_completed),
                ];
            })
            ->values()
            ->all();

        return [
            'name' => $workout->name,
            'focus' => 'Strength',
            'durationMinutes' => 45,
            'exercises' => $exercises,
            // A planned mission must be started before live mode will accept it.
            'startHref' => $workout->status === 'in_progress'
                ? route('workouts.live.show', $workout)
                : route('workouts.start', $workout),
            'startMethod' => $workout->status === 'in_progress' ? 'get' : 'post',
            'detailsHref' => route('workouts.index'),
        ];
    }

    /**
     * @return array{focusArea: string, primary: list<string>, secondary: list<string>}
     */
    private function targetMuscles(?Workout $workout): array
    {
        if (! $workout) {
            return ['focusArea' => 'Recovery', 'primary' => [], 'secondary' => []];
        }

        $primary = collect();
        $secondary = collect();

        foreach ($workout->workoutExercises as $workoutExercise) {
            $exercise = $workoutExercise->exercise;

            if (! $exercise) {
                continue;
            }

            $primary = $primary->merge($this->muscleKeys($exercise->primary_muscle));
            $secondary = $secondary->merge($this->muscleKeys($exercise->secondary_muscles));
        }

        $primary = $primary->unique()->values();
        $secondary = $secondary->diff($primary)->unique()->values();

        return [
            'focusArea' => $primary->isEmpty()
                ? 'Full Body'
                : ucwords(str_replace('_', ' ', $primary->first())),
            'primary' => $primary->all(),
            'secondary' => $secondary->all(),
        ];
    }

    /**
     * Normalises free-form muscle labels onto the body map's known keys.
     *
     * @return Collection<int, string>
     */
    private function muscleKeys(mixed $value): Collection
    {
        return collect(is_iterable($value) ? iterator_to_array($value) : (array) $value)
            ->filter(fn ($item) => is_string($item) && $item !== '')
            ->map(fn (string $item) => str_replace([' ', '-'], '_', strtolower(trim($item))))
            ->filter(fn (string $key) => in_array($key, self::MUSCLE_KEYS, true))
            ->values();
    }

    private function dailyQuests(User $user): array
    {
        return $user->userQuests()
            ->whereDate('assigned_date', today())
            ->with(['questTemplate', 'progress'])
            ->get()
            ->map(fn ($quest) => [
                'name' => $quest->questTemplate?->name ?? 'Quest',
                'current' => (int) ($quest->progress?->current_value ?? 0),
                'target' => (int) ($quest->questTemplate?->target_value ?? 1),
                'xpReward' => (int) ($quest->questTemplate?->xp_reward_base ?? 0),
            ])
            ->values()
            ->all();
    }

    private function weeklyProgress(User $user): array
    {
        $weekStart = Carbon::now()->startOfWeek();

        $completed = $user->workouts()
            ->where('status', WorkoutStatus::Completed->value)
            ->whereBetween('completed_at', [$weekStart, Carbon::now()])
            ->get();

        $target = (int) ($user->trainingPreference?->days_per_week ?? 5);

        $seconds = $completed->sum(function ($workout) {
            if (! $workout->started_at || ! $workout->completed_at) {
                return 0;
            }

            return $workout->started_at->diffInSeconds($workout->completed_at);
        });

        return [
            'completionPercent' => $target > 0
                ? (int) round(min(100, ($completed->count() / $target) * 100))
                : 0,
            'workoutsCompleted' => $completed->count(),
            'workoutsTarget' => $target,
            'trainingTime' => $this->formatDuration((int) $seconds),
            // Calorie burn is not tracked yet; surfaced as zero rather than invented.
            'caloriesBurned' => 0,
            'streakDays' => (int) ($user->streak?->current_streak_days ?? 0),
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    private function upcomingWorkouts(User $user): array
    {
        return $user->workouts()
            ->whereDate('scheduled_date', '>', today())
            ->orderBy('scheduled_date')
            ->limit(4)
            ->get()
            ->map(fn ($workout) => [
                'date' => $workout->scheduled_date->isTomorrow()
                    ? 'Tomorrow'
                    : $workout->scheduled_date->format('D, M j'),
                'name' => $workout->name,
                'icon' => 'body',
            ])
            ->values()
            ->all();
    }

    /**
     * Recently unlocked achievements plus the closest one still locked.
     */
    private function achievements(User $user): array
    {
        $unlocked = $user->userAchievements()
            ->with('achievement')
            ->orderByDesc('unlocked_at')
            ->limit(4)
            ->get()
            ->map(fn ($record) => [
                'name' => $record->achievement?->name ?? 'Achievement',
                'description' => $record->achievement?->description,
                'xpReward' => (int) ($record->achievement?->xp_reward ?? 0),
                'unlockedAt' => $record->unlocked_at?->diffForHumans(),
            ])
            ->values()
            ->all();

        $unlockedIds = $user->userAchievements()->pluck('achievement_id');

        return [
            'recent' => $unlocked,
            'unlockedCount' => $unlockedIds->count(),
            'totalCount' => Achievement::where('is_active', true)->count(),
            'next' => $this->nextAchievement($user, $unlockedIds->all()),
        ];
    }

    /**
     * The cheapest locked achievement, as a "what to chase next" hint.
     */
    private function nextAchievement(User $user, array $unlockedIds): ?array
    {
        $candidate = Achievement::where('is_active', true)
            ->where('is_hidden', false)
            ->whereNotIn('id', $unlockedIds)
            ->orderBy('criteria_value')
            ->first();

        if (! $candidate) {
            return null;
        }

        $current = match ($candidate->criteria_type) {
            'WorkoutsCompleted' => $user->workouts()->where('status', WorkoutStatus::Completed->value)->count(),
            'QuestsCompleted' => $user->userQuests()->where('status', 'Completed')->count(),
            'TotalXpEarned' => (int) ($user->hunterProfile?->total_xp_earned ?? 0),
            'LevelReached' => (int) ($user->hunterProfile?->current_level ?? 1),
            'BossesDefeated' => $user->bossEncounters()->where('status', 'Defeated')->count(),
            default => 0,
        };

        return [
            'name' => $candidate->name,
            'description' => $candidate->description,
            'current' => $current,
            'target' => (int) $candidate->criteria_value,
        ];
    }

    /**
     * Phase 2 systems, surfaced as a compact status strip.
     */
    private function systems(User $user): array
    {
        $activeRun = $this->dungeonService->getUserActiveRun($user);

        return [
            'inventoryCount' => (int) $this->inventoryService->getInventory($user)->sum('quantity'),
            'skillsLearned' => $this->skillService->getUserSkills($user)->count(),
            'titlesUnlocked' => $this->titleService->getUserTitles($user)->count(),
            'activeDungeon' => $activeRun ? [
                'name' => $activeRun->dungeon?->name ?? 'Dungeon',
                'floor' => (int) $activeRun->current_floor,
                'floorCount' => (int) ($activeRun->dungeon?->floor_count ?? 0),
                'xpEarned' => (int) $activeRun->total_xp_earned,
            ] : null,
        ];
    }
}
