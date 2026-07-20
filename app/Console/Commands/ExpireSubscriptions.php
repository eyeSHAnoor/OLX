<?php
// app/Console/Commands/ExpireSubscriptions.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionExpiredMail;
use App\Notifications\SubscriptionExpiredNotification;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Expire subscriptions that have passed their end date and notify users';

    public function handle()
    {
        $this->info('Current time: ' . now());
        // Fetch all active subscriptions that have ended
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->get()
            ->each(function ($subscription) {
                $this->info(
                    "ID: {$subscription->id}, ends_at: {$subscription->ends_at}, status: {$subscription->status}"
                );
            });

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return;
        }

        foreach ($expiredSubscriptions as $subscription) {
            // Update status
            $subscription->status = 'expired';
            $subscription->payment_status = 'expired';
            $subscription->save();

            $user = $subscription->user;
            if (!$user) {
                continue; // skip if user deleted
            }

            // Send email
            try {
                Mail::to($user->email)->send(new SubscriptionExpiredMail($subscription));
            } catch (\Exception $e) {
                $this->error("Failed to send email to {$user->email}: {$e->getMessage()}");
            }

            // Send notification via Notification class
            try {
                $user->notify(new SubscriptionExpiredNotification($subscription));
            } catch (\Exception $e) {
                $this->error("Failed to send notification to user {$user->id}: {$e->getMessage()}");
            }
        }

        $this->info('Expired subscriptions processed and users notified.');
    }
}