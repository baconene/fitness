<?php

namespace App\Http\Controllers\Onboarding;

use App\Enums\HunterRank;
use App\Http\Controllers\Controller;
use App\Models\HunterProfile;
use App\Models\OnboardingProgress;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        $progress = OnboardingProgress::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_step' => 'Account',
                'completed_steps' => [],
                'step_data' => [],
            ]
        );

        if ($progress->completed_at) {
            return redirect('/dashboard');
        }

        return Inertia::render('Onboarding/Wizard', [
            'progress' => $progress,
        ]);
    }

    public function store()
    {
        $user = auth()->user();
        $progress = OnboardingProgress::where('user_id', $user->id)->first();

        if (! $progress) {
            $progress = OnboardingProgress::create([
                'user_id' => $user->id,
                'current_step' => request('step'),
                'completed_steps' => [],
                'step_data' => [],
            ]);
        }

        $stepData = request('data', []);
        $step = request('step');

        $stepDataArray = is_array($progress->step_data) ? $progress->step_data : [];
        $stepDataArray[$step] = $stepData;

        $completedSteps = is_array($progress->completed_steps) ? $progress->completed_steps : [];
        if (! in_array($step, $completedSteps)) {
            $completedSteps[] = $step;
        }

        $nextStep = $this->getNextStep($step);

        $progress->update([
            'step_data' => $stepDataArray,
            'completed_steps' => $completedSteps,
            'current_step' => $nextStep,
        ]);

        return Inertia::render('Onboarding/Wizard', [
            'progress' => $progress,
        ]);
    }

    public function complete()
    {
        $user = auth()->user();
        $progress = OnboardingProgress::where('user_id', $user->id)->first();

        if (! $progress || $progress->completed_at) {
            return redirect('/dashboard');
        }

        // Check if profile already exists
        $existingProfile = HunterProfile::where('user_id', $user->id)->first();
        if ($existingProfile) {
            $progress->update(['completed_at' => now()]);

            return redirect('/dashboard');
        }

        try {
            DB::transaction(function () use ($user, $progress) {
                $stepData = $progress->step_data ?? [];

                $profile = $user->hunterProfile()->create([
                    'codename' => $stepData['Account']['hunterName'] ?? $user->name,
                    'rank' => HunterRank::ERank,
                    'current_level' => 1,
                    'current_xp' => 0,
                    'total_xp_earned' => 0,
                    'awakened_at' => now(),
                ]);

                $profile->stats()->create([
                    'strength' => 10,
                    'endurance' => 10,
                    'agility' => 10,
                    'vitality' => 10,
                    'willpower' => 10,
                ]);

                $progress->update(['completed_at' => now()]);
            });
        } catch (QueryException $e) {
            // Handle unique constraint violation - profile already exists
            if ($e->errorInfo[1] == 1062 || str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                $progress->update(['completed_at' => now()]);

                return redirect('/dashboard');
            }

            throw $e;
        }

        return redirect('/dashboard');
    }

    private function getNextStep(string $currentStep): string
    {
        $steps = [
            'Account' => 'BodyProfile',
            'BodyProfile' => 'FitnessExperience',
            'FitnessExperience' => 'Goal',
            'Goal' => 'Availability',
            'Availability' => 'Equipment',
            'Equipment' => 'Limitations',
            'Limitations' => 'Awakening',
        ];

        return $steps[$currentStep] ?? 'Awakening';
    }
}
