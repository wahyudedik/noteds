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
            // ========================================
            // PRODUCTION SEEDERS - REQUIRED
            // ========================================

            // 1. Core System - REQUIRED (Roles, Settings)
            RoleSeeder::class,
            AffiliatePermissionSeeder::class,
            SellerNotesPermissionSeeder::class,
            SettingSeeder::class,

            // 2. Reference Data - REQUIRED (Master data)
            CategorySeeder::class,
            BadgeSeeder::class,
            LevelSeeder::class,
            ExchangeRateSeeder::class,
            CommissionTierSeeder::class,
            TaxRuleSeeder::class,
            NoteTemplateSeeder::class,

            // 3. CMS Content - REQUIRED (Static pages)
            CmsPageSeeder::class,
            FaqSeeder::class,
            LandingPageSectionSeeder::class,
            SocialMediaLinkSeeder::class,
            DocumentationSeeder::class,

            // 4. Subscription Plans - REQUIRED if using premium features
            SubscriptionPlanSeeder::class,

            // ========================================
            // DEVELOPMENT/TESTING ONLY - ENABLED TO ACTIVATE ALL FEATURES
            // ========================================

            AdminSeeder::class, // Create admin via CLI
            LandingPageSectionSeeder::class, // Requires admin user
            UserSeeder::class, // Real users will register 
            WalletSeeder::class, // Auto-created on user registration
            ReferralCodeSeeder::class, // Auto-created on user registration
            ReferralSeeder::class, // Real data from actual referrals
            WorkspaceSeeder::class, // Users create their own
            WorkspaceCollaborationSeeder::class, // Users create their own
            FolderSeeder::class, // Users create their own
            NoteSeeder::class, // Real notes from users
            NoteSeriesSeeder::class, // Real series from users
            NoteBundleSeeder::class, // Real bundles from users
            StudyMaterialSeeder::class, // Real materials from users
            PurchasedNoteSeeder::class, // Purchased notes history
            FeaturedNoteSeeder::class, // Admin will feature manually
            TransactionSeeder::class, // Real transactions
            ReferralTransactionSeeder::class, // Referral transaction history
            MonetizationApprovalSeeder::class, // Real approvals
            NoteEngagementSeeder::class, // Real user engagement
            NoteReviewSeeder::class, // Real reviews
            NoteCommentSeeder::class, // Real comments
            NoteReactionSeeder::class, // Real reactions
            NoteQuestionSeeder::class, // Real questions
            NoteCollaborationSeeder::class, // Real collaborations
            NoteReportSeeder::class, // Real reports
            GiftNoteSeeder::class, // Real gifts
            RefundSeeder::class, // Real refunds
            SupportSeeder::class, // Real support tickets
            AppNotificationSeeder::class, // Real notifications
            ActivitySeeder::class, // Real activity logs
            SocialFeatureSeeder::class, // Real social data
            MessageSeeder::class, // Real messages
            WithdrawSeeder::class, // Real withdrawals
            StudioSeeder::class, // Real studio orders
            StudioPaymentVerificationPermissionsSeeder::class, // Studio payment permissions
            WebhookSeeder::class, // Enable webhook test data
            PointsRulesSeeder::class, // Points/XP rules
            LeaderboardSettingsSeeder::class, // Leaderboard configs
            DisableProtectionSeeder::class, // Dev-only protection toggles
            TestMultiCurrencyUsersSeeder::class, // Dev multi-currency users
            AiAnalysisSeeder::class, // AI analysis sample data
            RecommendationSeeder::class, // AI recommendation tracking test data
        ]);
    }
}
