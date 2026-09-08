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
}
