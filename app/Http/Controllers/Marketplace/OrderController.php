<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\MarketplaceService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private MarketplaceService $marketplaceService,
        private MidtransService $midtransService
    ) {}

    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['product', 'product.seller'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Marketplace/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['product', 'product.seller']);

        return Inertia::render('Marketplace/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        try {
            $order = $this->marketplaceService->createOrder(
                $product,
                auth()->id(),
                $validated['quantity']
            );

            $payment = $this->midtransService->createTransaction($order);

            if (!$payment['success']) {
                return back()->withErrors(['payment' => $payment['message']]);
            }

            return Inertia::render('Marketplace/Payment', [
                'order' => $order->load('product'),
                'snap_token' => $payment['snap_token'],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Order $order)
    {
        $this->authorize('update', $order);

        if ($order->payment_status === 'paid') {
            return back()->withErrors(['error' => 'Cannot cancel paid order']);
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('marketplace.orders.index')
            ->with('success', 'Order cancelled successfully.');
    }
}
