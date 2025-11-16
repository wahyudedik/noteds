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
            RoleSeeder::class,
            AdminSeeder::class,
            SettingSeeder::class,
            ExchangeRateSeeder::class,
            CommissionTierSeeder::class,
            TaxRuleSeeder::class,
            UserSeeder::class,
            WalletSeeder::class,
            ReferralCodeSeeder::class,
            ReferralSeeder::class,
            WorkspaceSeeder::class,
            WorkspaceCollaborationSeeder::class,
            FolderSeeder::class,
            NoteSeeder::class,
            StudyMaterialSeeder::class,
            AiAnalysisSeeder::class,
            DocumentationSeeder::class,
            LandingPageSectionSeeder::class,
            CmsPageSeeder::class,
            FaqSeeder::class,
            SocialMediaLinkSeeder::class,
            FeaturedNoteSeeder::class,
            TransactionSeeder::class,
            PurchasedNoteSeeder::class,
            NoteEngagementSeeder::class,
            NoteReviewSeeder::class,
            NoteCollaborationSeeder::class,
            NoteReportSeeder::class,
            SupportSeeder::class,
            AppNotificationSeeder::class,
            SocialFeatureSeeder::class,
            WithdrawSeeder::class,
            StudioSeeder::class,
        ]);
    }
}
