<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\QuestTemplate;
use App\Models\User;
use App\Services\CalendarService;
use App\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M7CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_aggregates_workouts()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        $workoutService = app(WorkoutService::class);
        $workout = $workoutService->startWorkout($user);
        $workout->update(['started_at' => now()]);

        $calendarService = app(CalendarService::class);
        $events = $calendarService->getEventsForRange($user, now()->toDateString(), now()->toDateString());

        $this->assertArrayHasKey(now()->toDateString(), $events);
        $this->assertCount(1, $events[now()->toDateString()]);
        $this->assertEquals('Workout', $events[now()->toDateString()][0]['type']);
    }

    public function test_calendar_aggregates_quests()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        $template = QuestTemplate::create([
            'name' => 'Daily Quest',
            'slug' => 'daily_quest',
            'quest_type' => 'Daily',
            'category' => 'Tasks',
            'difficulty' => 'Easy',
            'target_metric' => 'tasks',
            'target_value' => 1,
            'xp_reward_base' => 10,
        ]);

        $today = now();
        $user->userQuests()->create([
            'quest_template_id' => $template->id,
            'status' => 'Active',
            'assigned_date' => $today,
            'expires_at' => $today->clone()->addDay(),
        ]);

        $calendarService = app(CalendarService::class);
        $events = $calendarService->getEventsForRange($user, $today, $today);

        $dateKey = $today->toDateString();
        $this->assertArrayHasKey($dateKey, $events);
        $questEvents = collect($events[$dateKey])
            ->where('type', 'Quest')
            ->all();
        $this->assertCount(1, $questEvents);
    }

    public function test_get_heatmap_data()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        $workoutService = app(WorkoutService::class);
        $workout = $workoutService->startWorkout($user);
        $workout->update(['started_at' => now()]);

        $calendarService = app(CalendarService::class);
        $heatmap = $calendarService->getHeatmapData($user, now()->year);

        $this->assertArrayHasKey(now()->toDateString(), $heatmap);
        $this->assertEquals(1, $heatmap[now()->toDateString()]);
    }

    public function test_get_agenda_items()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        $template = QuestTemplate::create([
            'name' => 'Test Quest',
            'slug' => 'test_quest',
            'quest_type' => 'Daily',
            'category' => 'Test',
            'difficulty' => 'Easy',
            'target_metric' => 'test',
            'target_value' => 5,
            'xp_reward_base' => 10,
        ]);

        $userQuest = $user->userQuests()->create([
            'quest_template_id' => $template->id,
            'status' => 'Active',
            'assigned_date' => now()->toDateString(),
            'expires_at' => now()->addDay()->toDateString(),
        ]);
        $userQuest->progress()->create(['current_value' => 2]);

        $calendarService = app(CalendarService::class);
        $items = $calendarService->getAgendaItems($user, now()->toDateString());

        $this->assertGreaterThan(0, count($items));
        $questItem = collect($items)->where('type', 'Quest')->first();
        $this->assertNotNull($questItem);
        $this->assertStringContainsString('2/5', $questItem['details']);
    }

    public function test_get_month_view()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        $workoutService = app(WorkoutService::class);
        $workout = $workoutService->startWorkout($user);

        $calendarService = app(CalendarService::class);
        $weeks = $calendarService->getMonth($user, now()->year, now()->month);

        $this->assertIsArray($weeks);
        $this->assertGreaterThan(0, count($weeks));
        $this->assertCount(7, $weeks[0]);
    }

    public function test_calendar_page_renders_with_the_props_the_component_expects(): void
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create([
            'rank' => HunterRank::ERank,
            'awakened_at' => now(),
        ]);
        $profile->stats()->create();

        $response = $this->actingAs($user)->get(route('calendar.show'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Calendar/Index')
                ->has('weeks')
                ->has('weeks.0', 7)
                ->has('weeks.0.0', fn ($day) => $day->hasAll(['date', 'day', 'isCurrentMonth', 'isToday', 'events']))
                ->has('heatmapData')
                ->where('currentYear', now()->year)
                ->where('currentMonth', now()->month)
        );
    }

    public function test_calendar_page_honours_an_explicit_year_and_month(): void
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create([
            'rank' => HunterRank::ERank,
            'awakened_at' => now(),
        ]);
        $profile->stats()->create();

        $response = $this->actingAs($user)->get(route('calendar.show', ['year' => 2025, 'month' => 3]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Calendar/Index')
                ->where('currentYear', '2025')
                ->where('currentMonth', '3')
                ->where('weeks.0.0.date', '2025-02-24')
        );
    }

    public function test_agenda_endpoint_returns_json_for_the_page(): void
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create([
            'rank' => HunterRank::ERank,
            'awakened_at' => now(),
        ]);
        $profile->stats()->create();

        $workout = app(WorkoutService::class)->startWorkout($user);
        $workout->update(['started_at' => now()]);

        $response = $this->actingAs($user)
            ->getJson(route('calendar.agenda', ['date' => now()->toDateString()]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['items' => [['type', 'title', 'icon', 'details']]]);
    }

    public function test_guests_cannot_reach_the_calendar(): void
    {
        $this->get(route('calendar.show'))->assertRedirect(route('login'));
    }
}
