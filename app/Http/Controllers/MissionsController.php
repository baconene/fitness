<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\User;
use App\Models\UserQuest;
use App\Models\Workout;
use App\Services\ExperienceService;
use App\Services\HunterProgressionService;
use App\Services\QuestGenerationService;
use Illuminate\Database\Eloquent\Builder;
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

        $type = in_array($request->query('type'), ['daily', 'weekly'], true) ? $request->query('type') : 'all';
        $missions = $user->userQuests()->getQuery()->when($type !== 'all', fn (Builder $query): Builder => $query->whereHas(
            'questTemplate', fn (Builder $template): Builder => $template->where('quest_type', ucfirst($type)),
        ));

        return Inertia::render('Missions/Index', [
            'questType' => $type,
            'activeCount' => (clone $missions)->where('status', 'Active')->count(),
            'quests' => (clone $missions)->with('questTemplate', 'progress')->orderByDesc('assigned_date')->orderByDesc('id')->paginate(24)->withQueryString()->through(function (UserQuest $quest): array {
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
            'completedCount' => (clone $missions)->where('status', 'Completed')->count(),
            'upcomingWorkouts' => $this->upcomingWorkouts($user),
            'exerciseOptions' => Exercise::where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'exercise_type', 'primary_muscle']),
        ]);
    }

    /**
     * The next few scheduled sessions, so a hunter can see what a mission actually
     * involves (and adjust the plan behind it) before starting.
     *
     * @return array<int, array<string, mixed>>
     */
    private function upcomingWorkouts(User $user): array
    {
        return $user->workouts()
            ->whereIn('status', ['planned', 'in_progress'])
            ->with(['workoutExercises.exercise', 'workoutExercises.workoutSets', 'trainingProgram'])
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_date')
            ->limit(3)
            ->get()
            ->map(fn (Workout $workout): array => [
                'id' => $workout->id,
                'name' => $workout->name,
                'status' => $workout->status,
                'scheduledDate' => $workout->scheduled_date?->toDateString(),
                'programId' => $workout->training_program_id,
                'programName' => $workout->trainingProgram?->name,
                'setCount' => $workout->workoutExercises->sum(fn ($exercise) => $exercise->workoutSets->count()),
                'exercises' => $workout->workoutExercises->map(fn ($exercise): array => [
                    'id' => $exercise->id,
                    'exerciseId' => $exercise->exercise_id,
                    'name' => $exercise->exercise->name,
                    'sets' => $exercise->workoutSets->count(),
                    'completedSets' => $exercise->workoutSets->where('is_completed', true)->count(),
                ])->all(),
            ])
            ->all();
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
