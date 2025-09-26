<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Limpiar usuarios existentes de prueba (opcional)
        // User::whereIn('email', ['passenger@test.com', 'driver@test.com', 'admin@test.com', 'both@test.com', 'demo@chasquix.com'])->delete();

        // Usuario Passenger
        $passenger = User::updateOrCreate(
            ['phone' => '+51987654321'],
            [
                'name' => 'Juan Passenger',
                'first_name' => 'Juan',
                'last_name' => 'Passenger',
                'phone' => '+51987654321',
                'email' => 'passenger@test.com',
                'password' => Hash::make('secret123'),
                'user_type' => 'passenger',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $passenger->syncRoles(['passenger']);
        if (!$passenger->referral_code) {
            $passenger->generateReferralCode();
        }
        $passenger->profile()->firstOrCreate([]);

        // Usuario Driver
        $driver = User::updateOrCreate(
            ['phone' => '+51987654322'],
            [
                'name' => 'Carlos Driver',
                'first_name' => 'Carlos',
                'last_name' => 'Driver',
                'phone' => '+51987654322',
                'email' => 'driver@test.com',
                'password' => Hash::make('secret123'),
                'user_type' => 'driver',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $driver->syncRoles(['driver']);
        if (!$driver->referral_code) {
            $driver->generateReferralCode();
        }
        $driver->profile()->firstOrCreate([]);

        // Crear perfil de conductor
        $driverProfile = $driver->driver()->firstOrCreate([
            'license_number' => 'D12345678',
            'license_expiry_date' => now()->addYears(2),
            'driver_status' => 'offline',
            'documents_verified' => true,
        ]);

        // Crear vehículo para el conductor
        $driverProfile->vehicles()->firstOrCreate(
            ['license_plate' => 'ABC-123'],
            [
                'vehicle_type' => 'car',
                'brand' => 'Toyota',
                'model' => 'Yaris',
                'license_plate' => 'ABC-123',
                'color' => 'Blanco',
                'passenger_capacity' => 4,
                'year' => 2020,
                'vehicle_status' => 'active',
            ]
        );

        // Usuario Admin
        $admin = User::updateOrCreate(
            ['phone' => '+51987654323'],
            [
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '+51987654323',
                'email' => 'admin@test.com',
                'password' => Hash::make('secret123'),
                'user_type' => 'both',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);
        if (!$admin->referral_code) {
            $admin->generateReferralCode();
        }
        $admin->profile()->firstOrCreate([]);

        // Usuario Both (Passenger y Driver)
        $both = User::updateOrCreate(
            ['phone' => '+51987654324'],
            [
                'name' => 'Ana Both',
                'first_name' => 'Ana',
                'last_name' => 'Both',
                'phone' => '+51987654324',
                'email' => 'both@test.com',
                'password' => Hash::make('secret123'),
                'user_type' => 'both',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $both->syncRoles(['passenger', 'driver']);
        if (!$both->referral_code) {
            $both->generateReferralCode();
        }
        $both->profile()->firstOrCreate([]);

        // Crear perfil de conductor para usuario both
        $bothDriverProfile = $both->driver()->firstOrCreate([
            'license_number' => 'D87654321',
            'license_expiry_date' => now()->addYears(3),
            'driver_status' => 'offline',
            'documents_verified' => true,
        ]);

        // Crear vehículo para usuario both
        $bothDriverProfile->vehicles()->firstOrCreate(
            ['license_plate' => 'XYZ-789'],
            [
                'vehicle_type' => 'motorcycle',
                'brand' => 'Honda',
                'model' => 'CB125F',
                'license_plate' => 'XYZ-789',
                'color' => 'Rojo',
                'passenger_capacity' => 1,
                'year' => 2021,
                'vehicle_status' => 'active',
            ]
        );

        // Usuario Demo para frontend
        $demo = User::updateOrCreate(
            ['phone' => '+51987654325'],
            [
                'name' => 'Demo User',
                'first_name' => 'Demo',
                'last_name' => 'User',
                'phone' => '+51987654325',
                'email' => 'demo@chasquix.com',
                'password' => Hash::make('123456'),
                'user_type' => 'passenger',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $demo->syncRoles(['passenger']);
        if (!$demo->referral_code) {
            $demo->generateReferralCode();
        }
        $demo->profile()->firstOrCreate([]);

        $this->command->info("✅ Usuarios creados/actualizados:");
        $this->command->info("📧 passenger@test.com / secret123 (Passenger)");
        $this->command->info("📧 driver@test.com / secret123 (Driver)");
        $this->command->info("📧 admin@test.com / secret123 (Admin)");
        $this->command->info("📧 both@test.com / secret123 (Both)");
        $this->command->info("📧 demo@chasquix.com / 123456 (Demo)");
    }
}
