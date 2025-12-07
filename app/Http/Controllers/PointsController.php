<?php

namespace App\Http\Controllers;

use App\Models\Point;
use App\Models\PointRedemption;
use App\Models\PointsPricingConfig;
use App\Models\Setting;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PointsController extends Controller
{
    public function __construct(private PointsService $pointsService) {}

    /**
     * Display points dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $points = $user->points()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $redemptions = $user->pointRedemptions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_points' => $user->total_points,
            'total_earned' => $user->total_points_earned,
            'total_redeemed' => $user->total_points_redeemed,
            'expiring_soon' => $user->points()
                ->where('is_redeemed', false)
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addDays(30))
                ->sum('points'),
        ];

        // Get redemption options from database instead of hardcoded settings
        $discountConfigs = PointsPricingConfig::where('type', 'discount')
            ->where('is_active', true)
            ->orderBy('points_required')
            ->get()
            ->map(function ($config) {
                return [
                    'points' => (int) $config->points_required,
                    'discount_amount' => (float) $config->discount_amount,
                    'label' => $config->name,
                    'config_id' => $config->id,
                ];
            })
            ->toArray();

        $premiumConfigs = PointsPricingConfig::where('type', 'premium_feature')
            ->where('is_active', true)
            ->orderBy('points_required')
            ->get()
            ->map(function ($config) {
                return [
                    'points' => (int) $config->points_required,
                    'premium_days' => (int) $config->premium_days,
                    'label' => $config->name,
                    'config_id' => $config->id,
                ];
            })
            ->toArray();

        // Fallback to hardcoded values if no configs in database
        $redemptionOptions = [
            'discounts' => $discountConfigs ?: [
                [
                    'points' => Setting::getSetting('points_redemption_discount_1000', 'points', 1000),
                    'discount_amount' => 1000,
                    'label' => 'Rp 1,000 Discount',
                ],
                [
                    'points' => Setting::getSetting('points_redemption_discount_5000', 'points', 4500),
                    'discount_amount' => 5000,
                    'label' => 'Rp 5,000 Discount',
                ],
                [
                    'points' => Setting::getSetting('points_redemption_discount_10000', 'points', 8000),
                    'discount_amount' => 10000,
                    'label' => 'Rp 10,000 Discount',
                ],
            ],
            'premium' => $premiumConfigs ?: [
                [
                    'points' => Setting::getSetting('points_redemption_premium_7days', 'points', 5000),
                    'premium_days' => 7,
                    'label' => '7 Days Premium',
                ],
                [
                    'points' => Setting::getSetting('points_redemption_premium_30days', 'points', 20000),
                    'premium_days' => 30,
                    'label' => '30 Days Premium',
                ],
            ],
        ];

        return view('points.index', compact('points', 'redemptions', 'stats', 'redemptionOptions'));
    }

    /**
     * Redeem points for discount.
     */
    public function redeemDiscount(Request $request): RedirectResponse
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'discount_amount' => 'required|numeric|min:1',
        ]);

        try {
            $redemption = $this->pointsService->redeemForDiscount(
                auth()->user(),
                $request->points,
                $request->discount_amount,
                null,
                30 // Valid for 30 days
            );

            return redirect()->route('points.index')
                ->with('success', "Discount code generated: {$redemption->redemption_code}. Valid for 30 days.");
        } catch (\Exception $e) {
            return redirect()->route('points.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Redeem points for premium.
     */
    public function redeemPremium(Request $request): RedirectResponse
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'premium_days' => 'required|integer|min:1',
        ]);

        try {
            $redemption = $this->pointsService->redeemForPremium(
                auth()->user(),
                $request->points,
                $request->premium_days
            );

            return redirect()->route('points.index')
                ->with('success', "Premium access activated for {$request->premium_days} days!");
        } catch (\Exception $e) {
            return redirect()->route('points.index')
                ->with('error', $e->getMessage());
        }
    }
}
