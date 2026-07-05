<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);

        User::factory()->create([
            'name' => 'Warehouse User',
            'email' => 'warehouse@test.com',
            // 'email' => 'www.ayeshapyari123@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'Branch User',
            'email' => 'branch@test.com',
        ]);

         User::factory(12)->create();

    }
}
