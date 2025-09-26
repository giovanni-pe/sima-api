<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Económico',
                'description' => 'Viajes accesibles para todos',
                'base_rate' => 2.50,
                'per_km_rate' => 0.75,
                'per_minute_rate' => 0.20,
                'minimum_fare' => 4.00,
                'cancellation_fee' => 1.00,
                'waiting_fee_per_minute' => 0.15,
                'is_active' => true,
            ],
            [
                'name' => 'Premium',
                'description' => 'Viajes con autos de alta gama',
                'base_rate' => 5.00,
                'per_km_rate' => 1.20,
                'per_minute_rate' => 0.35,
                'minimum_fare' => 8.00,
                'cancellation_fee' => 2.00,
                'waiting_fee_per_minute' => 0.25,
                'is_active' => true,
            ],
            [
                'name' => 'SUV',
                'description' => 'Ideal para grupos o equipaje adicional',
                'base_rate' => 4.00,
                'per_km_rate' => 1.00,
                'per_minute_rate' => 0.30,
                'minimum_fare' => 7.00,
                'cancellation_fee' => 1.50,
                'waiting_fee_per_minute' => 0.20,
                'is_active' => true,
            ],
            [
                'name' => 'Mototaxi',
                'description' => 'Rápido y económico para distancias cortas',
                'base_rate' => 1.00,
                'per_km_rate' => 0.50,
                'per_minute_rate' => 0.10,
                'minimum_fare' => 2.50,
                'cancellation_fee' => 0.50,
                'waiting_fee_per_minute' => 0.05,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            ServiceType::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}
