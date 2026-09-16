<?php

namespace App\Services;

use App\Models\User;
use App\Models\WaterLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class HydrationService
{
    /** Millilitres per kilogram of bodyweight, a common general guideline. */
    private const ML_PER_KG = 35;

    private const DEFAULT_TARGET_ML = 3000;

    private const MIN_TARGET_ML = 1500;

    private const MAX_TARGET_ML = 5000;

    /** Guards against a runaway total from repeated taps or a bad payload. */
    private const MAX_DAILY_ML = 15000;

    public function log(User $user, int $amountMl, ?string $loggedAt = null): WaterLog
    {
        $timestamp = $loggedAt ? Carbon::parse($loggedAt) : now();

        if ($this->totalForDay($user, $timestamp) + $amountMl > self::MAX_DAILY_ML) {
            throw ValidationException::withMessages([
                'amount_ml' => 'That would put you over the daily logging limit of 15 L.',
            ]);
        }

        return $user->waterLogs()->create([
            'amount_ml' => $amountMl,
            'logged_at' => $timestamp,
        ]);
    }

    public function delete(User $user, WaterLog $log): void
    {
        abort_unless($log->user_id === $user->id, 403);

        $log->delete();
    }

    /**
     * Total millilitres for the calendar day that `$moment` falls in, measured in
     * the user's own timezone rather than the server's.
     */
    public function totalForDay(User $user, ?Carbon $moment = null): int
    {
        [$start, $end] = $this->dayBounds($user, $moment);

        return (int) $user->waterLogs()->whereBetween('logged_at', [$start, $end])->sum('amount_ml');
    }

    /**
     * @return Collection<int, WaterLog>
     */
    public function logsForDay(User $user, ?Carbon $moment = null): Collection
    {
        [$start, $end] = $this->dayBounds($user, $moment);

        return $user->waterLogs()->whereBetween('logged_at', [$start, $end])
            ->orderByDesc('logged_at')->orderByDesc('id')->get();
    }

    /**
     * Daily target scaled to the most recent recorded bodyweight, falling back to
     * a flat default when no measurement exists yet.
     */
    public function targetMl(User $user): int
    {
        $weight = $user->healthMeasurements()->latest('measured_at')->value('weight_kg');

        if (! $weight) {
            return self::DEFAULT_TARGET_ML;
        }

        $target = (int) round($weight * self::ML_PER_KG / 50) * 50;

        return max(self::MIN_TARGET_ML, min(self::MAX_TARGET_ML, $target));
    }

    /**
     * Dashboard-ready hydration summary.
     *
     * @return array{consumedMl: int, consumedLitres: float, targetMl: int, targetLitres: float, percent: int, logs: array<int, array{id: int, amountMl: int, loggedAt: string}>}
     */
    public function summary(User $user): array
    {
        $consumed = $this->totalForDay($user);
        $target = $this->targetMl($user);

        return [
            'consumedMl' => $consumed,
            'consumedLitres' => round($consumed / 1000, 2),
            'targetMl' => $target,
            'targetLitres' => round($target / 1000, 2),
            'percent' => $target > 0 ? min(100, (int) round($consumed / $target * 100)) : 0,
            'logs' => $this->logsForDay($user)->map(fn (WaterLog $log): array => [
                'id' => $log->id,
                'amountMl' => $log->amount_ml,
                'loggedAt' => $log->logged_at->setTimezone($user->timezone())->format('H:i'),
            ])->all(),
        ];
    }

    /**
     * Daily totals for the last `$days` days, oldest first, including days with
     * nothing logged so the series has no gaps to plot around.
     *
     * @return array<int, array{date: string, label: string, litres: float, percent: int, met: bool}>
     */
    public function history(User $user, int $days = 7): array
    {
        $target = $this->targetMl($user);
        $timezone = $user->timezone();
        $series = [];

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $day = now()->setTimezone($timezone)->subDays($offset);
            $consumed = $this->totalForDay($user, $day);

            $series[] = [
                'date' => $day->toDateString(),
                'label' => $day->format('D'),
                'litres' => round($consumed / 1000, 2),
                'percent' => $target > 0 ? min(100, (int) round($consumed / $target * 100)) : 0,
                'met' => $consumed >= $target,
            ];
        }

        return $series;
    }

    /**
     * @return array{targetLitres: float, averageLitres: float, daysMet: int, days: int, series: array<int, array<string, mixed>>}
     */
    public function historySummary(User $user, int $days = 7): array
    {
        $series = $this->history($user, $days);
        $logged = array_column($series, 'litres');

        return [
            'targetLitres' => round($this->targetMl($user) / 1000, 2),
            'averageLitres' => $logged === [] ? 0.0 : round(array_sum($logged) / count($logged), 2),
            'daysMet' => count(array_filter($series, fn (array $day): bool => $day['met'])),
            'days' => $days,
            'series' => $series,
        ];
    }

    /**
     * UTC bounds of the user-local calendar day containing `$moment`.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dayBounds(User $user, ?Carbon $moment = null): array
    {
        $timezone = $user->timezone();
        $local = ($moment ? $moment->copy() : now())->setTimezone($timezone);

        return [
            $local->copy()->startOfDay()->utc(),
            $local->copy()->endOfDay()->utc(),
        ];
    }
}
