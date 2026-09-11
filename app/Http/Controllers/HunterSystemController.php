<?php

namespace App\Http\Controllers;

use App\Enums\HunterRank;
use App\Enums\StatChangeReason;
use App\Enums\StatType;
use App\Models\Achievement;
use App\Models\Boss;
use App\Models\Dungeon;
use App\Models\DungeonRun;
use App\Models\HunterProfile;
use App\Models\Item;
use App\Models\Skill;
use App\Models\Title;
use App\Models\User;
use App\Models\UserSkill;
use App\Services\AchievementService;
use App\Services\BossBattleService;
use App\Services\DungeonService;
use App\Services\ExperienceService;
use App\Services\HunterMilestoneService;
use App\Services\HunterProgressionService;
use App\Services\InventoryService;
use App\Services\RankService;
use App\Services\SkillService;
use App\Services\TitleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class HunterSystemController extends Controller
{
    public function show(Request $request, ExperienceService $experience, RankService $ranks, HunterMilestoneService $milestones): Response|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->hunterProfile;
        if (! $profile) {
            return redirect()->route('onboarding.show');
        }

        $milestones->unlockEarnedTitles($user);
        $ownedSkills = UserSkill::where('user_id', $user->id)->get()->keyBy('skill_id');
        $ownedTitles = app(TitleService::class)->getUserTitles($user)->keyBy('title_id');
        $nextRank = $ranks->getNextRank($profile->rank);

        return Inertia::render('Hunter/Show', [
            'hunter' => [
                'codename' => $profile->codename ?? $user->name,
                'level' => $profile->current_level,
                'rank' => $profile->rank->value,
                'totalXp' => $profile->total_xp_earned,
                'progress' => $experience->getLevelProgress($profile),
                'nextRank' => $nextRank?->value,
                'nextRankLevel' => $nextRank ? $ranks->getLevelForRank($nextRank) : null,
                'stats' => $profile->stats,
                'workouts' => $user->workouts()->where('status', 'completed')->count(),
                'quests' => $user->userQuests()->where('status', 'Completed')->count(),
                'bosses' => $user->bossEncounters()->where('status', 'Defeated')->count(),
            ],
            'skills' => Skill::where('is_active', true)->get()->map(fn (Skill $skill): array => [
                'id' => $skill->id, 'name' => $skill->name, 'description' => $skill->description,
                'level' => $ownedSkills->get($skill->id)?->level ?? 0,
                'experience' => $ownedSkills->get($skill->id)?->experience ?? 0,
                'maxLevel' => $skill->max_level,
                'requirement' => $milestones->skillRequirement($user, $skill),
            ]),
            'titles' => Title::where('is_active', true)->get()->map(fn (Title $title): array => [
                'id' => $title->id, 'name' => $title->name, 'description' => $title->description,
                'rarity' => $title->rarity, 'owned' => $ownedTitles->has($title->id),
                'equipped' => (bool) $ownedTitles->get($title->id)?->is_active,
            ]),
            'ledger' => $experience->getLedger($profile, 12)->map(fn ($entry): array => [
                'id' => $entry->id, 'amount' => $entry->amount,
                'source' => class_basename($entry->source_type),
                'date' => $entry->awarded_at?->toIso8601String(),
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profile($request->user());
        $validated = $request->validate(['codename' => ['required', 'string', 'max:30']]);
        $profile->update($validated);

        return back()->with('success', 'Hunter identity updated.');
    }

    public function allocateStat(Request $request, HunterProgressionService $progression): RedirectResponse
    {
        $validated = $request->validate(['stat' => ['required', Rule::enum(StatType::class)]]);
        $profile = $this->profile($request->user());
        DB::transaction(function () use ($profile, $validated, $progression): void {
            $stats = $profile->stats()->lockForUpdate()->first();
            if (! $stats || $stats->stat_points_available < 1) {
                throw ValidationException::withMessages(['stat' => 'No attribute points available. Keep completing missions.']);
            }
            $profile->setRelation('stats', $stats);
            $stats->decrement('stat_points_available');
            $progression->applyStatChange($profile, StatType::from($validated['stat']), 1, StatChangeReason::ManualAdjustment);
        });

        return back()->with('success', 'Attribute increased.');
    }

    public function learnSkill(Request $request, Skill $skill, HunterMilestoneService $milestones, SkillService $skills): RedirectResponse
    {
        $this->profile($request->user());
        abort_unless($skill->is_active, 404);
        if (! $milestones->skillRequirement($request->user(), $skill)['eligible']) {
            throw ValidationException::withMessages(['skill' => 'Complete the required training milestone first.']);
        }
        $skills->learnSkill($request->user(), $skill);

        return back()->with('success', 'Skill unlocked. Your training made this possible.');
    }

    public function equipTitle(Request $request, Title $title, TitleService $titles, HunterMilestoneService $milestones): RedirectResponse
    {
        $this->profile($request->user());
        abort_unless($title->is_active, 404);
        $milestones->unlockEarnedTitles($request->user());
        abort_unless($titles->hasTitle($request->user(), $title), 404);
        $titles->activateTitle($request->user(), $title);

        return back()->with('success', 'Title equipped.');
    }

    public function inventory(Request $request, InventoryService $inventory): Response|RedirectResponse
    {
        $profile = $request->user()->hunterProfile;
        if (! $profile) {
            return redirect()->route('onboarding.show');
        }
        $equipped = $profile->avatar_meta['equipped_items'] ?? [];

        return Inertia::render('Inventory/Index', [
            'items' => $inventory->getInventory($request->user())->filter(fn ($entry): bool => $entry->quantity > 0 && $entry->item !== null)
                ->map(fn ($entry): array => [
                    'id' => $entry->item->id, 'name' => $entry->item->name, 'description' => $entry->item->description,
                    'rarity' => $entry->item->rarity, 'quantity' => $entry->quantity,
                    'slot' => $entry->item->equip_slot,
                    'equippable' => $entry->item->is_equippable && $entry->item->is_active,
                    'equipped' => (int) ($equipped[$entry->item->equip_slot] ?? 0) === $entry->item->id,
                ])->values(),
        ]);
    }

    public function equipItem(Request $request, Item $item, InventoryService $inventory): RedirectResponse
    {
        $this->profile($request->user());
        abort_unless($inventory->equipItem($request->user(), $item), 404);

        return back()->with('success', 'Equipment loadout updated.');
    }

    public function unequipItem(Request $request, Item $item, InventoryService $inventory): RedirectResponse
    {
        $this->profile($request->user());
        abort_unless($inventory->unequipItem($request->user(), $item), 404);

        return back()->with('success', 'Item unequipped.');
    }

    public function achievements(Request $request, AchievementService $achievements): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user->hunterProfile) {
            return redirect()->route('onboarding.show');
        }
        $achievements->evaluateForUser($user);
        $owned = $user->userAchievements()->get()->keyBy('achievement_id');

        return Inertia::render('Achievements/Index', [
            'achievements' => Achievement::where('is_active', true)->get()
                ->filter(fn (Achievement $achievement): bool => ! $achievement->is_hidden || $owned->has($achievement->id))
                ->map(fn (Achievement $achievement): array => [
                    'id' => $achievement->id, 'name' => $achievement->name,
                    'description' => $achievement->description, 'category' => $achievement->category,
                    'xp' => $achievement->xp_reward, 'unlocked' => $owned->has($achievement->id),
                    'unlockedAt' => $owned->get($achievement->id)?->unlocked_at?->toIso8601String(),
                ])->values(),
        ]);
    }

    public function dungeons(Request $request, DungeonService $dungeons): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user->hunterProfile) {
            return redirect()->route('onboarding.show');
        }

        return Inertia::render('Dungeons/Index', [
            'dungeons' => Dungeon::where('is_active', true)->withCount('floors')->get()->map(fn (Dungeon $dungeon): array => [
                'id' => $dungeon->id, 'name' => $dungeon->name, 'description' => $dungeon->description,
                'rank' => $dungeon->rank_requirement, 'floors' => $dungeon->floor_count,
                'difficulty' => $dungeon->difficulty,
                'available' => $this->meetsRank($user, $dungeon->rank_requirement) && $dungeon->entry_fee === 0 && $dungeon->floors_count === $dungeon->floor_count,
                'paidEntry' => $dungeon->entry_fee > 0,
            ]),
            'activeRun' => $dungeons->getUserActiveRun($user)?->load('dungeon.floors'),
            'runs' => DungeonRun::where('user_id', $user->id)->where('status', '!=', 'Active')->with('dungeon')->latest('started_at')->limit(10)->get(),
            'bosses' => Boss::where('is_active', true)->get()->map(fn (Boss $boss): array => [
                'id' => $boss->id, 'name' => $boss->name, 'description' => $boss->description,
                'rank' => $boss->rank_requirement, 'health' => $boss->max_health, 'xp' => $boss->xp_reward,
                'available' => $this->meetsRank($user, $boss->rank_requirement),
            ]),
            'encounter' => $user->bossEncounters()->where('status', 'Active')->with('boss')->first(),
        ]);
    }

    public function enterDungeon(Request $request, Dungeon $dungeon, DungeonService $dungeons): RedirectResponse
    {
        $this->profile($request->user());
        abort_unless($dungeon->is_active, 404);
        if (! $this->meetsRank($request->user(), $dungeon->rank_requirement) || $dungeon->entry_fee > 0 || $dungeon->floors()->count() !== $dungeon->floor_count) {
            throw ValidationException::withMessages(['dungeon' => 'This gate is unavailable at your current rank or does not have a complete free-entry mission.']);
        }
        $dungeons->startRun($request->user(), $dungeon);

        return back()->with('success', 'Gate entered. Complete a workout to clear the next floor.');
    }

    public function abandonDungeon(Request $request, DungeonRun $run, DungeonService $dungeons): RedirectResponse
    {
        abort_unless($run->user_id === $request->user()->id, 404);
        $dungeons->abandonRun($run);

        return back()->with('success', 'You left the gate. Your earned progress is safe.');
    }

    public function challengeBoss(Request $request, Boss $boss, BossBattleService $battles): RedirectResponse
    {
        $this->profile($request->user());
        abort_unless($boss->is_active, 404);
        if (! $this->meetsRank($request->user(), $boss->rank_requirement)) {
            throw ValidationException::withMessages(['boss' => 'Reach the required hunter rank to challenge this boss.']);
        }
        $battles->startEncounter($request->user(), $boss);

        return back()->with('success', 'Boss challenge active. Your next completed workouts will deal damage.');
    }

    private function profile(User $user): HunterProfile
    {
        return $user->hunterProfile()->firstOrFail();
    }

    private function meetsRank(User $user, string $requirement): bool
    {
        $rank = HunterRank::tryFrom($requirement);

        return $rank !== null && $user->hunterProfile->rank->sortOrder() >= $rank->sortOrder();
    }
}
