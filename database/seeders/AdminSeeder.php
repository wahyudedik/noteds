<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;

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
                'name' => 'Noteds Admin',
                'username' => 'noteds',
                'password' => bcrypt('Wahyu123456789@'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'wallet_balance' => 10000000, // Platform wallet - increased for testing
                'avatar' => 'https://ui-avatars.com/api/?name=Admin',
                'bio' => 'System Administrator - Full access to all features (Seller, Buyer)',
                'location' => 'Mojokerto, Indonesia',
                'is_active' => true,
                'suspended_at' => null,
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create a test workspace for admin to test AI features
        $workspace = \App\Models\Workspace::firstOrCreate(
            [
                'owner_id' => $admin->id,
                'name' => 'Admin Test Workspace',
            ],
            [
                'slug' => Str::slug('Admin Test Workspace') . '-' . Str::random(5),
                'type' => 'personal',
                'description' => 'Test workspace for admin to test all AI features',
                'is_active' => true,
            ]
        );

        // Add admin as member with admin role
        \App\Models\WorkspaceMember::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'user_id' => $admin->id,
            ],
            [
                'role' => 'admin',
                'is_active' => true,
                'joined_at' => now(),
            ]
        );
    }
}
