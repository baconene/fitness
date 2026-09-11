<?php

namespace App\Http\Controllers;

use App\Models\PersonalRecord;
use App\Services\ExperienceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    public function index(Request $request, ExperienceService $experience): Response
    {
        $user = $request->user();
        $profile = $user->hunterProfile;
        $periodStart = now($user->timezone())->startOfWeek()->subWeeks(11);
        $workouts = $user->workouts()->where('status', 'completed')->where('completed_at', '>=', $periodStart->copy()->utc())->get();
        $weeks = collect(range(0, 11))->map(function (int $offset) use ($periodStart, $workouts, $user): array {
            $start = $periodStart->copy()->addWeeks($offset);
            $end = $start->copy()->endOfWeek();
            $sessions = $workouts->filter(fn ($workout): bool => $workout->completed_at->copy()->timezone($user->timezone())->between($start, $end));

            return [
                'date' => $start->toDateString(),
                'label' => $start->format('M j'),
                'workouts' => $sessions->count(),
                'minutes' => (int) round($sessions->sum(fn ($workout): float => $workout->started_at ? max(0, $workout->started_at->diffInMinutes($workout->completed_at)) : 0)),
                'xp' => (int) $sessions->sum('total_xp_awarded'),
            ];
        });

        return Inertia::render('Progress/Index', [
            'weeks' => $weeks,
            'summary' => [
                'workouts' => $user->workouts()->where('status', 'completed')->count(),
                'quests' => $user->userQuests()->where('status', 'Completed')->count(),
                'xp' => (int) ($profile?->total_xp_earned ?? 0),
                'streak' => (int) ($user->streak?->current_streak_days ?? 0),
            ],
            'records' => PersonalRecord::query()->where('user_id', $user->id)->where('is_current', true)->with('exercise:id,name')->latest('achieved_at')->get(),
            'history' => $user->workouts()->where('status', 'completed')->latest('completed_at')->paginate(10)->through(fn ($workout): array => [
                'id' => $workout->id, 'name' => $workout->name, 'date' => $workout->completed_at->copy()->timezone($user->timezone())->format('M j, Y'),
                'xp' => (int) $workout->total_xp_awarded,
                'minutes' => $workout->started_at ? (int) max(0, $workout->started_at->diffInMinutes($workout->completed_at)) : 0,
            ]),
            'xpLedger' => $profile ? $experience->getLedger($profile, 20)->map(fn ($entry): array => [
                'id' => $entry->id, 'amount' => $entry->amount, 'source' => class_basename($entry->source_type),
                'date' => $entry->awarded_at?->copy()->timezone($user->timezone())->format('M j, H:i'),
            ]) : [],
            'statHistory' => $profile?->statHistory()->latest()->limit(20)->get() ?? [],
        ]);
    }
}
