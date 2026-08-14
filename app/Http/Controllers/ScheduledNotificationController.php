<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ScheduledNotification;
use App\Data\ScheduledNotificationData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ScheduledNotificationController extends Controller
{
    /**
     * Display a listing of scheduled notifications.
     */
    public function index()
    {
        $columns = ['title', 'scheduled_at', 'is_sent', 'created_at', 'is_email'];

        $globalSearch = getGlobalSearchFilter([...$columns]);

        $notifications = QueryBuilder::for(ScheduledNotification::class)
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('is_sent'),
                AllowedFilter::exact('is_email'),
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        return Inertia::render('schedulenotification/Index', [
            'notifications' => ScheduledNotificationData::collect($notifications),
        ]);
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        return Inertia::render('schedulenotification/RecordForm');
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|url',
            'is_email' => 'sometimes|boolean', 
            'scheduled_at' => 'required|date',
        ]);

        ScheduledNotification::create($validated);

        return redirect()->route('scheduled-notifications.index')
            ->with('success', 'Notification scheduled successfully!');
    }

    /**
     * Show the form for editing the specified notification.
     */
    public function edit(ScheduledNotification $notification)
    {
        // dd($notification);
        // Don't allow editing if already sent
        if ($notification->is_sent) {
            return redirect()->route('scheduled-notifications.index')
                ->with('error', 'Cannot edit a notification that has already been sent.');
        }

        return Inertia::render('schedulenotification/RecordForm', [
            'notification' => ScheduledNotificationData::from($notification),
        ]);
    }

    /**
     * Update the specified notification.
     */
    public function update(Request $request, ScheduledNotification $notification)
    {
        // Don't allow updating if already sent
        if ($notification->is_sent) {
            return redirect()->route('scheduled-notifications.index')
                ->with('error', 'Cannot update a notification that has already been sent.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|url',
            'scheduled_at' => 'required|date|after:now',
             'is_email' => 'sometimes|boolean',
        ]);

        $notification->update($validated);

        return redirect()->route('scheduled-notifications.index')
            ->with('success', 'Notification updated successfully!');
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(ScheduledNotification $notification)
    {
        // Don't allow deleting if already sent
        if ($notification->is_sent) {
            return redirect()->route('scheduled-notifications.index')
                ->with('error', 'Cannot delete a notification that has already been sent.');
        }

        $notification->delete();

        return redirect()->route('scheduled-notifications.index')
            ->with('success', 'Notification deleted successfully!');
    }
}