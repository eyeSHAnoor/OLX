<?php

// app/Notifications/ManualSubscriptionPendingNotification.php
namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ManualSubscriptionPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Subscription $subscription) {}

    public function via($notifiable)
    {
        return ['mail'];                         // email only
    }

    public function toMail($notifiable)
    {
        $planName = $this->subscription->plan->name ?? 'the selected plan';
        $amount   = number_format($this->subscription->amount_paid, 2);

        return (new MailMessage)
            ->subject('Subscription Received – Pending Approval')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("We have received your manual payment for the **{$planName}** plan (Rs. {$amount}).")
            ->line('Your subscription is currently **pending review** by our team.')
            ->line('Please wait while we confirm your payment details. You will be notified once it’s approved.')
            ->salutation('Thank you, ' . config('app.name'));
    }
}