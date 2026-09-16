<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\QuestTemplate;
use App\Models\User;
use App\Models\WaterLog;
use App\Services\HydrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HydrationTest extends TestCase
{
    use RefreshDatabase;

    private function awakenedUser(): User
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'awakened_at' => now()]);
        $profile->stats()->create();

        return $user;
    }

    public function test_a_hunter_can_log_a_drink(): void
    {
        $user = $this->awakenedUser();

        $this->actingAs($user)
            ->post(route('health.water.store'), ['amount_ml' => 500])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('water_logs', ['user_id' => $user->id, 'amount_ml' => 500]);
        $this->assertSame(500, app(HydrationService::class)->totalForDay($user->fresh()));
    }

    public function test_logged_amounts_accumulate_across_the_day(): void
    {
        $user = $this->awakenedUser();

        foreach ([250, 500, 750] as $amount) {
            $this->actingAs($user)->post(route('health.water.store'), ['amount_ml' => $amount]);
        }

        $this->assertSame(1500, app(HydrationService::class)->totalForDay($user->fresh()));
    }

    public function test_an_entry_can_be_removed(): void
    {
        $user = $this->awakenedUser();
        $log = WaterLog::factory()->for($user)->create(['amount_ml' => 500]);

        $this->actingAs($user)
            ->delete(route('health.water.destroy', $log))
            ->assertRedirect();

        $this->assertDatabaseMissing('water_logs', ['id' => $log->id]);
    }

    public function test_a_hunter_cannot_remove_someone_elses_entry(): void
    {
        $owner = $this->awakenedUser();
        $intruder = $this->awakenedUser();
        $log = WaterLog::factory()->for($owner)->create(['amount_ml' => 500]);

        $this->actingAs($intruder)
            ->delete(route('health.water.destroy', $log))
            ->assertForbidden();

        $this->assertDatabaseHas('water_logs', ['id' => $log->id]);
    }

    public function test_the_amount_is_validated(): void
    {
        $user = $this->awakenedUser();

        $this->actingAs($user)->post(route('health.water.store'), ['amount_ml' => 0])
            ->assertSessionHasErrors('amount_ml');

        $this->actingAs($user)->post(route('health.water.store'), ['amount_ml' => 9000])
            ->assertSessionHasErrors('amount_ml');

        $this->assertSame(0, app(HydrationService::class)->totalForDay($user->fresh()));
    }

    public function test_logging_stops_at_the_daily_ceiling(): void
    {
        $user = $this->awakenedUser();
        WaterLog::factory()->for($user)->create(['amount_ml' => 14_800, 'logged_at' => now()]);

        $this->actingAs($user)
            ->post(route('health.water.store'), ['amount_ml' => 500])
            ->assertSessionHasErrors('amount_ml');

        $this->assertSame(14_800, app(HydrationService::class)->totalForDay($user->fresh()));
    }

    public function test_yesterdays_logs_do_not_count_towards_today(): void
    {
        $user = $this->awakenedUser();
        WaterLog::factory()->for($user)->create(['amount_ml' => 900, 'logged_at' => now()->subDay()]);
        WaterLog::factory()->for($user)->create(['amount_ml' => 400, 'logged_at' => now()]);

        $this->assertSame(400, app(HydrationService::class)->totalForDay($user));
    }

    public function test_the_target_scales_with_the_latest_recorded_weight(): void
    {
        $user = $this->awakenedUser();
        $hydration = app(HydrationService::class);

        $this->assertSame(3000, $hydration->targetMl($user), 'Falls back to a flat default with no measurement.');

        $user->healthMeasurements()->create([
            'measured_at' => now()->subDay()->toDateString(),
            'weight_kg' => 60,
            'height_cm' => 170,
        ]);
        $user->healthMeasurements()->create([
            'measured_at' => now()->toDateString(),
            'weight_kg' => 80,
            'height_cm' => 170,
        ]);

        // 80kg * 35ml, rounded to the nearest 50.
        $this->assertSame(2800, $hydration->targetMl($user->fresh()));
    }

    public function test_the_target_is_clamped_for_extreme_weights(): void
    {
        $user = $this->awakenedUser();
        $user->healthMeasurements()->create([
            'measured_at' => now()->toDateString(),
            'weight_kg' => 250,
            'height_cm' => 190,
        ]);

        $this->assertSame(5000, app(HydrationService::class)->targetMl($user->fresh()));
    }

    public function test_the_dashboard_exposes_the_hydration_summary(): void
    {
        $user = $this->awakenedUser();
        WaterLog::factory()->for($user)->create(['amount_ml' => 1500, 'logged_at' => now()]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->has('health.water', fn ($water) => $water
                    ->where('consumedMl', 1500)
                    ->where('consumedLitres', 1.5)
                    ->where('targetMl', 3000)
                    // A whole number of litres serialises as 3, not 3.0.
                    ->where('targetLitres', 3)
                    ->where('percent', 50)
                    ->has('logs', 1)
                )
            );
    }

    public function test_logging_water_advances_the_hydration_quest(): void
    {
        $user = $this->awakenedUser();

        $template = QuestTemplate::create([
            'name' => 'Drink 3L of water',
            'slug' => 'drink_3l_water',
            'quest_type' => 'Daily',
            'category' => 'Health',
            'difficulty' => 'Easy',
            'target_metric' => 'WaterLitres',
            'target_value' => 3,
            'xp_reward_base' => 20,
        ]);

        $user->userQuests()->create([
            'quest_template_id' => $template->id,
            'status' => 'Active',
            'assigned_date' => now()->toDateString(),
            'expires_at' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($user)->post(route('health.water.store'), ['amount_ml' => 2500]);

        $quest = $user->userQuests()->with('progress')->firstOrFail();
        $this->assertSame(2, (int) $quest->progress->current_value, '2500ml counts as 2 whole litres.');

        $this->actingAs($user)->post(route('health.water.store'), ['amount_ml' => 600]);

        $this->assertSame(3, (int) $user->userQuests()->with('progress')->firstOrFail()->progress->current_value);
    }

    public function test_history_covers_every_day_including_the_empty_ones(): void
    {
        $user = $this->awakenedUser();
        WaterLog::factory()->for($user)->create(['amount_ml' => 1500, 'logged_at' => now()]);
        WaterLog::factory()->for($user)->create(['amount_ml' => 3000, 'logged_at' => now()->subDays(2)]);

        $history = app(HydrationService::class)->history($user, 7);

        $this->assertCount(7, $history, 'A gap-free series keeps the chart honest.');
        $this->assertSame(now()->subDays(6)->toDateString(), $history[0]['date'], 'Oldest first.');
        $this->assertSame(now()->toDateString(), $history[6]['date']);
        $this->assertSame(1.5, $history[6]['litres']);
        $this->assertSame(3.0, $history[4]['litres']);
        $this->assertSame(0.0, $history[5]['litres'], 'A day with nothing logged still appears.');
        $this->assertTrue($history[4]['met'], '3L meets the default 3L target.');
        $this->assertFalse($history[6]['met']);
    }

    public function test_history_summary_averages_across_the_whole_window(): void
    {
        $user = $this->awakenedUser();
        WaterLog::factory()->for($user)->create(['amount_ml' => 3500, 'logged_at' => now()]);

        $summary = app(HydrationService::class)->historySummary($user, 7);

        $this->assertSame(7, $summary['days']);
        $this->assertSame(1, $summary['daysMet']);
        $this->assertSame(3.0, $summary['targetLitres']);
        // 3.5L over seven days, six of them empty.
        $this->assertSame(0.5, $summary['averageLitres']);
    }

    public function test_the_health_page_exposes_the_hydration_trend(): void
    {
        $user = $this->awakenedUser();
        WaterLog::factory()->for($user)->create(['amount_ml' => 1000, 'logged_at' => now()]);

        $this->actingAs($user)
            ->get(route('health.index'))
            ->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->has('hydration.history.series', 7)
                    ->where('hydration.consumedLitres', 1)
                    ->where('hydration.history.days', 7)
                    ->has('hydration.history.averageLitres')
                    ->has('hydration.history.daysMet')
            );
    }
}
