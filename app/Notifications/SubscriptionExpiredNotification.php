<?php

// app/Notifications/SubscriptionExpiredNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $subscription)
    {
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? 'Unknown',
            'message' => "Your subscription for plan {$this->subscription->plan->name} has expired",
            'type' => 'subscription_expired',
            'url' => route('subscriptions.index', [], false)
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? 'Unknown',
            'message' => "Your subscription for plan {$this->subscription->plan->name} has expired",
            'type' => 'subscription_expired',
            'url' => route('subscriptions.index', [], false)
        ]);
    }
}
