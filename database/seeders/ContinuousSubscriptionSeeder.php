<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ContinuousSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create the plan
        $plan = Plan::firstOrCreate(
            ['name' => 'Pro'],
            [
                'price' => 999,
                'duration_days' => 30,
                'description' => 'Premium Monthly Plan',
                'features' => [
                    'Unlimited Access',
                    'Priority Support',
                ],
                'is_popular' => true,
                'sort_order' => 1,
            ]
        );

        // Make sure at least 10 users exist
        if (User::count() < 10) {
            User::factory(10 - User::count())->create();
        }

        // Get all users (or use ->take(10)->get() if you only want 10)
        $users = User::all();

        foreach ($users as $user) {

            // Skip users that already have subscriptions
            if (Subscription::where('user_id', $user->id)->exists()) {
                continue;
            }

           $start = Carbon::today()->subMonths(4);

            for ($month = 1; $month <= 5; $month++) {

                // Last subscription ends today
                if ($month === 4) {
                    $end = Carbon::today();
                } else {
                    $end = (clone $start)->addMonth();
                }

                Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,

                    'starts_at' => $start,
                    'ends_at' => $end,

                    'receipt_image' => null,
                    'payment_method' => 'JazzCash',
                    'payment_status' => 'completed',
                    'status' => $month === 4 ? 'active' : 'active',

                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    'payment_gateway' => 'jazzcash',
                    'amount_paid' => $plan->price,
                    'payment_data' => [
                        'seeded' => true,
                        'month' => $month,
                    ],
                ]);

                $start = clone $end;
            }
        }
    }
}