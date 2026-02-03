<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Roles
        $superAdmin = Role::create(['name' => 'super_admin',]);
        $warehouseRole = Role::create(['name' => 'warehouse_manager',]);
        $branchRole = Role::create(['name' => 'branch_manager',]);

        $users = User::all();

        // Assign to users
        $users->filter(fn($user) => $user->email == 'admin@test.com')->first()?->assignRole($superAdmin);
        $users->filter(fn($user) => $user->email == 'warehouse@test.com')->first()?->assignRole($warehouseRole);
        $users->filter(fn($user) => $user->email == 'branch@test.com')->first()?->assignRole($branchRole);
    }
}
