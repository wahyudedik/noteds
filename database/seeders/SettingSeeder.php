<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Premium subscription price (DEPRECATED - All users now have free access)
        // Setting kept for backward compatibility but subscription feature has been removed
        Setting::updateOrCreate(
            ['key' => 'premium_price_monthly', 'group' => 'subscription'],
            [
                'value' => '25000',
                'type' => 'number',
                'description' => 'Monthly premium subscription price in Rupiah (DEPRECATED - Feature removed)',
            ]
        );

        // Referral signup reward
        Setting::updateOrCreate(
            ['key' => 'referral_reward_signup', 'group' => 'referral'],
            [
                'value' => '5000',
                'type' => 'number',
                'description' => 'Referral signup reward amount in Rupiah',
            ]
        );

        // Referral transaction commission percentage
        Setting::updateOrCreate(
            ['key' => 'referral_reward_commission_percent', 'group' => 'referral'],
            [
                'value' => '5',
                'type' => 'number',
                'description' => 'Referral transaction commission percentage',
            ]
        );

        // Marketplace commission settings
        Setting::updateOrCreate(
            ['key' => 'platform_commission_percent', 'group' => 'marketplace'],
            [
                'value' => '20',
                'type' => 'number',
                'description' => 'Platform commission percentage (deducted from every transaction)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'creator_commission_percent', 'group' => 'marketplace'],
            [
                'value' => '0',
                'type' => 'number',
                'description' => 'Creator commission percentage (only for original creator on resale)',
            ]
        );

        // Share commission percentage
        Setting::updateOrCreate(
            ['key' => 'share_commission_percent', 'group' => 'marketplace'],
            [
                'value' => '5',
                'type' => 'number',
                'description' => 'Commission percentage for users who share notes and generate purchases',
            ]
        );

        // Viral/Hot badge thresholds
        Setting::updateOrCreate(
            ['key' => 'hot_note_threshold', 'group' => 'marketplace'],
            [
                'value' => '50',
                'type' => 'number',
                'description' => 'Minimum views in 24 hours to mark a note as "Hot"',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'viral_note_threshold', 'group' => 'marketplace'],
            [
                'value' => '200',
                'type' => 'number',
                'description' => 'Minimum views in 24 hours to mark a note as "Viral"',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'viral_growth_rate_threshold', 'group' => 'marketplace'],
            [
                'value' => '50',
                'type' => 'number',
                'description' => 'Minimum growth rate percentage (24h views / 7d views * 100) to mark as "Viral"',
            ]
        );

        // Share-to-Earn points settings
        Setting::updateOrCreate(
            ['key' => 'share_points_per_share', 'group' => 'marketplace'],
            [
                'value' => '10',
                'type' => 'number',
                'description' => 'Points awarded per share action',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'share_points_per_click', 'group' => 'marketplace'],
            [
                'value' => '5',
                'type' => 'number',
                'description' => 'Points awarded per click on share link',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'share_points_per_purchase', 'group' => 'marketplace'],
            [
                'value' => '50',
                'type' => 'number',
                'description' => 'Points awarded per purchase through share link',
            ]
        );

        // Monthly rewards settings
        Setting::updateOrCreate(
            ['key' => 'monthly_reward_rank_1', 'group' => 'marketplace'],
            [
                'value' => '100000',
                'type' => 'number',
                'description' => 'Monthly reward amount for rank 1 (in IDR)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'monthly_reward_rank_2', 'group' => 'marketplace'],
            [
                'value' => '50000',
                'type' => 'number',
                'description' => 'Monthly reward amount for rank 2 (in IDR)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'monthly_reward_rank_3', 'group' => 'marketplace'],
            [
                'value' => '25000',
                'type' => 'number',
                'description' => 'Monthly reward amount for rank 3 (in IDR)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'monthly_reward_top_10', 'group' => 'marketplace'],
            [
                'value' => '10000',
                'type' => 'number',
                'description' => 'Monthly reward amount for rank 4-10 (in IDR)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'monthly_reward_top_50', 'group' => 'marketplace'],
            [
                'value' => '5000',
                'type' => 'number',
                'description' => 'Monthly reward amount for rank 11-50 (in IDR)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'tax_default_percent', 'group' => 'marketplace'],
            [
                'value' => '11',
                'type' => 'number',
                'description' => 'Default tax percentage applied when no country-specific rule is found',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'tax_inclusive_default', 'group' => 'marketplace'],
            [
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Indicates whether listed prices already include tax by default',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'min_price_default', 'group' => 'marketplace'],
            [
                'value' => '50000',
                'type' => 'number',
                'description' => 'Minimum default price for paid notes in Rupiah',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'recommended_price_multiplier', 'group' => 'marketplace'],
            [
                'value' => '1.5',
                'type' => 'number',
                'description' => 'Suggested multiplier applied to minimum price for recommended pricing guidance',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'min_price_categories', 'group' => 'marketplace'],
            [
                'value' => [],
                'type' => 'json',
                'description' => 'Category-specific minimum prices for notes',
            ]
        );

        // Premium buyer exclusive discount (DEPRECATED - All users now have free access)
        // Setting kept for backward compatibility but premium feature has been removed
        Setting::updateOrCreate(
            ['key' => 'premium_buyer_discount_percent', 'group' => 'marketplace'],
            [
                'value' => '10',
                'type' => 'number',
                'description' => 'Exclusive discount percentage for premium buyers (DEPRECATED - Feature removed)',
            ]
        );

        // Featured Notes Pricing
        $locations = ['marketplace_grid', 'marketplace_banner', 'landing_hero', 'landing_carousel'];
        $durations = [7, 14, 30];

        $defaultPricing = [
            'marketplace_grid' => [7 => 50000, 14 => 90000, 30 => 150000],
            'marketplace_banner' => [7 => 75000, 14 => 140000, 30 => 250000],
            'landing_hero' => [7 => 150000, 14 => 280000, 30 => 500000],
            'landing_carousel' => [7 => 100000, 14 => 180000, 30 => 350000],
        ];

        foreach ($locations as $location) {
            foreach ($durations as $duration) {
                $key = "featured_price_{$location}_{$duration}";
                Setting::updateOrCreate(
                    ['key' => $key, 'group' => 'featured_notes'],
                    [
                        'value' => (string) $defaultPricing[$location][$duration],
                        'type' => 'number',
                        'description' => "Price for featured note at {$location} for {$duration} days",
                    ]
                );
            }
        }

        // Content Protection Settings (all disabled by default)
        $protectionSettings = [
            'protection_disable_text_selection' => 'Disable text selection on pages',
            'protection_disable_right_click' => 'Disable right-click context menu',
            'protection_disable_keyboard_shortcuts' => 'Disable keyboard shortcuts (Ctrl+C, Ctrl+V, etc.)',
            'protection_disable_copy_paste' => 'Disable copy, cut, and paste events',
            'protection_disable_drag_drop' => 'Disable drag and drop for images and elements',
            'protection_disable_print' => 'Disable print functionality (Ctrl+P)',
            'protection_disable_view_source' => 'Disable view source (Ctrl+U)',
            'protection_detect_devtools' => 'Detect and warn when Developer Tools are opened',
            'protection_disable_screenshot' => 'Disable screenshot on mobile devices (iOS/Android)',
            'protection_disable_image_saving' => 'Disable drag and save images from pages',
            'protection_disable_console' => 'Block console.log and browser console access',
            'protection_monitor_clipboard' => 'Monitor and clear clipboard periodically',
            'protection_disable_print_screen' => 'Disable Print Screen key and Windows+Print Screen',
            'protection_disable_snipping_tool' => 'Disable Windows+Shift+S (Snipping Tool)',
            'protection_detect_window_blur' => 'Detect when window loses focus (possible screenshot)',
            'protection_detect_visibility_change' => 'Detect tab visibility changes (tab switch for screenshot)',
            'protection_clear_clipboard_periodic' => 'Clear clipboard periodically every 800ms',
            'protection_blur_overlay' => 'Show blur overlay when screenshot or suspicious activity detected',
            'protection_disable_f12' => 'Disable F12 key (Developer Tools)',
            'protection_disable_devtools_shortcuts' => 'Disable DevTools shortcuts (Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C)',
            'protection_detect_ai_bots' => 'Detect AI bots from User-Agent (ChatGPT, Claude, Perplexity, etc.)',
            'protection_detect_headless' => 'Detect headless browsers (Selenium, Puppeteer, Playwright, etc.)',
            'protection_detect_mouse_movement' => 'Detect suspicious mouse movement patterns (AI pattern)',
            'protection_detect_click_pattern' => 'Detect suspicious click patterns (too consistent like AI)',
            'protection_detect_screen_recording' => 'Detect screen recording attempts using canvas fingerprinting',
        ];

        foreach ($protectionSettings as $key => $description) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'content_protection'],
                [
                    'value' => '0',
                    'type' => 'boolean',
                    'description' => $description,
                ]
            );
        }

        // Points & Rewards System Settings
        Setting::updateOrCreate(
            ['key' => 'points_expiration_days', 'group' => 'points'],
            [
                'value' => '365',
                'type' => 'number',
                'description' => 'Number of days before points expire (0 = never expire)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'points_purchase', 'group' => 'points'],
            [
                'value' => '10',
                'type' => 'number',
                'description' => 'Points awarded per purchase',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'points_purchase_multiplier', 'group' => 'points'],
            [
                'value' => '1',
                'type' => 'number',
                'description' => 'Multiplier for purchase points (points = base * multiplier)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'points_review', 'group' => 'points'],
            [
                'value' => '5',
                'type' => 'number',
                'description' => 'Points awarded per review',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'points_share', 'group' => 'points'],
            [
                'value' => '3',
                'type' => 'number',
                'description' => 'Points awarded per share',
            ]
        );

        // Redemption Settings
        Setting::updateOrCreate(
            ['key' => 'points_redemption_discount_1000', 'group' => 'points'],
            [
                'value' => '1000',
                'type' => 'number',
                'description' => 'Points required for 1000 IDR discount',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'points_redemption_discount_5000', 'group' => 'points'],
            [
                'value' => '4500',
                'type' => 'number',
                'description' => 'Points required for 5000 IDR discount',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'points_redemption_discount_10000', 'group' => 'points'],
            [
                'value' => '8000',
                'type' => 'number',
                'description' => 'Points required for 10000 IDR discount',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'points_redemption_premium_7days', 'group' => 'points'],
            [
                'value' => '5000',
                'type' => 'number',
                'description' => 'Points required for 7 days premium access',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'points_redemption_premium_30days', 'group' => 'points'],
            [
                'value' => '20000',
                'type' => 'number',
                'description' => 'Points required for 30 days premium access',
            ]
        );
    }
}

