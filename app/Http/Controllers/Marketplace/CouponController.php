<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CouponController extends Controller
{
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Validate coupon code.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'product_id' => 'nullable|uuid|exists:products,id',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $product = null;
        if ($request->product_id) {
            $product = \App\Models\Product::find($request->product_id);
        }

        $result = $this->couponService->validateCoupon(
            $request->code,
            auth()->user(),
            $product,
            (float) ($request->amount ?? 0)
        );

        return response()->json($result);
    }

    /**
     * Apply coupon to order.
     */
    public function apply(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // Verify order ownership
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $this->couponService->applyCoupon($order, $request->code);
            return back()->with('success', 'Coupon applied successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
