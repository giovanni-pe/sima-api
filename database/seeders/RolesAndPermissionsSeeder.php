<?php
// database/seeders/RolesAndPermissionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos básicos
        $permissions = [
            // Trip permissions
            'create_trip',
            'accept_trip',
            'cancel_trip',
            'complete_trip',
            'rate_trip',

            // Driver permissions
            'drive_vehicle',
            'update_location',
            'manage_availability',

            // Admin permissions
            'manage_users',
            'manage_drivers',
            'manage_trips',
            'view_analytics',
            'manage_subscriptions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles
        $passengerRole = Role::firstOrCreate(['name' => 'passenger']);
        $driverRole = Role::firstOrCreate(['name' => 'driver']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Asignar permisos a roles
        $passengerRole->syncPermissions([
            'create_trip',
            'cancel_trip',
            'rate_trip',
        ]);

        $driverRole->syncPermissions([
            'accept_trip',
            'complete_trip',
            'rate_trip',
            'drive_vehicle',
            'update_location',
            'manage_availability',
        ]);

        $adminRole->syncPermissions(Permission::all());
    }
}
