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
            ['email' => 'admin@noteds.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'wallet_balance' => 1000000, // Platform wallet
                'avatar' => 'https://ui-avatars.com/api/?name=Admin',
                'bio' => 'System Administrator',
                'location' => 'Bandung, Indonesia',
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
