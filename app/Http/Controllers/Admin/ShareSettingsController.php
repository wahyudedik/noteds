<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShareSettingsController extends Controller
{
    /**
     * Display share settings page.
     */
    public function index(): View
    {
        // Get share analytics settings
        $shareCommissionPercent = Setting::getSetting('share_commission_percent', 'marketplace', 5.0);
        $shareMonthlyPayoutDay = (int) Setting::getSetting('share_monthly_payout_day', 'marketplace', 1);
        $shareMaxSharesPerUserPerLink = (int) Setting::getSetting('share_max_shares_per_user_per_link', 'marketplace', 1);
        $shareCommissionPaymentMode = Setting::getSetting('share_commission_payment_mode', 'marketplace', 'monthly');

        return view('admin.share-settings.index', compact(
            'shareCommissionPercent',
            'shareMonthlyPayoutDay',
            'shareMaxSharesPerUserPerLink',
            'shareCommissionPaymentMode'
        ));
    }

    /**
     * Update share settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'share_commission_percent' => 'required|numeric|min:0|max:100',
            'share_monthly_payout_day' => 'required|integer|min:1|max:31',
            'share_max_shares_per_user_per_link' => 'required|integer|min:1|max:1000',
            'share_commission_payment_mode' => 'required|in:monthly,immediate',
        ]);

        // Save settings
        Setting::setSetting('share_commission_percent', (float) $validated['share_commission_percent'], 'marketplace');
        Setting::setSetting('share_monthly_payout_day', (int) $validated['share_monthly_payout_day'], 'marketplace');
        Setting::setSetting('share_max_shares_per_user_per_link', (int) $validated['share_max_shares_per_user_per_link'], 'marketplace');
        Setting::setSetting('share_commission_payment_mode', $validated['share_commission_payment_mode'], 'marketplace');

        return redirect()->route('admin.share-settings.index')
            ->with('success', 'Share settings updated successfully.');
    }
}
