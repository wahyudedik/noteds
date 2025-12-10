<?php

namespace App\Http\Controllers;

use App\Models\ContestSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminContestSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'role:admin']);
    }

    /**
     * Show contest settings form
     */
    public function index(): View
    {
        $setting = ContestSetting::first();

        // Create default setting if not exists
        if (!$setting) {
            $setting = ContestSetting::create([
                'enabled' => true,
                'platform_fee_percentage' => 10,
                'terms_and_conditions' => null,
                'approval_guidelines' => null,
                'max_contests_per_buyer' => 10,
                'max_prize_amount' => null,
                'require_kyc' => false,
                'auto_distribute_prizes' => true,
            ]);
        }

        return view('admin.contests.settings', [
            'setting' => $setting,
        ]);
    }

    /**
     * Update contest settings
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'platform_fee_percentage' => 'required|numeric|min:0|max:100',
            'terms_and_conditions' => 'nullable|string',
            'approval_guidelines' => 'nullable|string',
            'max_contests_per_buyer' => 'required|integer|min:1',
            'max_prize_amount' => 'nullable|numeric|min:0',
            'require_kyc' => 'required|boolean',
            'auto_distribute_prizes' => 'required|boolean',
        ]);

        // Convert string boolean values to actual booleans
        $validated['enabled'] = $request->has('enabled') && $request->enabled === 'on' ? true : false;
        $validated['require_kyc'] = $request->has('require_kyc') && $request->require_kyc === 'on' ? true : false;
        $validated['auto_distribute_prizes'] = $request->has('auto_distribute_prizes') && $request->auto_distribute_prizes === 'on' ? true : false;

        $setting = ContestSetting::first();

        if (!$setting) {
            $setting = ContestSetting::create($validated);
        } else {
            $setting->update($validated);
        }

        return redirect()->route('admin.contests.settings')
            ->with('success', 'Contest settings updated successfully!');
    }
}
