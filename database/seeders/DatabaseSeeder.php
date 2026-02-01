<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
            VoteSeeder::class,
            IdeaValidationSeeder::class,
            MarketplaceSeeder::class,
            ClipperSeeder::class,
            PlatformSettingsSeeder::class,
            HashtagSeeder::class,
            BookmarkSeeder::class,
            RepostSeeder::class,
            GDPRSeeder::class,
            UserBlockSeeder::class,
            ProductSeeder::class,
            TrendingSeeder::class,
            NotificationSeeder::class,
            PluginSeeder::class,
        ]);
    }
}
