<?php

// app/Notifications/SubscriptionRejectedNotification.php
namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionRejectedNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('Subscription Rejected ❌')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("We regret to inform you that your subscription for the **{$planName}** plan (Rs. {$amount}) has been rejected.")
            ->line('**Possible reasons for rejection:**')
            ->line('- Invalid or unclear receipt image')
            ->line('- Payment amount does not match the plan price')
            ->line('- Receipt does not match your account details')
            ->line('- Technical issues with the payment verification')
            ->line('If you believe this is an error, please contact our support team with your payment details.')
            ->action('Contact Support', url('/contact'))
            ->line('You can try submitting a new payment with a clearer receipt image.')
            ->salutation('Thank you for your understanding, ' . config('app.name'));
    }
}