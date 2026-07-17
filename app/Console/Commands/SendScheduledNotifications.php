<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledNotification;
use App\Models\User;
use App\Notifications\ScheduledNotification as ScheduledNotificationClass;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send';
    protected $description = 'Send scheduled notifications to all users';

    public function handle()
    {
        $notifications = ScheduledNotification::where('is_sent', false)
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($notifications as $notification) {
            $users = User::all();
            
            foreach ($users as $user) {
                // Send via notification system (this handles database, broadcast, and webpush)
                $user->notify(new ScheduledNotificationClass(
                    $notification->title,
                    $notification->message,
                    $notification->url
                ));
            }

            $notification->update(['is_sent' => true]);
            $this->info("Sent notification: {$notification->title} to " . count($users) . " users");
        }
    }
}