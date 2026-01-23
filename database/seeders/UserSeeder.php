<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserSetting;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'info@noteds.com'],
            [
                'name' => 'Admin Noteds',
                'password' => bcrypt('Wahyu123456789@'),
                'business_name' => null,
                'business_field' => 'General',
                'skills' => ['admin'],
                'goals' => [],
                'role' => 'admin',
            ]
        );
        if (!$admin->email_verified_at) {
            $admin->email_verified_at = now();
            $admin->save();
        }
        UserSetting::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'notification_preferences' => [],
                'privacy_settings' => [
                    'posts_visibility' => 'public',
                    'comments_permission' => 'everyone',
                    'messaging_permission' => 'everyone',
                    'profile_visibility' => 'public',
                    'activity_visibility' => 'public',
                    'sharing' => ['analytics' => true, 'marketing' => false, 'recommendations' => true],
                ],
                'email_preferences' => [],
                'profile_visibility' => true,
                'search_visibility' => true,
                'auto_play_enabled' => false,
            ]
        );

        $count = 15;
        for ($i = 1; $i <= $count; $i++) {
            $name = "User {$i}";
            $email = "user{$i}@example.test";
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt('password123'),
                    'business_name' => $i % 2 === 0 ? "Biz {$i}" : null,
                    'business_field' => $i % 3 === 0 ? "Technology" : "General",
                    'skills' => $i % 2 === 0 ? ['javascript', 'laravel'] : ['marketing', 'design'],
                    'goals' => ['grow_network', 'learn'],
                    'role' => $i === 1 ? 'admin' : 'user',
                ]
            );

            UserSetting::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'notification_preferences' => [],
                    'privacy_settings' => [
                        'posts_visibility' => $i % 5 === 0 ? 'followers' : 'public',
                        'comments_permission' => $i % 7 === 0 ? 'followers' : 'everyone',
                        'messaging_permission' => $i % 11 === 0 ? 'none' : 'everyone',
                        'profile_visibility' => $i % 9 === 0 ? 'followers' : 'public',
                        'activity_visibility' => 'public',
                        'sharing' => ['analytics' => true, 'marketing' => false, 'recommendations' => true],
                    ],
                    'email_preferences' => [],
                    'profile_visibility' => true,
                    'search_visibility' => true,
                    'auto_play_enabled' => false,
                ]
            );
        }

        // Follows
        $users = User::pluck('id')->all();
        foreach ($users as $follower) {
            foreach (array_slice($users, 0, rand(3, 6)) as $followee) {
                if ($follower === $followee) continue;
                $exists = DB::table('follows')->where('follower_id', $follower)->where('following_id', $followee)->exists();
                if (!$exists) {
                    DB::table('follows')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'follower_id' => $follower,
                        'following_id' => $followee,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
