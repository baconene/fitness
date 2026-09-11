<?php

namespace App\Http\Controllers;

use App\Models\UserQuest;
use App\Services\ExperienceService;
use App\Services\HunterProgressionService;
use App\Services\QuestGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MissionsController extends Controller
{
    public function __construct(private QuestGenerationService $quests) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user->hunterProfile) {
            return redirect()->route('onboarding.show');
        }

        $this->quests->generateDailyQuests($user);
        $this->quests->generateWeeklyQuests($user);
        $this->quests->synchronizeProgress($user);

        return Inertia::render('Missions/Index', [
            'quests' => $user->userQuests()->with('questTemplate', 'progress')->orderByDesc('assigned_date')->orderByDesc('id')->paginate(24)->through(function (UserQuest $quest): array {
                $metric = strtolower(str_replace('_', '', $quest->questTemplate->target_metric));

                return [
                    'id' => $quest->id,
                    'name' => $quest->questTemplate->name,
                    'description' => $quest->questTemplate->description,
                    'type' => $quest->questTemplate->quest_type,
                    'status' => $quest->status,
                    'current' => (int) ($quest->progress?->current_value ?? 0),
                    'target' => (int) $quest->questTemplate->target_value,
                    'xp' => (int) $quest->questTemplate->xp_reward_base,
                    'assignedDate' => $quest->assigned_date->toDateString(),
                    'expiresAt' => $quest->expires_at?->toDateString(),
                    'tracking' => in_array($metric, ['workoutscompleted', 'measurementslogged', 'personalrecords'], true),
                    'actionHref' => route($metric === 'measurementslogged' ? 'health.index' : 'workouts.index'),
                ];
            }),
            'today' => now($user->timezone())->toDateString(),
            'completedCount' => $user->userQuests()->where('status', 'Completed')->count(),
        ]);
    }

    public function claim(Request $request, UserQuest $quest, ExperienceService $experience, HunterProgressionService $progression): RedirectResponse
    {
        abort_unless($quest->user_id === $request->user()->id, 403);
        abort_unless($request->user()->hunterProfile, 403);
        $this->quests->synchronizeProgress($request->user());

        DB::transaction(function () use ($quest, $request, $experience, $progression): void {
            $quest = UserQuest::query()->lockForUpdate()->findOrFail($quest->id);
            if ($quest->status === 'Completed') {
                return;
            }

            $today = now($request->user()->timezone())->toDateString();
            if ($quest->status !== 'Active' || $quest->assigned_date->toDateString() > $today || ($quest->expires_at && $quest->expires_at->toDateString() < $today)
                || ($quest->progress?->current_value ?? 0) < $quest->questTemplate->target_value) {
                throw ValidationException::withMessages(['quest' => 'Complete the recorded objective before claiming this reward.']);
            }

            $this->quests->completeQuest($quest, $experience, $progression);
        });

        return back()->with('success', 'Quest complete. Your Hunter rewards are saved.');
    }
}
