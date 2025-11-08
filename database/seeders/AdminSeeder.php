<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'info@noteds.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => bcrypt('Wahyu123456789@'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'wallet_balance' => 1000000, // Platform wallet
                'avatar' => 'https://ui-avatars.com/api/?name=Admin',
                'bio' => 'System Administrator',
                'location' => 'Bandung, Indonesia',
                'is_active' => true,
                'suspended_at' => null,
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
