<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $passenger = User::updateOrCreate(
            ['phone' => '+51987654321'],
            [
                'name' => 'Admin',
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'phone' => '+51987654321',
                'email' => 'admin@test.com',
                'password' => Hash::make('secret123'),
                'user_type' => 'passenger',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        
        $this->command->info("✅ Usuarios creados/actualizados:");
        $this->command->info("📧  admin@test.com / secret123 (Admin)");
      
    }
}
