<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\QuestTemplate;
use App\Models\StepLog;
use App\Models\User;
use App\Services\StepTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StepTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function hunter(): User
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank, 'awakened_at' => now()]);
        $profile->stats()->create();

        return $user;
    }

    public function test_a_step_count_can_be_recorded(): void
    {
        $user = $this->hunter();

        $this->actingAs($user)
            ->post(route('health.steps.store'), ['steps' => 8400])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(8400, app(StepTrackingService::class)->stepsOn($user->fresh()));
    }

    /**
     * A step count is a running total for the day, not an event, so logging
     * again replaces it. Adding would double-count a device that reports
     * cumulative figures.
     */
    public function test_recording_again_replaces_the_day_rather_than_adding(): void
    {
        $user = $this->hunter();

        $this->actingAs($user)->post(route('health.steps.store'), ['steps' => 4000]);
        $this->actingAs($user)->post(route('health.steps.store'), ['steps' => 9500]);

        $this->assertSame(9500, app(StepTrackingService::class)->stepsOn($user->fresh()));
        $this->assertSame(1, StepLog::where('user_id', $user->id)->count());
    }

    public function test_an_earlier_day_can_be_filled_in(): void
    {
        $user = $this->hunter();
        $yesterday = now()->subDay()->toDateString();

        $this->actingAs($user)
            ->post(route('health.steps.store'), ['steps' => 12000, 'counted_on' => $yesterday])
            ->assertSessionHasNoErrors();

        $steps = app(StepTrackingService::class);
        $this->assertSame(12000, $steps->stepsOn($user->fresh(), $yesterday));
        $this->assertSame(0, $steps->stepsOn($user->fresh()), 'Today is untouched.');
    }

    public function test_a_future_day_is_refused(): void
    {
        $user = $this->hunter();

        $this->actingAs($user)
            ->post(route('health.steps.store'), ['steps' => 5000, 'counted_on' => now()->addDay()->toDateString()])
            ->assertSessionHasErrors('counted_on');
    }

    public function test_the_count_is_validated(): void
    {
        $user = $this->hunter();

        $this->actingAs($user)->post(route('health.steps.store'), [])->assertSessionHasErrors('steps');
        $this->actingAs($user)->post(route('health.steps.store'), ['steps' => -5])->assertSessionHasErrors('steps');
    }

    /**
     * Regression: the seeded step mission was generated but could never
     * progress, because synchronizeProgress had no case for the metric.
     */
    public function test_logging_steps_advances_the_step_mission(): void
    {
        $user = $this->hunter();

        $template = QuestTemplate::create([
            'name' => 'Reach 8,000 steps',
            'slug' => 'reach-8000-steps',
            'quest_type' => 'Daily',
            'category' => 'Conditioning',
            'difficulty' => 'Medium',
            'target_metric' => 'Steps',
            'target_value' => 8000,
            'xp_reward_base' => 40,
        ]);

        $user->userQuests()->create([
            'quest_template_id' => $template->id,
            'status' => 'Active',
            'assigned_date' => now()->toDateString(),
            'expires_at' => now()->toDateString(),
        ]);

        $this->actingAs($user)->post(route('health.steps.store'), ['steps' => 8600]);

        $this->assertSame(
            8600,
            (int) $user->userQuests()->with('progress')->firstOrFail()->progress->current_value,
        );
    }

    public function test_the_health_page_exposes_the_step_summary(): void
    {
        $user = $this->hunter();
        StepLog::factory()->for($user)->create(['steps' => 9000, 'counted_on' => now()->toDateString()]);

        $this->actingAs($user)
            ->get(route('health.index'))
            ->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->has('steps.series', 7)
                    ->where('steps.today', 9000)
                    ->where('steps.target', 8000)
                    ->where('steps.percent', 100)
            );
    }
}
