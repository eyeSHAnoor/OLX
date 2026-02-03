<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarehousePositionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $positions = [
            [
                'code' => 'BIN-A01',
                'zone' => 'Zone A',
                'type' => 'shelf',
                'capacity' => 100,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BIN-A02',
                'zone' => 'Zone A',
                'type' => 'shelf',
                'capacity' => 120,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PALLET-B01',
                'zone' => 'Zone B',
                'type' => 'pallet',
                'capacity' => 500,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BULK-C01',
                'zone' => 'Zone C',
                'type' => 'bulk',
                'capacity' => 1000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BIN-B03',
                'zone' => 'Zone B',
                'type' => 'shelf',
                'capacity' => 80,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('warehouse_positions')->insert($positions);
    }
}
