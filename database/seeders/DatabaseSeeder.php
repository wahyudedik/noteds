<?php

namespace Database\Seeders;

use App\Models\User;
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
            RoleSeeder::class,
            AdminSeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            NoteSeeder::class,
            WorkspaceSeeder::class,
            FolderSeeder::class,
            FeaturedNoteSeeder::class,
            TransactionSeeder::class,
            ReferralCodeSeeder::class,
            FaqSeeder::class,
            SocialMediaLinkSeeder::class,
            TaxRuleSeeder::class,
            CmsPageSeeder::class,
            LandingPageSectionSeeder::class,
        ]);
    }
}
