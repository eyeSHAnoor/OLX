<?php

namespace Database\Seeders;

use App\Models\Gift;
use Illuminate\Database\Seeder;

class GiftSeeder extends Seeder
{
    public function run(): void
    {
        $gifts = [
            [
                'name' => 'Coffee Mug',
                'description' => 'Premium ceramic coffee mug',
                'image' => null,
                'quantity' => 100,
            ],
            [
                'name' => 'T-Shirt',
                'description' => 'Company branded T-Shirt',
                'image' => null,
                'quantity' => 80,
            ],
            [
                'name' => 'Water Bottle',
                'description' => 'Insulated stainless steel bottle',
                'image' => null,
                'quantity' => 60,
            ],
            [
                'name' => 'Gift Card',
                'description' => '$25 Gift Card',
                'image' => null,
                'quantity' => 40,
            ],
            [
                'name' => 'Backpack',
                'description' => 'Laptop backpack',
                'image' => null,
                'quantity' => 25,
            ],
        ];

        foreach ($gifts as $gift) {
            Gift::create([
                ...$gift,
                'is_active' => true,
            ]);
        }
    }
}