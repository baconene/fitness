<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_progress_is_created()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/onboarding/step', [
            'step' => 'Account',
            'data' => ['name' => 'Test User'],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('onboarding_progress', [
            'user_id' => $user->id,
        ]);
    }

    public function test_onboarding_completion_creates_hunter_profile()
    {
        $user = User::factory()->create();

        $progress = $user->onboardingProgress()->create([
            'current_step' => 'Limitations',
            'completed_steps' => [],
            'step_data' => [
                'Account' => ['hunterName' => 'TestHunter'],
            ],
        ]);

        $response = $this->actingAs($user)->post('/onboarding/complete');

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('hunter_profiles', [
            'user_id' => $user->id,
            'codename' => 'TestHunter',
            'rank' => 'E',
            'current_level' => 1,
        ]);

        $this->assertDatabaseHas('hunter_stats', [
            'strength' => 10,
            'endurance' => 10,
            'agility' => 10,
            'vitality' => 10,
            'willpower' => 10,
        ]);
    }
}
