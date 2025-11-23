<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuyerProtectionSetting;
use App\Services\BuyerProtectionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BuyerProtectionController extends Controller
{
    public function __construct(
        private BuyerProtectionService $buyerProtectionService
    ) {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Show settings
     */
    public function index(): View
    {
        $settings = $this->buyerProtectionService->getSettings();

        return view('admin.buyer-protection.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'money_back_guarantee_enabled' => 'boolean',
            'money_back_guarantee_days' => 'required|integer|min:1|max:365',
            'auto_approve_refunds' => 'boolean',
            'max_refund_amount' => 'nullable|numeric|min:0',
            'refund_policy_rules' => 'nullable|array',
            'quality_assurance_enabled' => 'boolean',
            'quality_check_criteria' => 'nullable|array',
            'dispute_resolution_enabled' => 'boolean',
            'dispute_resolution_days' => 'required|integer|min:1|max:90',
        ]);

        $settings = $this->buyerProtectionService->getSettings();
        $settings->update($validated);

        return redirect()->route('admin.buyer-protection.index')
            ->with('success', 'Buyer protection settings updated successfully.');
    }
}

