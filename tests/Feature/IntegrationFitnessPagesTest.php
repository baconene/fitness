<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IntegrationFitnessPagesTest extends TestCase
{
    use RefreshDatabase;

    private function awakenedUser(): User
    {
        $user = User::factory()->create();

        $profile = $user->hunterProfile()->create([
            'codename' => 'Nightbreaker',
            'rank' => HunterRank::ERank,
            'current_level' => 1,
            'current_xp' => 0,
            'total_xp_earned' => 0,
            'awakened_at' => now(),
        ]);

        $profile->stats()->create();

        return $user;
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function pageProvider(): array
    {
        return [
            'missions' => ['missions.index', 'Missions/Index'],
            'calendar' => ['calendar.show', 'Calendar/Index'],
            'programs' => ['programs.index', 'Programs/Index'],
            'hunter' => ['hunter.show', 'Hunter/Show'],
            'inventory' => ['inventory.index', 'Inventory/Index'],
            'achievements' => ['achievements.index', 'Achievements/Index'],
            'dungeons' => ['dungeons.index', 'Dungeons/Index'],
            'health' => ['health.index', 'Health/Index'],
            'progress' => ['progress.index', 'Progress/Index'],
            'activity' => ['activity.index', 'Activity/Index'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_page_renders_for_an_awakened_hunter(string $routeName, string $component): void
    {
        $response = $this->actingAs($this->awakenedUser())->get(route($routeName));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component($component));
    }

    public function test_hunter_pages_send_a_new_user_to_onboarding(): void
    {
        $user = User::factory()->create();

        foreach (['hunter.show', 'inventory.index', 'achievements.index', 'dungeons.index'] as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertRedirect(route('onboarding.show'));
        }
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('missions.index'))->assertRedirect(route('login'));
        $this->get(route('health.index'))->assertRedirect(route('login'));
    }
}
