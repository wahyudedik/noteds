<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ClipperCommissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AdminClipperSettingsController extends Controller
{
    public function __construct(
        private ClipperCommissionService $commissionService
    ) {}

    public function index()
    {
        $settings = $this->commissionService->getSettings();
        return Inertia::render('Admin/Clipper/Settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'platform_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            $this->commissionService->updatePlatformFeePercent((float) $validated['platform_fee_percent']);
            return redirect()->back()->with('success', 'Clipper platform fee settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update clipper platform fee settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}

