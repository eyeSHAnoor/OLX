<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;
use App\Models\FeatureValue;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [

            'Condition' => ['New', 'Used', 'Refurbished'],

            'Color' => [
                'Black', 'White', 'Silver', 'Gray', 'Blue',
                'Red', 'Green', 'Gold'
            ],

            'Fuel Type' => ['Petrol', 'Diesel', 'Hybrid', 'Electric'],

            'Transmission' => ['Manual', 'Automatic'],

            'Body Type' => [
                'Sedan', 'Hatchback', 'SUV', 'Coupe', 'Pickup'
            ],

            'Warranty' => ['Yes', 'No'],

            'Authenticity' => ['Original', 'Copy'],

            // Custom value features (no predefined values)
            'Mileage' => [],
            'Engine Capacity' => [],
            'Screen Size' => [],
            'Storage' => [],
            'RAM' => [],
        ];

        foreach ($features as $featureName => $values) {

            $feature = Feature::firstOrCreate([
                'name' => $featureName,
            ]);

            foreach ($values as $value) {
                FeatureValue::firstOrCreate([
                    'feature_id' => $feature->id,
                    'value' => $value,
                ]);
            }
        }
    }
}
