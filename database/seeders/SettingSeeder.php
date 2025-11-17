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
    }
}

