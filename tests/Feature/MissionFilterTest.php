<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\QuestTemplate;
use App\Models\User;
use App\Models\UserQuest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MissionFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_frequency_filter_applies_before_pagination_and_preserves_links(): void
    {
        $user = User::factory()->create();
        $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $daily = $this->template('Daily');
        $weekly = $this->template('Weekly');
        foreach (range(1, 26) as $day) {
            $this->assignment($user, $weekly, now()->subDays($day)->toDateString());
        }
        $this->assignment($user, $daily, now()->toDateString());
        $other = User::factory()->create();
        $this->assignment($other, $weekly, now()->toDateString());

        $this->actingAs($user)->get(route('missions.index', ['type' => 'weekly']))
            ->assertInertia(fn (Assert $page) => $page->component('Missions/Index')
                ->where('questType', 'weekly')->where('quests.total', 26)->where('activeCount', 26)
                ->has('quests.data', 24)->where('quests.data.0.type', 'Weekly')
                ->where('quests.next_page_url', fn ($url) => str_contains($url, 'type=weekly') && str_contains($url, 'page=2')));
        $this->actingAs($user)->get(route('missions.index', ['type' => 'daily']))
            ->assertInertia(fn (Assert $page) => $page->where('questType', 'daily')->where('quests.total', 1)
                ->where('activeCount', 1)->where('quests.data.0.type', 'Daily'));
        $this->actingAs($user)->get(route('missions.index', ['type' => 'weekly', 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page->has('quests.data', 2)->where('quests.data.0.type', 'Weekly'));
        $this->actingAs($user)->get(route('missions.index', ['type' => 'invalid']))
            ->assertInertia(fn (Assert $page) => $page->where('questType', 'all')->where('quests.total', 27));
    }

    public function test_empty_filter_and_completed_counts_follow_selected_frequency(): void
    {
        $user = User::factory()->create();
        $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $this->assignment($user, $this->template('Daily'), now()->toDateString(), 'Completed');
        $this->actingAs($user)->get(route('missions.index', ['type' => 'weekly']))
            ->assertInertia(fn (Assert $page) => $page->has('quests.data', 0)->where('activeCount', 0)->where('completedCount', 0));
        $this->actingAs($user)->get(route('missions.index', ['type' => 'daily']))
            ->assertInertia(fn (Assert $page) => $page->where('completedCount', 1)->where('activeCount', 0));
    }

    public function test_every_computed_metric_links_to_where_it_is_logged(): void
    {
        $user = User::factory()->create();
        $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $this->assignment($user, $this->template('Daily', 'DistanceKm'), now()->subDay()->toDateString());
        $this->assignment($user, $this->template('Weekly', 'WaterLitres'), now()->subDays(2)->toDateString());
        $this->assignment($user, $this->template('Daily', 'manual', 'uncomputed'), now()->subDays(3)->toDateString());

        $this->actingAs($user)->get(route('missions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('quests.data.0.tracking.href', route('workouts.index'))
                ->where('quests.data.0.tracking.hint', fn (string $hint) => str_contains($hint, 'distance'))
                ->where('quests.data.1.tracking.href', route('health.index'))
                ->where('quests.data.2.tracking', null));
    }

    private function template(string $type, string $metric = 'manual', ?string $slug = null): QuestTemplate
    {
        return QuestTemplate::factory()->create(['name' => $type.' mission', 'slug' => $slug ?? strtolower($type.'-'.$metric),
            'quest_type' => $type, 'category' => 'Test', 'difficulty' => 'Easy', 'target_metric' => $metric,
            'target_value' => 1, 'xp_reward_base' => 10, 'is_active' => false]);
    }

    private function assignment(User $user, QuestTemplate $template, string $date, string $status = 'Active'): UserQuest
    {
        return UserQuest::factory()->create(['user_id' => $user->id, 'quest_template_id' => $template->id,
            'assigned_date' => $date, 'expires_at' => now()->addWeek()->toDateString(), 'status' => $status]);
    }
}
