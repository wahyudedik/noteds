<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MarketplaceCommissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminMarketplaceSettingsController extends Controller
{
    public function __construct(
        private MarketplaceCommissionService $commissionService
    ) {}

    /**
     * Show marketplace settings page.
     */
    public function index()
    {
        $settings = $this->commissionService->getCommissionSettings();

        return Inertia::render('Admin/Marketplace/Settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update marketplace commission settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'flat_fee' => ['sometimes', 'numeric', 'min:0'],
        ], [
            'percentage.min' => 'Commission percentage cannot be negative.',
            'percentage.max' => 'Commission percentage cannot exceed 100%.',
            'flat_fee.min' => 'Flat fee cannot be negative.',
        ]);

        try {
            $this->commissionService->updateCommissionSettings($validated);

            return redirect()->route('admin.marketplace.settings')
                ->with('success', 'Marketplace commission settings updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update settings. Please try again.']);
        }
    }
}