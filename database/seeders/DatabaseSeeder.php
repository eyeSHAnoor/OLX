<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(BrandSeeder::class);
        // $this->call(AdSeeder::class);
        $this->call(PlanSeeder::class);
        $this->call(FeatureSeeder::class);
        $this->call(AttributeGroupSeeder::class);
        $this->call(CategoryAttributeSeeder::class);
        $this->call(AttributeOptionSeeder::class);
        $this->call(BrandModelSeeder::class);
      
    }
}
