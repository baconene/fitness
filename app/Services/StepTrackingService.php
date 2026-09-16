<?php

namespace App\Services;

use App\Models\StepLog;
use App\Models\User;
use Carbon\Carbon;

/**
 * Daily step counts, entered by hand.
 *
 * There is no wearable integration, so a step count is whatever the hunter
 * reports for a day. It is a running total rather than a stream of events, so
 * logging again for the same day replaces the figure instead of adding to it.
 *
 * Every lookup goes through whereDate: the column is cast to a date but stored
 * with a midnight time, so comparing it against a plain Y-m-d string never
 * matches.
 */
class StepTrackingService
{
    private const MAX_DAILY_STEPS = 200000;

    public function record(User $user, int $steps, ?string $date = null): StepLog
    {
        $day = $this->day($user, $date);

        $log = $user->stepLogs()->whereDate('counted_on', $day)->first()
            ?? $user->stepLogs()->make();

        $log->counted_on = $day;
        $log->steps = min(max(0, $steps), self::MAX_DAILY_STEPS);
        $log->save();

        return $log;
    }

    public function stepsOn(User $user, ?string $date = null): int
    {
        return (int) $user->stepLogs()->whereDate('counted_on', $this->day($user, $date))->value('steps');
    }

    /** Totals across a window, for the mission metric. */
    public function stepsBetween(User $user, string $from, string $to): int
    {
        return (int) $user->stepLogs()
            ->whereDate('counted_on', '>=', $from)
            ->whereDate('counted_on', '<=', $to)
            ->sum('steps');
    }

    /**
     * @return array{today: int, target: int, percent: int, series: array<int, array{date: string, label: string, steps: int, percent: int}>}
     */
    public function summary(User $user, int $days = 7, int $target = 8000): array
    {
        $timezone = $user->timezone();
        $series = [];

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $day = now()->setTimezone($timezone)->subDays($offset);
            $steps = $this->stepsOn($user, $day->toDateString());

            $series[] = [
                'date' => $day->toDateString(),
                'label' => $day->format('D'),
                'steps' => $steps,
                'percent' => $target > 0 ? min(100, (int) round($steps / $target * 100)) : 0,
            ];
        }

        $today = $this->stepsOn($user);

        return [
            'today' => $today,
            'target' => $target,
            'percent' => $target > 0 ? min(100, (int) round($today / $target * 100)) : 0,
            'series' => $series,
        ];
    }

    private function day(User $user, ?string $date = null): string
    {
        return $date ? Carbon::parse($date)->toDateString() : now($user->timezone())->toDateString();
    }
}
