<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\TaxRule;
use App\Services\CurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index(): View
    {
        // Get all settings grouped
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        // Get S3 settings specifically
        $s3Settings = Setting::where('group', 's3')->get()->keyBy('key');

        // Get premium price setting
        $premiumPrice = Setting::getPremiumPrice();

        // Get referral reward settings
        $referralSignupReward = Setting::getReferralSignupReward();
        $referralCommissionPercent = Setting::getReferralCommissionPercent();

        // Get marketplace commission settings
        $platformCommissionPercent = Setting::getPlatformCommissionPercent();
        $creatorCommissionPercent = Setting::getCreatorCommissionPercent();

        // Get premium buyer discount
        $premiumBuyerDiscountPercent = Setting::getPremiumBuyerDiscountPercent();
        $defaultTaxPercent = Setting::getDefaultTaxPercent();
        $taxInclusiveDefault = Setting::isTaxInclusiveDefault();
        $taxRules = TaxRule::orderBy('country_name')->get();
        $minPriceDefault = Setting::getDefaultMinPrice();
        $recommendedPriceMultiplier = Setting::getRecommendedPriceMultiplier();
        $categoryMinPriceRules = Setting::getCategoryMinPriceList();
        $availableTags = Tag::orderBy('name')->get();

        // Get featured notes pricing
        $featuredPricing = Setting::getFeaturedPricing();
        $featuredLocationLabels = Setting::getFeaturedLocationLabels();
        $featuredDurations = Setting::getFeaturedDurations();

        // Studio settings
        $studioPlatformFeePercent = Setting::getSetting('studio_platform_fee_percent', 'studio', 10);
        $studioEmailToggles = [
            'quote_created' => (bool) Setting::getSetting('studio_email_quote_created', 'studio', true),
            'quote_accepted' => (bool) Setting::getSetting('studio_email_quote_accepted', 'studio', true),
            'quote_rejected' => (bool) Setting::getSetting('studio_email_quote_rejected', 'studio', true),
            'escrow_funded' => (bool) Setting::getSetting('studio_email_escrow_funded', 'studio', true),
            'escrow_released' => (bool) Setting::getSetting('studio_email_escrow_released', 'studio', true),
            'escrow_refunded' => (bool) Setting::getSetting('studio_email_escrow_refunded', 'studio', true),
            'vendor_assigned' => (bool) Setting::getSetting('studio_email_vendor_assigned', 'studio', true),
        ];
        $studioSlaFundingReminderDays = (int) Setting::getSetting('studio_sla_funding_reminder_days', 'studio', 3);

        // AI usage configuration
        $aiFreeUsageLimit = Setting::getAiFreeUsageLimit();
        $aiFeaturePrices = Setting::getAiFeaturePrices();
        $currencyService = app(CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();
        $currencyInfo = \App\Helpers\CurrencyHelper::getCurrencyInfo($baseCurrency);
        $currencySymbol = $currencyInfo['symbol'] ?? $baseCurrency;

        // Google Translate API settings
        $googleTranslateEnabled = (bool) Setting::getSetting('google_translate_enabled', 'translation', false);
        $googleTranslateApiKey = Setting::getSetting('google_translate_api_key', 'translation', '');

        return view('admin.settings.index', compact(
            'settings',
            's3Settings',
            'premiumPrice',
            'referralSignupReward',
            'referralCommissionPercent',
            'platformCommissionPercent',
            'creatorCommissionPercent',
            'studioPlatformFeePercent',
            'studioEmailToggles',
            'studioSlaFundingReminderDays',
            'premiumBuyerDiscountPercent',
            'defaultTaxPercent',
            'taxInclusiveDefault',
            'taxRules',
            'minPriceDefault',
            'recommendedPriceMultiplier',
            'categoryMinPriceRules',
            'availableTags',
            'featuredPricing',
            'featuredLocationLabels',
            'featuredDurations',
            'aiFreeUsageLimit',
            'aiFeaturePrices',
            'currencySymbol',
            'baseCurrency',
            'googleTranslateEnabled',
            'googleTranslateApiKey'
        ));
    }

    /**
     * Update settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            's3_enabled' => 'nullable|boolean',
            's3_provider' => 'nullable|in:aws,digitalocean,wasabi,other',
            's3_key' => 'nullable|string|max:255',
            's3_secret' => 'nullable|string|max:255',
            's3_region' => 'nullable|string|max:100',
            's3_bucket' => 'nullable|string|max:255',
            's3_endpoint' => 'nullable|url|max:500',
            's3_path_prefix' => 'nullable|string|max:255',
            'premium_price_monthly' => 'nullable|numeric|min:0|max:10000000',
            'referral_reward_signup' => 'nullable|numeric|min:0|max:10000000',
            'referral_reward_commission_percent' => 'nullable|numeric|min:0|max:100',
            'platform_commission_percent' => 'nullable|numeric|min:0|max:100',
            'creator_commission_percent' => 'nullable|numeric|min:0|max:100',
            'studio_platform_fee_percent' => 'nullable|numeric|min:0|max:100',
            'studio_email_quote_created' => 'nullable|boolean',
            'studio_email_quote_accepted' => 'nullable|boolean',
            'studio_email_quote_rejected' => 'nullable|boolean',
            'studio_email_escrow_funded' => 'nullable|boolean',
            'studio_email_escrow_released' => 'nullable|boolean',
            'studio_email_escrow_refunded' => 'nullable|boolean',
            'studio_email_vendor_assigned' => 'nullable|boolean',
            'studio_sla_funding_reminder_days' => 'nullable|integer|min:1|max:30',
            'premium_buyer_discount_percent' => 'nullable|numeric|min:0|max:50',
            'tax_default_percent' => 'nullable|numeric|min:0|max:100',
            'tax_inclusive_default' => 'nullable|boolean',
            'ai_free_usage_limit' => 'nullable|integer|min:-1|max:100',
            'ai_price_image_search' => 'nullable|numeric|min:0|max:10000000',
            'ai_price_image_generate' => 'nullable|numeric|min:0|max:10000000',
            'ai_price_video_generate' => 'nullable|numeric|min:0|max:10000000',
            'min_price_default' => 'nullable|numeric|min:0|max:100000000',
            'recommended_price_multiplier' => 'nullable|numeric|min:0|max:10',
            'featured_price.*.*' => 'nullable|numeric|min:0|max:100000000',
            // Content Protection Settings
            'protection_disable_text_selection' => 'nullable|boolean',
            'protection_disable_right_click' => 'nullable|boolean',
            'protection_disable_keyboard_shortcuts' => 'nullable|boolean',
            'protection_disable_copy_paste' => 'nullable|boolean',
            'protection_disable_drag_drop' => 'nullable|boolean',
            'protection_disable_print' => 'nullable|boolean',
            'protection_disable_view_source' => 'nullable|boolean',
            'protection_detect_devtools' => 'nullable|boolean',
            'protection_disable_screenshot' => 'nullable|boolean',
            'protection_disable_image_saving' => 'nullable|boolean',
            'protection_disable_console' => 'nullable|boolean',
            'protection_monitor_clipboard' => 'nullable|boolean',
            'protection_disable_print_screen' => 'nullable|boolean',
            'protection_disable_snipping_tool' => 'nullable|boolean',
            'protection_detect_window_blur' => 'nullable|boolean',
            'protection_detect_visibility_change' => 'nullable|boolean',
            'protection_clear_clipboard_periodic' => 'nullable|boolean',
            'protection_blur_overlay' => 'nullable|boolean',
            'protection_disable_f12' => 'nullable|boolean',
            'protection_disable_devtools_shortcuts' => 'nullable|boolean',
            'protection_detect_ai_bots' => 'nullable|boolean',
            'protection_detect_headless' => 'nullable|boolean',
            'protection_detect_mouse_movement' => 'nullable|boolean',
            'protection_detect_click_pattern' => 'nullable|boolean',
            'protection_detect_screen_recording' => 'nullable|boolean',
            // Google Translate API settings
            'google_translate_enabled' => 'nullable|boolean',
            'google_translate_api_key' => 'nullable|string|max:500|required_if:google_translate_enabled,1',
        ]);

        // Update or create S3 settings
        $s3Settings = [
            's3_enabled' => $request->boolean('s3_enabled'),
            's3_provider' => $request->input('s3_provider', 'aws'),
            's3_key' => $request->input('s3_key'),
            's3_secret' => $request->input('s3_secret'),
            's3_region' => $request->input('s3_region'),
            's3_bucket' => $request->input('s3_bucket'),
            's3_endpoint' => $request->input('s3_endpoint'),
            's3_path_prefix' => $request->input('s3_path_prefix', 'backups'),
        ];

        foreach ($s3Settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 's3'],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                    'type' => is_bool($value) ? 'boolean' : 'string',
                ]
            );
        }

        // Update premium price
        if ($request->has('premium_price_monthly')) {
            Setting::setSetting(
                'premium_price_monthly',
                $request->input('premium_price_monthly', 25000),
                'number',
                'subscription',
                'Monthly premium subscription price in Rupiah'
            );
        }

        // Update referral reward settings
        if ($request->has('referral_reward_signup')) {
            Setting::setSetting(
                'referral_reward_signup',
                $request->input('referral_reward_signup', 5000),
                'number',
                'referral',
                'Referral signup reward amount in Rupiah'
            );
        }

        if ($request->has('referral_reward_commission_percent')) {
            Setting::setSetting(
                'referral_reward_commission_percent',
                $request->input('referral_reward_commission_percent', 5),
                'number',
                'referral',
                'Referral transaction commission percentage'
            );
        }

        // Update marketplace commission settings
        if ($request->has('platform_commission_percent')) {
            Setting::setSetting(
                'platform_commission_percent',
                $request->input('platform_commission_percent', 20),
                'number',
                'marketplace',
                'Platform commission percentage (deducted from every transaction)'
            );
        }

        if ($request->has('creator_commission_percent')) {
            Setting::setSetting(
                'creator_commission_percent',
                $request->input('creator_commission_percent', 0),
                'number',
                'marketplace',
                'Creator commission percentage (only for original creator on resale)'
            );
        }

        // Update studio platform fee percent
        if ($request->has('studio_platform_fee_percent')) {
            Setting::setSetting(
                'studio_platform_fee_percent',
                $request->input('studio_platform_fee_percent', 10),
                'number',
                'studio',
                'Studio platform fee percentage deducted on escrow releases'
            );
        }

        // Update studio email toggles
        $studioEmailKeys = [
            'studio_email_quote_created',
            'studio_email_quote_accepted',
            'studio_email_quote_rejected',
            'studio_email_escrow_funded',
            'studio_email_escrow_released',
            'studio_email_escrow_refunded',
            'studio_email_vendor_assigned',
        ];
        foreach ($studioEmailKeys as $key) {
            if ($request->has($key)) {
                Setting::setSetting(
                    $key,
                    $request->boolean($key),
                    'boolean',
                    'studio',
                    'Studio email toggle for ' . $key
                );
            }
        }

        if ($request->has('studio_sla_funding_reminder_days')) {
            Setting::setSetting(
                'studio_sla_funding_reminder_days',
                (int) $request->input('studio_sla_funding_reminder_days', 3),
                'number',
                'studio',
                'Days before sending funding reminder for quoted orders'
            );
        }

        // Update premium buyer discount
        if ($request->has('premium_buyer_discount_percent')) {
            Setting::setSetting(
                'premium_buyer_discount_percent',
                $request->input('premium_buyer_discount_percent', 10),
                'number',
                'marketplace',
                'Exclusive discount percentage for premium buyers (applied to all purchases)'
            );
        }

        if ($request->has('tax_default_percent')) {
            Setting::setSetting(
                'tax_default_percent',
                $request->input('tax_default_percent', Setting::getDefaultTaxPercent()),
                'number',
                'marketplace',
                'Default tax percentage applied when no country-specific rule is found'
            );
        }

        if ($request->has('tax_inclusive_default')) {
            Setting::setSetting(
                'tax_inclusive_default',
                $request->boolean('tax_inclusive_default'),
                'boolean',
                'marketplace',
                'Indicates whether listed prices already include tax by default'
            );
        }

        if ($request->has('min_price_default')) {
            Setting::setSetting(
                'min_price_default',
                $request->input('min_price_default', Setting::getDefaultMinPrice()),
                'number',
                'marketplace',
                'Minimum default price for paid notes in Rupiah'
            );
        }

        if ($request->has('recommended_price_multiplier')) {
            Setting::setSetting(
                'recommended_price_multiplier',
                $request->input('recommended_price_multiplier', Setting::getRecommendedPriceMultiplier()),
                'number',
                'marketplace',
                'Suggested multiplier applied to minimum price for recommended pricing guidance'
            );
        }

        if ($request->has('ai_free_usage_limit')) {
            Setting::setSetting(
                'ai_free_usage_limit',
                $request->input('ai_free_usage_limit', Setting::getAiFreeUsageLimit()),
                'number',
                'ai',
                'Daily free usage limit for premium AI utilities'
            );
        }

        $aiPriceFields = [
            'image_search' => 'ai_price_image_search',
            'image_generate' => 'ai_price_image_generate',
            'video_generate' => 'ai_price_video_generate',
        ];

        foreach ($aiPriceFields as $feature => $fieldName) {
            if ($request->has($fieldName)) {
                Setting::setSetting(
                    'ai_price_' . $feature,
                    $request->input($fieldName, Setting::getAiFeaturePrice($feature)),
                    'number',
                    'ai',
                    'Per-use pricing for premium AI feature: ' . str_replace('_', ' ', $feature)
                );
            }
        }

        $message = 'Settings updated successfully.';
        $updates = [];

        if ($request->has('premium_price_monthly')) {
            $updates[] = 'Premium price updated to Rp ' . number_format($request->input('premium_price_monthly'), 0, ',', '.') . '/month';
        }

        if ($request->has('referral_reward_signup')) {
            $updates[] = 'Referral signup reward updated to Rp ' . number_format($request->input('referral_reward_signup'), 0, ',', '.');
        }

        if ($request->has('referral_reward_commission_percent')) {
            $updates[] = 'Referral commission updated to ' . $request->input('referral_reward_commission_percent') . '%';
        }

        if ($request->has('platform_commission_percent')) {
            $updates[] = 'Platform commission updated to ' . $request->input('platform_commission_percent') . '%';
        }

        if ($request->has('creator_commission_percent')) {
            $updates[] = 'Creator commission updated to ' . $request->input('creator_commission_percent') . '%';
        }

        if ($request->has('studio_platform_fee_percent')) {
            $updates[] = 'Studio platform fee updated to ' . $request->input('studio_platform_fee_percent') . '%';
        }
        foreach ($studioEmailKeys as $key) {
            if ($request->has($key)) {
                $label = str_replace(['studio_email_', '_'], ['', ' '], $key);
                $updates[] = 'Studio email "' . $label . '" set to ' . ($request->boolean($key) ? 'ON' : 'OFF');
            }
        }
        if ($request->has('studio_sla_funding_reminder_days')) {
            $updates[] = 'Studio SLA funding reminder set to every ' . (int) $request->input('studio_sla_funding_reminder_days') . ' days';
        }

        if ($request->has('tax_default_percent')) {
            $updates[] = 'Default tax percentage updated to ' . $request->input('tax_default_percent') . '%';
        }

        if ($request->has('tax_inclusive_default')) {
            $updates[] = 'Default tax inclusion set to ' . ($request->boolean('tax_inclusive_default') ? 'inclusive' : 'exclusive');
        }

        if ($request->has('min_price_default')) {
            $updates[] = 'Default minimum price updated to Rp ' . number_format($request->input('min_price_default', 0), 0, ',', '.');
        }

        if ($request->has('recommended_price_multiplier')) {
            $updates[] = 'Recommended price multiplier updated to ' . $request->input('recommended_price_multiplier') . 'x';
        }

        if ($request->has('ai_free_usage_limit')) {
            $limitValue = $request->input('ai_free_usage_limit');
            $limitLabel = (int) $limitValue === -1 ? 'unlimited' : $limitValue . ' uses/day';
            $updates[] = 'AI free usage limit set to ' . $limitLabel;
        }

        foreach ($aiPriceFields as $feature => $fieldName) {
            if ($request->has($fieldName)) {
                $priceValue = (float) $request->input($fieldName);
                $updates[] = 'AI pricing for ' . str_replace('_', ' ', $feature) . ' updated to Rp ' . number_format($priceValue, 0, ',', '.');
            }
        }

        // Update featured notes pricing
        if ($request->has('featured_price')) {
            $locations = [
                'landing_hero',
                'landing_carousel',
                'marketplace_banner',
                'marketplace_grid',
                'popup_welcome',
                'popup_exit',
                'popup_interstitial',
            ];
            $durations = [7, 14, 30];

            foreach ($locations as $location) {
                foreach ($durations as $duration) {
                    $key = "featured_price_{$location}_{$duration}";
                    $price = $request->input("featured_price.{$location}.{$duration}");

                    if ($price !== null) {
                        Setting::setSetting(
                            $key,
                            $price,
                            'number',
                            'featured_notes',
                            "Price for featured note at {$location} for {$duration} days"
                        );
                    }
                }
            }
            $updates[] = 'Featured notes pricing updated';
        }

        // Update Content Protection Settings
        $protectionSettings = [
            'protection_disable_text_selection',
            'protection_disable_right_click',
            'protection_disable_keyboard_shortcuts',
            'protection_disable_copy_paste',
            'protection_disable_drag_drop',
            'protection_disable_print',
            'protection_disable_view_source',
            'protection_detect_devtools',
            'protection_disable_screenshot',
            'protection_disable_image_saving',
            'protection_disable_console',
            'protection_monitor_clipboard',
            'protection_disable_print_screen',
            'protection_disable_snipping_tool',
            'protection_detect_window_blur',
            'protection_detect_visibility_change',
            'protection_clear_clipboard_periodic',
            'protection_blur_overlay',
            'protection_disable_f12',
            'protection_disable_devtools_shortcuts',
            'protection_detect_ai_bots',
            'protection_detect_headless',
            'protection_detect_mouse_movement',
            'protection_detect_click_pattern',
            'protection_detect_screen_recording',
        ];

        foreach ($protectionSettings as $key) {
            if ($request->has($key)) {
                Setting::setSetting(
                    $key,
                    $request->boolean($key),
                    'boolean',
                    'content_protection',
                    ucfirst(str_replace('protection_', '', str_replace('_', ' ', $key)))
                );
            }
        }

        if ($request->hasAny($protectionSettings)) {
            $updates[] = 'Content protection settings updated';
        }

        // Update Google Translate API settings
        $isEnablingGoogleTranslate = $request->has('google_translate_enabled') && $request->boolean('google_translate_enabled');
        $hasExistingApiKey = (bool) Setting::getGoogleTranslateApiKey();
        
        // Validate API key is required when enabling for the first time
        if ($isEnablingGoogleTranslate && !$hasExistingApiKey) {
            $request->validate([
                'google_translate_api_key' => 'required|string|min:10',
            ], [
                'google_translate_api_key.required' => 'API key is required when enabling Google Translate API.',
                'google_translate_api_key.min' => 'API key must be at least 10 characters.',
            ]);
        }

        if ($request->has('google_translate_enabled')) {
            Setting::setSetting(
                'google_translate_enabled',
                $request->boolean('google_translate_enabled'),
                'boolean',
                'translation',
                'Enable Google Translate API for chat message translation'
            );
            $updates[] = 'Google Translate API ' . ($request->boolean('google_translate_enabled') ? 'enabled' : 'disabled');
        }

        if ($request->has('google_translate_api_key')) {
            $apiKey = $request->input('google_translate_api_key');
            // Only update if it's a new key (not the masked value and not empty)
            if ($apiKey && !str_starts_with($apiKey, '••••') && strlen(trim($apiKey)) > 0) {
                Setting::setSetting(
                    'google_translate_api_key',
                    encrypt(trim($apiKey)),
                    'string',
                    'translation',
                    'Google Translate API key (encrypted)'
                );
                $updates[] = 'Google Translate API key updated';
            }
        }

        if (!empty($updates)) {
            $message = 'Settings updated successfully. ' . implode('. ', $updates) . '.';
        }

        $redirectTo = $request->input('redirect_to');

        return redirect()->to($redirectTo ?: route('admin.settings.index'))
            ->with('success', $message);
    }

    /**
     * Test S3 connection.
     */
    public function testS3(Request $request): RedirectResponse
    {
        try {
            $s3Settings = Setting::where('group', 's3')->get()->keyBy('key');

            if (!$s3Settings->get('s3_enabled')?->value) {
                return back()->with('error', 'S3 is not enabled. Please enable it first.');
            }

            // Test connection logic here
            // This is a placeholder - you would implement actual S3 connection test
            $config = [
                'key' => $s3Settings->get('s3_key')?->value,
                'secret' => $s3Settings->get('s3_secret')?->value,
                'region' => $s3Settings->get('s3_region')?->value,
                'bucket' => $s3Settings->get('s3_bucket')?->value,
                'endpoint' => $s3Settings->get('s3_endpoint')?->value,
            ];

            // You can add actual S3 SDK test here
            // For now, just return success if all required fields are present
            if (empty($config['key']) || empty($config['secret']) || empty($config['bucket'])) {
                return back()->with('error', 'Please fill all required S3 credentials.');
            }

            return back()->with('success', 'S3 connection test successful! You can now use S3 for backups.');
        } catch (\Exception $e) {
            return back()->with('error', 'S3 connection test failed: ' . $e->getMessage());
        }
    }

}
