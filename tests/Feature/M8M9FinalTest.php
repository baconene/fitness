<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Achievement;
use App\Models\Boss;
use App\Models\User;
use App\Services\AchievementService;
use App\Services\BossBattleService;
use App\Services\StreakService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M8M9FinalTest extends TestCase
{
    use RefreshDatabase;

    public function test_achievement_unlocks_on_criteria_met()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'total_xp_earned' => 0]);
        $profile->stats()->create();

        Achievement::create([
            'name' => 'First Steps',
            'slug' => 'first_steps',
            'category' => 'Beginner',
            'criteria_type' => 'TotalXpEarned',
            'criteria_value' => 100,
            'xp_reward' => 0,
            'is_active' => true,
        ]);

        $profile->update(['total_xp_earned' => 100]);

        $service = app(AchievementService::class);
        $unlocked = $service->evaluateForUser($user);

        $this->assertCount(1, $unlocked);
        $this->assertDatabaseHas('user_achievements', ['user_id' => $user->id]);
    }

    public function test_achievement_unlock_is_idempotent()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'total_xp_earned' => 200]);
        $profile->stats()->create();

        Achievement::create([
            'name' => 'Experienced',
            'slug' => 'experienced',
            'category' => 'Intermediate',
            'criteria_type' => 'TotalXpEarned',
            'criteria_value' => 100,
            'xp_reward' => 0,
            'is_active' => true,
        ]);

        $service = app(AchievementService::class);
        $first = $service->evaluateForUser($user);
        $second = $service->evaluateForUser($user);

        $this->assertCount(1, $first);
        $this->assertCount(0, $second);
        $this->assertEquals(1, $user->userAchievements()->count());
    }

    public function test_boss_encounter_tracks_damage()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        $boss = Boss::create([
            'name' => 'Training Dummy',
            'slug' => 'training_dummy',
            'rank_requirement' => 'E',
            'max_health' => 100,
            'xp_reward' => 50,
            'is_active' => true,
        ]);

        $service = app(BossBattleService::class);
        $encounter = $service->startEncounter($user, $boss);

        $this->assertEquals('Active', $encounter->status);
        $this->assertEquals(100, $encounter->current_health);

        $newHealth = $service->applyDamage($encounter, 30, 'attack-1');
        $this->assertEquals(70, $newHealth);
    }

    public function test_boss_damage_is_idempotent()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        $boss = Boss::create([
            'name' => 'Test Boss',
            'slug' => 'test_boss',
            'rank_requirement' => 'E',
            'max_health' => 100,
            'xp_reward' => 50,
            'is_active' => true,
        ]);

        $service = app(BossBattleService::class);
        $encounter = $service->startEncounter($user, $boss);

        $health1 = $service->applyDamage($encounter, 25, 'same-key');
        $health2 = $service->applyDamage($encounter, 25, 'same-key');

        $this->assertEquals($health1, $health2);
        $this->assertEquals(75, $health1);
        $this->assertEquals(1, $encounter->damageEvents()->count());
    }

    public function test_boss_defeat_awards_xp()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create([
            'rank' => HunterRank::ERank,
            'current_xp' => 0,
            'total_xp_earned' => 0,
        ]);
        $profile->stats()->create();

        $boss = Boss::create([
            'name' => 'Weak Boss',
            'slug' => 'weak_boss',
            'rank_requirement' => 'E',
            'max_health' => 50,
            'xp_reward' => 100,
            'is_active' => true,
        ]);

        $service = app(BossBattleService::class);
        $encounter = $service->startEncounter($user, $boss);
        $service->applyDamage($encounter, 50, 'finishing-blow');

        $encounter->refresh();
        $this->assertEquals('Defeated', $encounter->status);

        $profile->refresh();
        $this->assertGreaterThan(0, $profile->current_xp);
    }

    public function test_streak_tracks_activity()
    {
        $user = User::factory()->create();
        $user->hunterProfile()->create(['rank' => HunterRank::ERank]);

        $service = app(StreakService::class);
        $service->recordActivity($user);

        $streak = $user->fresh()->streak;
        $this->assertNotNull($streak);
        $this->assertEquals(1, $streak->current_streak_days);
        $this->assertEquals(now()->toDateString(), $streak->last_activity_date->toDateString());
    }

    public function test_streak_preserves_on_daily_activity()
    {
        $user = User::factory()->create();
        $user->hunterProfile()->create(['rank' => HunterRank::ERank]);

        $service = app(StreakService::class);
        $service->recordActivity($user);

        $streak = $user->refresh()->streak;
        $this->assertEquals(1, $streak->current_streak_days);

        $service->recordActivity($user);
        $streak->refresh();
        $this->assertEquals(1, $streak->current_streak_days);
    }
}
