<?php

namespace Database\Seeders;

use App\Models\GiftPeriod;
use Illuminate\Database\Seeder;

class GiftPeriodSeeder extends Seeder
{
    public function run(): void
    {
        GiftPeriod::create([
            'name' => 'Eid Campaign 2026',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'is_active' => true,
        ]);

        GiftPeriod::create([
            'name' => 'New Year Campaign 2027',
            'start_date' => '2026-12-25',
            'end_date' => '2027-01-05',
            'is_active' => false,
        ]);
    }
}