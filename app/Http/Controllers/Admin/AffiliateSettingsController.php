<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AffiliateSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display affiliate settings page.
     */
    public function index(): View
    {
        // Get current affiliate settings
        $settings = [
            'affiliate_commission_tier_1' => Setting::getSetting('affiliate_commission_tier_1', 'marketplace', 0.5),
            'affiliate_commission_tier_2' => Setting::getSetting('affiliate_commission_tier_2', 'marketplace', 1),
            'affiliate_commission_tier_3' => Setting::getSetting('affiliate_commission_tier_3', 'marketplace', 2),
            'affiliate_commission_tier_4' => Setting::getSetting('affiliate_commission_tier_4', 'marketplace', 5),
            'affiliate_commission_tier_5' => Setting::getSetting('affiliate_commission_tier_5', 'marketplace', 10),
            'affiliate_commission_tier_6' => Setting::getSetting('affiliate_commission_tier_6', 'marketplace', 15),
            'affiliate_conversion_threshold_1' => Setting::getSetting('affiliate_conversion_threshold_1', 'marketplace', 10),
            'affiliate_conversion_threshold_2' => Setting::getSetting('affiliate_conversion_threshold_2', 'marketplace', 50),
            'affiliate_conversion_threshold_3' => Setting::getSetting('affiliate_conversion_threshold_3', 'marketplace', 100),
            'affiliate_conversion_threshold_4' => Setting::getSetting('affiliate_conversion_threshold_4', 'marketplace', 250),
            'affiliate_conversion_threshold_5' => Setting::getSetting('affiliate_conversion_threshold_5', 'marketplace', 500),
            'affiliate_conversion_threshold_6' => Setting::getSetting('affiliate_conversion_threshold_6', 'marketplace', 1000),
            'affiliate_min_payout_amount' => Setting::getSetting('affiliate_min_payout_amount', 'marketplace', 50),
            'affiliate_payout_day' => Setting::getSetting('affiliate_payout_day', 'marketplace', 1),
        ];

        return view('admin.settings.affiliate', compact('settings'));
    }

    /**
     * Update affiliate settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'affiliate_commission_tier_1' => ['required', 'numeric', 'min:0', 'max:100'],
            'affiliate_commission_tier_2' => ['required', 'numeric', 'min:0', 'max:100'],
            'affiliate_commission_tier_3' => ['required', 'numeric', 'min:0', 'max:100'],
            'affiliate_commission_tier_4' => ['required', 'numeric', 'min:0', 'max:100'],
            'affiliate_commission_tier_5' => ['required', 'numeric', 'min:0', 'max:100'],
            'affiliate_commission_tier_6' => ['required', 'numeric', 'min:0', 'max:100'],
            'affiliate_conversion_threshold_1' => ['required', 'integer', 'min:1'],
            'affiliate_conversion_threshold_2' => ['required', 'integer', 'min:1'],
            'affiliate_conversion_threshold_3' => ['required', 'integer', 'min:1'],
            'affiliate_conversion_threshold_4' => ['required', 'integer', 'min:1'],
            'affiliate_conversion_threshold_5' => ['required', 'integer', 'min:1'],
            'affiliate_conversion_threshold_6' => ['required', 'integer', 'min:1'],
            'affiliate_min_payout_amount' => ['required', 'numeric', 'min:0.01'],
            'affiliate_payout_day' => ['required', 'integer', 'min:1', 'max:31'],
        ]);

        // Update each setting
        foreach ($validated as $key => $value) {
            Setting::setSetting(
                $key,
                $value,
                in_array($key, ['affiliate_payout_day']) ? 'number' : 'decimal',
                'marketplace',
                $this->getSettingDescription($key)
            );
        }

        return redirect()->route('admin.affiliate-settings.index')
            ->with('success', __('affiliate.settings_updated'));
    }

    /**
     * Get setting description for UI display.
     */
    private function getSettingDescription(string $key): string
    {
        $descriptions = [
            'affiliate_commission_tier_1' => 'Affiliate commission percentage for Tier 1 (0-9 conversions)',
            'affiliate_commission_tier_2' => 'Affiliate commission percentage for Tier 2 (10-49 conversions)',
            'affiliate_commission_tier_3' => 'Affiliate commission percentage for Tier 3 (50-99 conversions)',
            'affiliate_commission_tier_4' => 'Affiliate commission percentage for Tier 4 (100-249 conversions)',
            'affiliate_commission_tier_5' => 'Affiliate commission percentage for Tier 5 (250-499 conversions)',
            'affiliate_commission_tier_6' => 'Affiliate commission percentage for Tier 6 (500+ conversions)',
            'affiliate_conversion_threshold_1' => 'Conversion threshold to reach Tier 2',
            'affiliate_conversion_threshold_2' => 'Conversion threshold to reach Tier 3',
            'affiliate_conversion_threshold_3' => 'Conversion threshold to reach Tier 4',
            'affiliate_conversion_threshold_4' => 'Conversion threshold to reach Tier 5',
            'affiliate_conversion_threshold_5' => 'Conversion threshold to reach Tier 6',
            'affiliate_conversion_threshold_6' => 'Maximum conversion threshold',
            'affiliate_min_payout_amount' => 'Minimum amount affiliates can request for payout',
            'affiliate_payout_day' => 'Day of month to auto-transfer affiliate commissions (1-31)',
        ];

        return $descriptions[$key] ?? '';
    }
}
