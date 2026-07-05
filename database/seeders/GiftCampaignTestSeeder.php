<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Gift;
use App\Models\GiftPeriod;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GiftCampaignTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create plans if they don't exist
        $basicPlan = Plan::firstOrCreate(
            ['name' => 'Basic'],
            [
                'name' => 'Basic',
                'description' => 'Basic subscription plan',
                'price' => 9.99,
                'duration_days' => 30,
                'is_popular' => false,
                'features' => json_encode(['Basic features', 'Limited access']),
                'sort_order' => 1,
            ]
        );

        $premiumPlan = Plan::firstOrCreate(
            ['name' => 'Premium'],
            [
                'name' => 'Premium',
                'description' => 'Premium subscription plan',
                'price' => 19.99,
                'duration_days' => 30,
                'is_popular' => true,
                'features' => json_encode(['All basic features', 'Priority support', 'Advanced features']),
                'sort_order' => 2,
            ]
        );

        $proPlan = Plan::firstOrCreate(
            ['name' => 'Pro'],
            [
                'name' => 'Pro',
                'description' => 'Pro subscription plan',
                'price' => 29.99,
                'duration_days' => 30,
                'is_popular' => false,
                'features' => json_encode(['All premium features', 'VIP support', 'Exclusive features', 'Early access']),
                'sort_order' => 3,
            ]
        );

        // Create test gifts
        $gift1 = Gift::firstOrCreate(
            ['name' => 'Premium Headphones'],
            [
                'name' => 'Premium Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation',
                'quantity' => 50,
                'is_active' => true,
            ]
        );

        $gift2 = Gift::firstOrCreate(
            ['name' => 'Smart Watch'],
            [
                'name' => 'Smart Watch',
                'description' => 'Fitness tracker with heart rate monitor',
                'quantity' => 30,
                'is_active' => true,
            ]
        );

        $gift3 = Gift::firstOrCreate(
            ['name' => 'Bluetooth Speaker'],
            [
                'name' => 'Bluetooth Speaker',
                'description' => 'Portable wireless speaker with deep bass',
                'quantity' => 40,
                'is_active' => true,
            ]
        );

        // Create users with different subscription scenarios
        $users = [
            // User 1: 6 months continuous subscription (ELIGIBLE)
            [
                'user' => [
                    'name' => 'Ahmed Khan',
                    'email' => 'ahmed.khan@example.com',
                    'phone' => '+923001234567',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(6),
                        'ends_at' => Carbon::now()->subMonths(5),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(5),
                        'ends_at' => Carbon::now()->subMonths(4),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(4),
                        'ends_at' => Carbon::now()->subMonths(3),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(3),
                        'ends_at' => Carbon::now()->subMonths(2),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(2),
                        'ends_at' => Carbon::now()->subMonths(1),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonth(),
                        'ends_at' => Carbon::now()->addDays(25),
                        'payment_status' => 'completed',
                        'status' => 'active',
                        'amount_paid' => 29.99,
                    ],
                ],
            ],

            // User 2: 4 months continuous subscription (ELIGIBLE - exactly 4 months)
            [
                'user' => [
                    'name' => 'Fatima Ali',
                    'email' => 'fatima.ali@example.com',
                    'phone' => '+923002345678',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [
                    [
                        'plan_id' => $basicPlan->id,
                        'starts_at' => Carbon::now()->subMonths(4),
                        'ends_at' => Carbon::now()->subMonths(3),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 9.99,
                    ],
                    [
                        'plan_id' => $basicPlan->id,
                        'starts_at' => Carbon::now()->subMonths(3),
                        'ends_at' => Carbon::now()->subMonths(2),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 9.99,
                    ],
                    [
                        'plan_id' => $basicPlan->id,
                        'starts_at' => Carbon::now()->subMonths(2),
                        'ends_at' => Carbon::now()->subMonths(1),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 9.99,
                    ],
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonth(),
                        'ends_at' => Carbon::now()->addDays(28),
                        'payment_status' => 'completed',
                        'status' => 'active',
                        'amount_paid' => 19.99,
                    ],
                ],
            ],

            // User 3: 8 months continuous subscription (ELIGIBLE)
            [
                'user' => [
                    'name' => 'Mohammad Usman',
                    'email' => 'm.usman@example.com',
                    'phone' => '+923003456789',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(8),
                        'ends_at' => Carbon::now()->subMonths(7),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(7),
                        'ends_at' => Carbon::now()->subMonths(6),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(6),
                        'ends_at' => Carbon::now()->subMonths(5),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(5),
                        'ends_at' => Carbon::now()->subMonths(4),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(4),
                        'ends_at' => Carbon::now()->subMonths(3),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(3),
                        'ends_at' => Carbon::now()->subMonths(2),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(2),
                        'ends_at' => Carbon::now()->subMonths(1),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonth(),
                        'ends_at' => Carbon::now()->addDays(20),
                        'payment_status' => 'completed',
                        'status' => 'active',
                        'amount_paid' => 29.99,
                    ],
                ],
            ],

            // User 4: 3 months subscription (NOT ELIGIBLE - less than 4 months)
            [
                'user' => [
                    'name' => 'Ayesha Iqbal',
                    'email' => 'ayesha.iqbal@example.com',
                    'phone' => '+923004567890',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [
                    [
                        'plan_id' => $basicPlan->id,
                        'starts_at' => Carbon::now()->subMonths(3),
                        'ends_at' => Carbon::now()->subMonths(2),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 9.99,
                    ],
                    [
                        'plan_id' => $basicPlan->id,
                        'starts_at' => Carbon::now()->subMonths(2),
                        'ends_at' => Carbon::now()->subMonths(1),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 9.99,
                    ],
                    [
                        'plan_id' => $basicPlan->id,
                        'starts_at' => Carbon::now()->subMonth(),
                        'ends_at' => Carbon::now()->addDays(15),
                        'payment_status' => 'completed',
                        'status' => 'active',
                        'amount_paid' => 9.99,
                    ],
                ],
            ],

            // User 5: 2 months subscription (NOT ELIGIBLE - less than 4 months)
            [
                'user' => [
                    'name' => 'Bilal Ahmed',
                    'email' => 'bilal.ahmed@example.com',
                    'phone' => '+923005678901',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(2),
                        'ends_at' => Carbon::now()->subMonths(1),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonth(),
                        'ends_at' => Carbon::now()->addDays(10),
                        'payment_status' => 'completed',
                        'status' => 'active',
                        'amount_paid' => 19.99,
                    ],
                ],
            ],

            // User 6: 4 months with gap (NOT ELIGIBLE - has gap in subscription)
            [
                'user' => [
                    'name' => 'Sara Malik',
                    'email' => 'sara.malik@example.com',
                    'phone' => '+923006789012',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [
                    [
                        'plan_id' => $basicPlan->id,
                        'starts_at' => Carbon::now()->subMonths(4),
                        'ends_at' => Carbon::now()->subMonths(3),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 9.99,
                    ],
                    // Gap here - no subscription for 1 month
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonth(),
                        'ends_at' => Carbon::now()->addDays(5),
                        'payment_status' => 'completed',
                        'status' => 'active',
                        'amount_paid' => 19.99,
                    ],
                ],
            ],

            // User 7: 5 months continuous subscription (ELIGIBLE)
            [
                'user' => [
                    'name' => 'Hassan Raza',
                    'email' => 'hassan.raza@example.com',
                    'phone' => '+923007890123',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(5),
                        'ends_at' => Carbon::now()->subMonths(4),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(4),
                        'ends_at' => Carbon::now()->subMonths(3),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(3),
                        'ends_at' => Carbon::now()->subMonths(2),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $premiumPlan->id,
                        'starts_at' => Carbon::now()->subMonths(2),
                        'ends_at' => Carbon::now()->subMonths(1),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 19.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonth(),
                        'ends_at' => Carbon::now()->addDays(22),
                        'payment_status' => 'completed',
                        'status' => 'active',
                        'amount_paid' => 29.99,
                    ],
                ],
            ],

            // User 8: No subscription (NOT ELIGIBLE)
            [
                'user' => [
                    'name' => 'Zainab Noor',
                    'email' => 'zainab.noor@example.com',
                    'phone' => '+923008901234',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [],
            ],

            // User 9: Pending subscription (NOT ELIGIBLE - payment not completed)
            [
                'user' => [
                    'name' => 'Omar Farooq',
                    'email' => 'omar.farooq@example.com',
                    'phone' => '+923009012345',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonth(),
                        'ends_at' => Carbon::now()->addDays(29),
                        'payment_status' => 'pending',
                        'status' => 'pending',
                        'amount_paid' => 29.99,
                    ],
                ],
            ],

            // User 10: 12 months continuous subscription (ELIGIBLE - long term subscriber)
            [
                'user' => [
                    'name' => 'Nadia Hussain',
                    'email' => 'nadia.hussain@example.com',
                    'phone' => '+923010123456',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'subscriptions' => [
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(12),
                        'ends_at' => Carbon::now()->subMonths(11),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(11),
                        'ends_at' => Carbon::now()->subMonths(10),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(10),
                        'ends_at' => Carbon::now()->subMonths(9),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(9),
                        'ends_at' => Carbon::now()->subMonths(8),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(8),
                        'ends_at' => Carbon::now()->subMonths(7),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(7),
                        'ends_at' => Carbon::now()->subMonths(6),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(6),
                        'ends_at' => Carbon::now()->subMonths(5),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(5),
                        'ends_at' => Carbon::now()->subMonths(4),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(4),
                        'ends_at' => Carbon::now()->subMonths(3),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(3),
                        'ends_at' => Carbon::now()->subMonths(2),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonths(2),
                        'ends_at' => Carbon::now()->subMonths(1),
                        'payment_status' => 'completed',
                        'status' => 'expired',
                        'amount_paid' => 29.99,
                    ],
                    [
                        'plan_id' => $proPlan->id,
                        'starts_at' => Carbon::now()->subMonth(),
                        'ends_at' => Carbon::now()->addDays(30),
                        'payment_status' => 'completed',
                        'status' => 'active',
                        'amount_paid' => 29.99,
                    ],
                ],
            ],
        ];

        // Create users and their subscriptions
        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['user']['email']],
                $userData['user']
            );

            // Create subscriptions for the user
            foreach ($userData['subscriptions'] as $subscriptionData) {
                Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $subscriptionData['plan_id'],
                    'starts_at' => $subscriptionData['starts_at'],
                    'ends_at' => $subscriptionData['ends_at'],
                    'payment_status' => $subscriptionData['payment_status'],
                    'status' => $subscriptionData['status'],
                    'amount_paid' => $subscriptionData['amount_paid'],
                ]);
            }

            // Create user profile if method exists
            if (method_exists($user, 'profile')) {
                $user->profile()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'city' => fake()->city(),
                    ]
                );
            }
        }

        // Create a test gift campaign period
        $period = GiftPeriod::firstOrCreate(
            ['name' => 'Q3 2026 Loyalty Rewards'],
            [
                'name' => 'Q3 2026 Loyalty Rewards',
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(25),
                'is_active' => true,
            ]
        );

        // Allocate gifts to the campaign
        $campaignGifts = [
            ['gift_id' => $gift1->id, 'allocated_quantity' => 20, 'remaining_quantity' => 20, 'notes' => null],
            ['gift_id' => $gift2->id, 'allocated_quantity' => 15, 'remaining_quantity' => 15, 'notes' => null],
            ['gift_id' => $gift3->id, 'allocated_quantity' => 25, 'remaining_quantity' => 25, 'notes' => null],
        ];

        foreach ($campaignGifts as $campaignGiftData) {
            // Check if campaignGifts relationship exists
            if (method_exists($period, 'campaignGifts')) {
                $period->campaignGifts()->firstOrCreate(
                    [
                        'gift_period_id' => $period->id,
                        'gift_id' => $campaignGiftData['gift_id'],
                    ],
                    $campaignGiftData
                );
            }
        }

        // Create another inactive campaign for testing
        GiftPeriod::firstOrCreate(
            ['name' => 'Q2 2026 Welcome Gifts'],
            [
                'name' => 'Q2 2026 Welcome Gifts',
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->subDays(10),
                'is_active' => false,
            ]
        );

        $this->command->info('Gift Campaign Test Data Seeded Successfully!');
        $this->command->info('-------------------------------------------');
        $this->command->info('Test Users Created:');
        $this->command->info('  ELIGIBLE (4+ months continuous):');
        $this->command->info('    - ahmed.khan@example.com (6 months)');
        $this->command->info('    - fatima.ali@example.com (4 months)');
        $this->command->info('    - m.usman@example.com (8 months)');
        $this->command->info('    - hassan.raza@example.com (5 months)');
        $this->command->info('    - nadia.hussain@example.com (12 months)');
        $this->command->info('  NOT ELIGIBLE:');
        $this->command->info('    - ayesha.iqbal@example.com (3 months)');
        $this->command->info('    - bilal.ahmed@example.com (2 months)');
        $this->command->info('    - sara.malik@example.com (gap in subscription)');
        $this->command->info('    - zainab.noor@example.com (no subscription)');
        $this->command->info('    - omar.farooq@example.com (pending payment)');
        $this->command->info('  Password for all users: password');
        $this->command->info('-------------------------------------------');
    }
}