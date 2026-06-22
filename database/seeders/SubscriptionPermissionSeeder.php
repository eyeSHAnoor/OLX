<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPermission;
use App\Models\Plan;

class SubscriptionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------
        // 1. CREATE PERMISSIONS
        // ----------------------------
        $proBatch = SubscriptionPermission::create([
            'name' => 'pro_batch',
            'label' => 'Pro Batch Access',
        ]);

        $premiumBatch = SubscriptionPermission::create([
            'name' => 'premium_batch',
            'label' => 'Premium Batch Access',
        ]);

        $featuredAds = SubscriptionPermission::create([
            'name' => 'featured_ads',
            'label' => 'Featured Ads',
        ]);

        // ----------------------------
        // 2. ASSIGN TO PLANS
        // ----------------------------

        // Plan 1 → NO permissions
        $plan1 = Plan::find(1);
        if ($plan1) {
            $plan1->permissions()->sync([]);
        }

        // Plan 2 → pro_batch only
        $plan2 = Plan::find(2);
        if ($plan2) {
            $plan2->permissions()->sync([
                $proBatch->id,
            ]);
        }

        // Plan 3 → premium_batch + featured_ads
        $plan3 = Plan::find(3);
        if ($plan3) {
            $plan3->permissions()->sync([
                $premiumBatch->id,
                $featuredAds->id,
            ]);
        }
    }
}