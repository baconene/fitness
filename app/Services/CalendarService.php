<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class CalendarService
{
    public function getEventsForRange(User $user, $startDate, $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $events = [];

        // Workouts
        $workouts = $user->workouts()
            ->whereBetween('started_at', [$start, $end])
            ->get();

        foreach ($workouts as $workout) {
            $events[] = [
                'id' => "workout-{$workout->id}",
                'title' => $workout->name,
                'date' => $workout->started_at->toDateString(),
                'type' => 'Workout',
                'icon' => '💪',
                'relatedId' => $workout->id,
            ];
        }

        // User Quests
        $quests = $user->userQuests()
            ->whereDate('assigned_date', '>=', $start->toDateString())
            ->whereDate('assigned_date', '<=', $end->toDateString())
            ->where('status', 'Active')
            ->with('questTemplate')
            ->get();

        foreach ($quests as $quest) {
            $events[] = [
                'id' => "quest-{$quest->id}",
                'title' => $quest->questTemplate->name,
                'date' => $quest->assigned_date->toDateString(),
                'type' => 'Quest',
                'icon' => '⭐',
                'relatedId' => $quest->id,
            ];
        }

        // Custom Calendar Events
        $customEvents = $user->calendarEvents()
            ->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        foreach ($customEvents as $event) {
            $events[] = [
                'id' => "custom-{$event->id}",
                'title' => $event->title,
                'date' => $event->event_date->toDateString(),
                'type' => $event->event_type,
                'icon' => $event->status_icon ?? '📌',
                'relatedId' => $event->id,
            ];
        }

        return collect($events)
            ->sortBy('date')
            ->groupBy('date')
            ->map(fn ($group) => $group->values()->all())
            ->all();
    }

    public function getHeatmapData(User $user, int $year): array
    {
        $startDate = Carbon::create($year, 1, 1)->startOfYear();
        $endDate = Carbon::create($year, 12, 31)->endOfYear();

        $workoutDates = $user->workouts()
            ->whereBetween('started_at', [$startDate, $endDate])
            ->select('started_at')
            ->groupBy('started_at')
            ->pluck('started_at');

        $heatmap = [];
        $current = $startDate->clone();

        while ($current <= $endDate) {
            $dateString = $current->toDateString();
            $hasWorkout = $workoutDates->contains(fn ($date) => $date->toDateString() === $dateString);

            $heatmap[$dateString] = $hasWorkout ? 1 : 0;
            $current->addDay();
        }

        return $heatmap;
    }

    public function getAgendaItems(User $user, $date): array
    {
        $date = Carbon::parse($date)->toDateString();

        $items = [];

        // Workouts
        $workouts = $user->workouts()
            ->whereDate('started_at', $date)
            ->with('workoutExercises.exercise')
            ->get();

        foreach ($workouts as $workout) {
            $items[] = [
                'type' => 'Workout',
                'title' => $workout->name,
                'time' => $workout->started_at?->format('H:i'),
                'icon' => '💪',
                'details' => "{$workout->workoutExercises->count()} exercises",
            ];
        }

        // Quests
        $quests = $user->userQuests()
            ->whereDate('assigned_date', $date)
            ->where('status', 'Active')
            ->with('questTemplate', 'progress')
            ->get();

        foreach ($quests as $quest) {
            $progress = $quest->progress?->current_value ?? 0;
            $target = $quest->questTemplate->target_value;
            $items[] = [
                'type' => 'Quest',
                'title' => $quest->questTemplate->name,
                'icon' => '⭐',
                'details' => "{$progress}/{$target}",
                'progress' => ($progress / $target) * 100,
            ];
        }

        return $items;
    }

    public function getMonth(User $user, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->clone()->endOfMonth();

        $events = $this->getEventsForRange($user, $startDate, $endDate);

        $weeks = [];
        $current = $startDate->clone()->startOfWeek();

        while ($current <= $endDate) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateString = $current->toDateString();
                $week[] = [
                    'date' => $dateString,
                    'day' => $current->day,
                    'isCurrentMonth' => $current->month === $month,
                    'isToday' => $current->toDateString() === now()->toDateString(),
                    'events' => $events[$dateString] ?? [],
                ];
                $current->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }
}
