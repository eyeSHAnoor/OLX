<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Expire subscriptions that have passed their end date';

    public function handle()
    {
        $now = Carbon::now();

        $subscriptions = Subscription::where('payment_status', 'completed')
            ->where('end_date', '<', $now)
            ->get();

        foreach ($subscriptions as $subscription) {
            DB::transaction(function () use ($subscription) {
                $subscription->update([
                    'payment_status' => 'expired'
                ]);

                // Update the user's subscription status if needed
                $user = $subscription->user;
                $user->status = 'inactive';
                $user->save();
            });

            $this->info("Expired subscription ID: {$subscription->id} for user ID: {$subscription->user_id}");
        }

        $this->info('All expired subscriptions processed.');
    }
}
