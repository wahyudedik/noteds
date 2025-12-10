<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AdminSettingsController extends Controller
{
    /**
     * Show settings page
     *
     * @return View
     */
    public function index(): View
    {
        $this->authorize('manage-settings');

        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update general settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_description' => 'nullable|string|max:1000',
            'support_email' => 'required|email',
            'admin_email' => 'required|email',
            'timezone' => 'required|timezone',
            'currency' => 'required|string|max:3',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        $this->saveSetting('app_name', $request->app_name);
        $this->saveSetting('app_url', $request->app_url);
        $this->saveSetting('app_description', $request->app_description);
        $this->saveSetting('support_email', $request->support_email);
        $this->saveSetting('admin_email', $request->admin_email);
        $this->saveSetting('timezone', $request->timezone);
        $this->saveSetting('currency', $request->currency);
        $this->saveSetting('maintenance_mode', $request->has('maintenance_mode') ? '1' : '0');

        activity('admin')
            ->log('General settings updated');

        return redirect()->back()->with('success', 'Pengaturan umum berhasil disimpan');
    }

    /**
     * Update payment settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePayment(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $request->validate([
            'midtrans_merchant_id' => 'required|string',
            'midtrans_server_key' => 'required|string',
            'midtrans_client_key' => 'required|string',
            'midtrans_sandbox_mode' => 'nullable|boolean',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'seller_percentage' => 'required|numeric|min:0|max:100',
            'min_transaction_amount' => 'required|numeric|min:0',
        ]);

        // Validate percentages add up to 100
        if ($request->commission_percentage + $request->seller_percentage != 100) {
            return redirect()->back()->with('error', 'Persentase komisi dan penjual harus berjumlah 100%');
        }

        $this->saveSetting('midtrans_merchant_id', $request->midtrans_merchant_id);
        $this->saveSetting('midtrans_server_key', encrypt($request->midtrans_server_key));
        $this->saveSetting('midtrans_client_key', $request->midtrans_client_key);
        $this->saveSetting('midtrans_sandbox_mode', $request->has('midtrans_sandbox_mode') ? '1' : '0');
        $this->saveSetting('commission_percentage', $request->commission_percentage);
        $this->saveSetting('seller_percentage', $request->seller_percentage);
        $this->saveSetting('min_transaction_amount', $request->min_transaction_amount);

        activity('admin')
            ->log('Payment settings updated');

        return redirect()->back()->with('success', 'Pengaturan pembayaran berhasil disimpan');
    }

    /**
     * Update affiliate settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateAffiliate(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $request->validate([
            'affiliate_tier1_min' => 'required|numeric|min:0',
            'affiliate_tier1_max' => 'required|numeric|min:0',
            'affiliate_tier1_commission' => 'required|numeric|min:0|max:100',
            'affiliate_tier2_min' => 'required|numeric|min:0',
            'affiliate_tier2_max' => 'required|numeric|min:0',
            'affiliate_tier2_commission' => 'required|numeric|min:0|max:100',
            'affiliate_tier3_min' => 'required|numeric|min:0',
            'affiliate_tier3_commission' => 'required|numeric|min:0|max:100',
            'affiliate_link_validity_days' => 'required|numeric|min:1|max:365',
        ]);

        $this->saveSetting('affiliate_tier1_min', $request->affiliate_tier1_min);
        $this->saveSetting('affiliate_tier1_max', $request->affiliate_tier1_max);
        $this->saveSetting('affiliate_tier1_commission', $request->affiliate_tier1_commission);
        $this->saveSetting('affiliate_tier2_min', $request->affiliate_tier2_min);
        $this->saveSetting('affiliate_tier2_max', $request->affiliate_tier2_max);
        $this->saveSetting('affiliate_tier2_commission', $request->affiliate_tier2_commission);
        $this->saveSetting('affiliate_tier3_min', $request->affiliate_tier3_min);
        $this->saveSetting('affiliate_tier3_commission', $request->affiliate_tier3_commission);
        $this->saveSetting('affiliate_link_validity_days', $request->affiliate_link_validity_days);

        activity('admin')
            ->log('Affiliate settings updated');

        return redirect()->back()->with('success', 'Pengaturan affiliate berhasil disimpan');
    }

    /**
     * Update share to earn settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateShareToEarn(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $request->validate([
            'share_commission_percentage' => 'required|numeric|min:0|max:100',
            'share_max_daily_commission' => 'required|numeric|min:0',
            'share_to_earn_enabled' => 'nullable|boolean',
        ]);

        $this->saveSetting('share_commission_percentage', $request->share_commission_percentage);
        $this->saveSetting('share_max_daily_commission', $request->share_max_daily_commission);
        $this->saveSetting('share_to_earn_enabled', $request->has('share_to_earn_enabled') ? '1' : '0');

        activity('admin')
            ->log('Share to Earn settings updated');

        return redirect()->back()->with('success', 'Pengaturan Share to Earn berhasil disimpan');
    }

    /**
     * Update points settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePoints(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $request->validate([
            'points_per_purchase' => 'required|numeric|min:0',
            'points_value_in_rupiah' => 'required|numeric|min:0',
            'points_expiry_days' => 'required|numeric|min:0',
        ]);

        $this->saveSetting('points_per_purchase', $request->points_per_purchase);
        $this->saveSetting('points_value_in_rupiah', $request->points_value_in_rupiah);
        $this->saveSetting('points_expiry_days', $request->points_expiry_days);

        activity('admin')
            ->log('Points settings updated');

        return redirect()->back()->with('success', 'Pengaturan poin berhasil disimpan');
    }

    /**
     * Update email settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $request->validate([
            'mail_driver' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'required|email',
            'mail_password' => 'required|string',
            'mail_from_name' => 'required|string',
            'mail_from_address' => 'required|email',
        ]);

        $this->saveSetting('mail_driver', $request->mail_driver);
        $this->saveSetting('mail_host', $request->mail_host);
        $this->saveSetting('mail_port', $request->mail_port);
        $this->saveSetting('mail_username', $request->mail_username);
        $this->saveSetting('mail_password', encrypt($request->mail_password));
        $this->saveSetting('mail_from_name', $request->mail_from_name);
        $this->saveSetting('mail_from_address', $request->mail_from_address);

        activity('admin')
            ->log('Email settings updated');

        return redirect()->back()->with('success', 'Pengaturan email berhasil disimpan');
    }

    /**
     * Update security settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateSecurity(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $request->validate([
            'email_verification_required' => 'nullable|boolean',
            'kyc_verification_required' => 'nullable|boolean',
            'two_factor_auth_enabled' => 'nullable|boolean',
            'rate_limit_enabled' => 'nullable|boolean',
            'rate_limit_requests' => 'required|numeric|min:1',
            'rate_limit_minutes' => 'required|numeric|min:1',
        ]);

        $this->saveSetting('email_verification_required', $request->has('email_verification_required') ? '1' : '0');
        $this->saveSetting('kyc_verification_required', $request->has('kyc_verification_required') ? '1' : '0');
        $this->saveSetting('two_factor_auth_enabled', $request->has('two_factor_auth_enabled') ? '1' : '0');
        $this->saveSetting('rate_limit_enabled', $request->has('rate_limit_enabled') ? '1' : '0');
        $this->saveSetting('rate_limit_requests', $request->rate_limit_requests);
        $this->saveSetting('rate_limit_minutes', $request->rate_limit_minutes);

        activity('admin')
            ->log('Security settings updated');

        return redirect()->back()->with('success', 'Pengaturan keamanan berhasil disimpan');
    }

    /**
     * Save or update a setting
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    private function saveSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
