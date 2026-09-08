<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\QuestTemplate;
use App\Models\User;
use App\Services\ExperienceService;
use App\Services\HunterProgressionService;
use App\Services\QuestGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M6QuestSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_daily_quests()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        QuestTemplate::create([
            'name' => 'Complete 5 Workouts',
            'slug' => 'complete_5_workouts',
            'quest_type' => 'Daily',
            'category' => 'Workouts',
            'difficulty' => 'Medium',
            'target_metric' => 'workouts_completed',
            'target_value' => 5,
            'xp_reward_base' => 50,
        ]);

        $service = app(QuestGenerationService::class);
        $quests = $service->generateDailyQuests($user);

        $this->assertCount(1, $quests);
        $this->assertDatabaseHas('user_quests', [
            'user_id' => $user->id,
            'status' => 'Active',
        ]);
        $this->assertDatabaseHas('quest_progress', [
            'current_value' => 0,
        ]);
    }

    public function test_daily_quests_are_idempotent()
    {
        $user = User::factory()->create();
        $user->hunterProfile()->create(['rank' => HunterRank::ERank]);

        QuestTemplate::create([
            'name' => 'Test Quest',
            'slug' => 'test_quest',
            'quest_type' => 'Daily',
            'category' => 'Test',
            'difficulty' => 'Easy',
            'target_metric' => 'test',
            'target_value' => 1,
            'xp_reward_base' => 10,
        ]);

        $service = app(QuestGenerationService::class);
        $first = $service->generateDailyQuests($user);
        $second = $service->generateDailyQuests($user);

        $this->assertCount(1, $first);
        $this->assertCount(0, $second);
    }

    public function test_complete_quest_awards_xp()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create([
            'rank' => HunterRank::ERank,
            'current_xp' => 0,
            'total_xp_earned' => 0,
        ]);
        $profile->stats()->create();

        $template = QuestTemplate::create([
            'name' => 'Daily Task',
            'slug' => 'daily_task',
            'quest_type' => 'Daily',
            'category' => 'Tasks',
            'difficulty' => 'Easy',
            'target_metric' => 'tasks',
            'target_value' => 1,
            'xp_reward_base' => 50,
        ]);

        $userQuest = $user->userQuests()->create([
            'quest_template_id' => $template->id,
            'status' => 'Active',
            'assigned_date' => now()->toDateString(),
            'expires_at' => now()->addDay()->toDateString(),
        ]);
        $userQuest->progress()->create(['current_value' => 0]);

        $questService = app(QuestGenerationService::class);
        $experienceService = app(ExperienceService::class);
        $progressionService = app(HunterProgressionService::class);

        $questService->completeQuest($userQuest, $experienceService, $progressionService);

        $profile->refresh();
        $this->assertGreaterThan(0, $profile->current_xp);
        $this->assertDatabaseHas('quest_rewards', [
            'user_quest_id' => $userQuest->id,
            'reward_type' => 'Xp',
        ]);
    }

    public function test_quest_reward_is_idempotent()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        $template = QuestTemplate::create([
            'name' => 'Idempotent Quest',
            'slug' => 'idempotent_quest',
            'quest_type' => 'OneTime',
            'category' => 'Test',
            'difficulty' => 'Easy',
            'target_metric' => 'test',
            'target_value' => 1,
            'xp_reward_base' => 100,
        ]);

        $userQuest = $user->userQuests()->create([
            'quest_template_id' => $template->id,
            'status' => 'Active',
            'assigned_date' => now()->toDateString(),
            'expires_at' => now()->addDay()->toDateString(),
        ]);
        $userQuest->progress()->create();

        $questService = app(QuestGenerationService::class);
        $experienceService = app(ExperienceService::class);
        $progressionService = app(HunterProgressionService::class);

        $questService->completeQuest($userQuest, $experienceService, $progressionService);
        $xpAfterFirst = $user->hunterProfile->fresh()->current_xp;

        $questService->completeQuest($userQuest, $experienceService, $progressionService);
        $xpAfterSecond = $user->hunterProfile->fresh()->current_xp;

        $this->assertEquals($xpAfterFirst, $xpAfterSecond);
        $this->assertEquals(1, $user->userQuests()->first()->rewards()->count());
    }

    public function test_expire_stale_quests()
    {
        $user = User::factory()->create();
        $user->hunterProfile()->create(['rank' => HunterRank::ERank]);

        $template = QuestTemplate::create([
            'name' => 'Stale Quest',
            'slug' => 'stale_quest',
            'quest_type' => 'Daily',
            'category' => 'Test',
            'difficulty' => 'Easy',
            'target_metric' => 'test',
            'target_value' => 1,
            'xp_reward_base' => 10,
        ]);

        $staleQuest = $user->userQuests()->create([
            'quest_template_id' => $template->id,
            'status' => 'Active',
            'assigned_date' => now()->subDays(3)->toDateString(),
            'expires_at' => now()->subDay()->toDateString(),
        ]);

        $service = app(QuestGenerationService::class);
        $expired = $service->expireStaleQuests();

        $this->assertGreaterThan(0, $expired);
        $this->assertEquals('Expired', $staleQuest->fresh()->status);
    }
}
