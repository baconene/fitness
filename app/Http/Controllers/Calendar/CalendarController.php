<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function __construct(private CalendarService $calendarService) {}

    public function show()
    {
        $user = Auth::user();
        $year = request()->input('year', now()->year);
        $month = request()->input('month', now()->month);

        $weeks = $this->calendarService->getMonth($user, $year, $month);
        $heatmapData = $this->calendarService->getHeatmapData($user, $year);

        return Inertia::render('Calendar/Index', [
            'weeks' => $weeks,
            'currentYear' => $year,
            'currentMonth' => $month,
            'heatmapData' => $heatmapData,
        ]);
    }

    public function getEvents()
    {
        $user = Auth::user();
        $startDate = request()->input('startDate');
        $endDate = request()->input('endDate');

        $events = $this->calendarService->getEventsForRange($user, $startDate, $endDate);

        return response()->json(['events' => $events]);
    }

    public function getAgenda()
    {
        $user = Auth::user();
        $date = request()->input('date', now()->toDateString());

        $items = $this->calendarService->getAgendaItems($user, $date);

        return response()->json(['items' => $items]);
    }
}
