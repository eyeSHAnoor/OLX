<?php

namespace Database\Seeders;

use App\Models\CampaignGift;
use App\Models\Gift;
use App\Models\GiftPeriod;
use Illuminate\Database\Seeder;

class CampaignGiftSeeder extends Seeder
{
    public function run(): void
    {
        $period = GiftPeriod::first();

        if (!$period) {
            return;
        }

        foreach (Gift::all() as $gift) {

            $allocated = rand(5, min($gift->quantity, 30));

            CampaignGift::create([
                'gift_period_id' => $period->id,
                'gift_id' => $gift->id,
                'allocated_quantity' => $allocated,
                'remaining_quantity' => $allocated,
                'notes' => 'Seeded campaign inventory',
            ]);
        }
    }
}