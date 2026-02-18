<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name' => 'Basic',
                'price' => 500,
                'duration_days' => 30,
                'description' => 'Perfect for individual sellers',
                'features' => [
                    '5 listings per month',
                    'Basic support',
                    'Email notifications',
                    'Profile page',
                ],
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'price' => 1500,
                'duration_days' => 30,
                'description' => 'For power users and small businesses',
                'features' => [
                    'Unlimited listings',
                    'Priority support',
                    'Featured listings',
                    'Advanced analytics',
                    'Multiple categories',
                ],
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'price' => 5000,
                'duration_days' => 30,
                'description' => 'For businesses and agencies',
                'features' => [
                    'Unlimited everything',
                    'Dedicated account manager',
                    'API access',
                    'Custom branding',
                    'Bulk upload tools',
                    '24/7 phone support',
                ],
                'is_popular' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}