<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@noteds.com'],
            [
                'name' => 'Admin Noteds',
                'role' => 'admin',
                'password' => Hash::make('admin123456'),
                'email_verified_at' => now(),
                'is_verified' => true,
                'kyc_verified' => true,
            ]
        );

        // Assign admin role
        $admin->assignRole('admin');

        $this->command->info('✅ Default admin user created with email: admin@noteds.com');
    }
}
