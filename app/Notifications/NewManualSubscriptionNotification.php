<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewManualSubscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,          // The user who purchased
        public Subscription $subscription
    ) {}

    public function via($notifiable)
    {
        // Adjust channels as needed. For super admins, 'database' and 'broadcast' are common.
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'user_id'       => $this->user->id,
            'user_name'     => $this->user->name,
            'subscription_id' => $this->subscription->id,
            'plan_name'     => $this->subscription->plan->name,
            'amount_paid'   => $this->subscription->amount_paid,
            'message'       => "{$this->user->name} submitted a manual subscription payment.",
            'type'          => 'new_manual_subscription',
            'url'           => route('users.index', $this->subscription->id), // Example admin route
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'user_name'     => $this->user->name,
            'message'       => "{$this->user->name} submitted a manual subscription payment.",
            'subscription_id' => $this->subscription->id,
            'url'           => route('users.index', $this->subscription->id),
        ]);
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('New Manual Subscription')
            ->icon('/icon.png')
            ->body("{$this->user->name} submitted a manual subscription payment.")
            ->data([
                'subscription_id' => $this->subscription->id,
                'url'             => route('users.index', $this->subscription->id),
            ]);
    }
}