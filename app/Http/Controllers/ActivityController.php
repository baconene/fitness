<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    /**
     * The system log: a reverse-chronological feed of everything the hunter
     * has done, drawn from the activity log and the XP ledger.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $timezone = $user->timezone();

        return Inertia::render('Activity/Index', [
            'entries' => ActivityLog::where('user_id', $user->id)
                ->latest('occurred_at')
                ->latest('id')
                ->paginate(30)
                ->through(fn (ActivityLog $log): array => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'subject' => $log->subject_type ? class_basename($log->subject_type) : null,
                    'meta' => $log->meta,
                    'occurredAt' => $log->occurred_at?->copy()->timezone($timezone)->format('M j, Y · H:i'),
                ]),
        ]);
    }
}
