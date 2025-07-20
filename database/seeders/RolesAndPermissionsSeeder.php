<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'manage products',
            'view products',
            'place orders',
            'manage orders',
            'manage users',
            'charge wallet',
            'view wallet',
            'make payment'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->givePermissionTo(['view products', 'place orders', 'charge wallet', 'view wallet','make payment']);
    }
}
