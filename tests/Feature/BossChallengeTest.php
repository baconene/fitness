<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Boss;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BossChallengeTest extends TestCase
{
    use RefreshDatabase;

    private function hunter(HunterRank $rank = HunterRank::CRank): User
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => $rank, 'awakened_at' => now()]);
        $profile->stats()->create();

        return $user;
    }

    private function boss(array $overrides = []): Boss
    {
        return Boss::create(array_merge([
            'name' => 'Shadow Monarch',
            'slug' => 'shadow-monarch',
            'description' => 'A trial for hunters who have found their footing.',
            'rank_requirement' => 'C',
            'max_health' => 500,
            'xp_reward' => 900,
            'is_active' => true,
        ], $overrides));
    }

    public function test_a_hunter_can_start_a_boss_encounter(): void
    {
        $user = $this->hunter();
        $boss = $this->boss();

        $this->actingAs($user)
            ->post(route('bosses.challenge', $boss))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('boss_encounters', [
            'user_id' => $user->id,
            'boss_id' => $boss->id,
            'status' => 'Active',
            'current_health' => 500,
        ]);
    }

    public function test_a_boss_above_your_rank_is_refused(): void
    {
        $user = $this->hunter(HunterRank::ERank);
        $boss = $this->boss(['rank_requirement' => 'S']);

        $this->actingAs($user)
            ->post(route('bosses.challenge', $boss))
            ->assertSessionHasErrors('boss');

        $this->assertSame(0, $user->bossEncounters()->count());
    }

    public function test_an_inactive_boss_cannot_be_challenged(): void
    {
        $user = $this->hunter();
        $boss = $this->boss(['is_active' => false]);

        $this->actingAs($user)->post(route('bosses.challenge', $boss))->assertNotFound();
    }

    public function test_the_dungeons_page_exposes_bosses_and_the_active_encounter(): void
    {
        $user = $this->hunter();
        $boss = $this->boss();

        $this->actingAs($user)
            ->get(route('dungeons.index'))
            ->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->has('bosses', 1, fn ($listed) => $listed->hasAll(['id', 'name', 'description', 'rank', 'health', 'xp', 'available'])
                    ->where('available', true)
                )->where('encounter', null)
            );

        $this->actingAs($user)->post(route('bosses.challenge', $boss));

        $this->actingAs($user)
            ->get(route('dungeons.index'))
            ->assertInertia(fn ($page) => $page->where('encounter.boss_id', $boss->id)->where('encounter.status', 'Active'));
    }

    public function test_guests_cannot_challenge(): void
    {
        $this->post(route('bosses.challenge', $this->boss()))->assertRedirect(route('login'));
    }
}
