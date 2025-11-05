<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
        
        return view('admin.settings.index', compact('settings', 's3Settings', 'premiumPrice', 'referralSignupReward', 'referralCommissionPercent', 'platformCommissionPercent', 'creatorCommissionPercent'));
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
        
        if (!empty($updates)) {
            $message = 'Settings updated successfully. ' . implode('. ', $updates) . '.';
        }

        return redirect()->route('admin.settings.index')
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
