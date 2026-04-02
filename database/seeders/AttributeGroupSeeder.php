<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttributeGroup;

class AttributeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributeGroups = [
            ['name' => 'General Specifications'],
            ['name' => 'Physical Characteristics'],
            ['name' => 'Technical Details'],
            ['name' => 'Performance'],
            ['name' => 'Connectivity'],
            ['name' => 'Warranty & Support'],
            ['name' => 'Condition'],
            ['name' => 'Features'],
            ['name' => 'Dimensions'],
            ['name' => 'Power Requirements'],
        ];

        foreach ($attributeGroups as $group) {
            AttributeGroup::create($group);
        }
    }
}