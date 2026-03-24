<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;

class NotificationController extends Controller
{
     public function index(Request $request)
    {
        $user = $request->user();
        
        // Get paginated notifications
        $notifications = $user->notifications()
            ->paginate(15)
            ->withQueryString();
        
        // Get unread count
        $unreadCount = $user->unreadNotifications->count();
        
        return Inertia::render('notification/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

   public function markAsRead(DatabaseNotification $notification)
    {
        // Check ownership
        if ($notification->notifiable_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back();
    }
}