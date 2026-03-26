<?php
// app/Mail/SubscriptionExpiredMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;

class SubscriptionExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function build()
    {
        return $this->subject('Your subscription has expired')
                    ->view('emails.subscription_expired')
                    ->with([
                        'user' => $this->subscription->user,
                        'plan' => $this->subscription->plan,
                        'ends_at' => $this->subscription->ends_at,
                    ]);
    }
}