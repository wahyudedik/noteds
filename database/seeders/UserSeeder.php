<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin/Test User
        User::create([
            'name' => 'Admin User',
            'email' => 'info@noteds.com',
            'password' => Hash::make('Wahyu123456789@'),
            'business_name' => 'Noteds Admin',
            'business_field' => 'Technology',
            'skills' => ['Laravel', 'Vue.js', 'Business Strategy'],
            'goals' => ['Build a successful platform', 'Help entrepreneurs'],
            'portfolio_url' => 'https://noteds.com',
            'website_url' => 'https://noteds.com',
            'is_verified_mentor' => true,
            'email_verified_at' => now(),
            'role' => 'admin',
            'balance' => 0,
        ]);

        // Sample Business Users
        $users = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'password' => Hash::make('password'),
                'business_name' => 'TechStart Indonesia',
                'business_field' => 'Software Development',
                'skills' => ['PHP', 'JavaScript', 'Project Management'],
                'goals' => ['Scale business', 'Find tech partners'],
                'portfolio_url' => 'https://techstart.id',
                'website_url' => 'https://techstart.id',
                'is_verified_mentor' => false,
                'email_verified_at' => now(),
                'role' => 'user',
                'balance' => 0,
            ],
            [
                'name' => 'Wahyu Dedik',
                'email' => 'wdedyk@gmail.com',
                'password' => Hash::make('Wahyu123456789@'),
                'business_name' => 'Digital Marketing Pro',
                'business_field' => 'Digital Marketing',
                'skills' => ['SEO', 'Content Marketing', 'Social Media'],
                'goals' => ['Grow client base', 'Share knowledge'],
                'portfolio_url' => 'https://digitalmarketingpro.com',
                'website_url' => 'https://digitalmarketingpro.com',
                'is_verified_mentor' => true,
                'email_verified_at' => now(),
                'role' => 'user',
                'balance' => 0,
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@example.com',
                'password' => Hash::make('password'),
                'business_name' => 'E-Commerce Solutions',
                'business_field' => 'E-Commerce',
                'skills' => ['E-Commerce', 'Logistics', 'Customer Service'],
                'goals' => ['Expand to new markets', 'Improve operations'],
                'portfolio_url' => null,
                'website_url' => 'https://ecommercesolutions.id',
                'is_verified_mentor' => false,
                'email_verified_at' => now(),
                'role' => 'user',
                'balance' => 0,
            ],
            [
                'name' => 'Dewi Sartika',
                'email' => 'dewi@example.com',
                'password' => Hash::make('password'),
                'business_name' => 'Creative Agency',
                'business_field' => 'Creative Services',
                'skills' => ['Design', 'Branding', 'Creative Strategy'],
                'goals' => ['Win awards', 'Build portfolio'],
                'portfolio_url' => 'https://creativeagency.com',
                'website_url' => 'https://creativeagency.com',
                'is_verified_mentor' => false,
                'email_verified_at' => now(),
                'role' => 'user',
                'balance' => 0,
            ],
            [
                'name' => 'Rizki Pratama',
                'email' => 'rizki@example.com',
                'password' => Hash::make('password'),
                'business_name' => 'FinTech Startup',
                'business_field' => 'Financial Technology',
                'skills' => ['FinTech', 'Blockchain', 'Payment Systems'],
                'goals' => ['Get funding', 'Launch product'],
                'portfolio_url' => null,
                'website_url' => null,
                'is_verified_mentor' => false,
                'email_verified_at' => now(),
                'role' => 'user',
                'balance' => 0,
            ],
            [
                'name' => 'Maya Indira',
                'email' => 'maya@example.com',
                'password' => Hash::make('password'),
                'business_name' => 'HealthTech Solutions',
                'business_field' => 'Healthcare Technology',
                'skills' => ['Healthcare', 'Technology', 'Business Development'],
                'goals' => ['Improve healthcare access', 'Scale platform'],
                'portfolio_url' => 'https://healthtech.id',
                'website_url' => 'https://healthtech.id',
                'is_verified_mentor' => true,
                'email_verified_at' => now(),
                'role' => 'user',
                'balance' => 0,
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi@example.com',
                'password' => Hash::make('password'),
                'business_name' => 'Food & Beverage Co',
                'business_field' => 'Food & Beverage',
                'skills' => ['F&B Operations', 'Marketing', 'Supply Chain'],
                'goals' => ['Open new branches', 'Franchise model'],
                'portfolio_url' => null,
                'website_url' => null,
                'is_verified_mentor' => false,
                'email_verified_at' => now(),
                'role' => 'user',
                'balance' => 0,
            ],
            [
                'name' => 'Lina Kurniawan',
                'email' => 'lina@example.com',
                'password' => Hash::make('password'),
                'business_name' => 'Education Platform',
                'business_field' => 'Education Technology',
                'skills' => ['EdTech', 'Curriculum Development', 'Online Learning'],
                'goals' => ['Reach more students', 'Improve platform'],
                'portfolio_url' => 'https://edtechplatform.com',
                'website_url' => 'https://edtechplatform.com',
                'is_verified_mentor' => false,
                'email_verified_at' => now(),
                'role' => 'user',
                'balance' => 0,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
