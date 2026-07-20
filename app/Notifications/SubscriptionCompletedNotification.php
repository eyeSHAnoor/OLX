<?php

// app/Notifications/SubscriptionCompletedNotification.php
namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Subscription $subscription) {}

    public function via($notifiable)
    {
        return ['mail']; // email only
    }

    public function toMail($notifiable)
    {
        $planName = $this->subscription->plan->name ?? 'the selected plan';
        $amount   = number_format($this->subscription->amount_paid, 2);
        $startsAt = $this->subscription->starts_at->format('F d, Y');
        $endsAt   = $this->subscription->ends_at->format('F d, Y');

        return (new MailMessage)
            ->subject('Subscription Approved & Activated! 🎉')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("Congratulations! Your subscription for the **{$planName}** plan (Rs. {$amount}) has been approved and activated.")
            ->line("**Subscription Details:**")
            ->line("- Plan: {$planName}")
            ->line("- Amount Paid: Rs. {$amount}")
            ->line("- Start Date: {$startsAt}")
            ->line("- End Date: {$endsAt}")
            ->action('View Your Subscription', url('/subscription'))
            ->line('Thank you for choosing ' . config('app.name') . '! We hope you enjoy your subscription.')
            ->salutation('Best regards, ' . config('app.name'));
    }
}