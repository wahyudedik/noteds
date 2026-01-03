<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\CartItem;
use App\Services\MarketplaceService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function __construct(
        private MarketplaceService $marketplaceService,
        private MidtransService $midtransService
    ) {}

    public function index(Request $request)
    {
        $query = Order::where('user_id', auth()->id())
            ->with(['product', 'product.seller']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(15);

        return Inertia::render('Marketplace/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'date_from', 'date_to']),
        ]);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['product', 'product.seller']);

        // Check payment status from Midtrans if still pending
        // Only check if payment is still pending - if it's paid, webhook already handled it
        if ($order->payment_status === 'pending' && $order->midtrans_order_id) {
            try {
                $status = $this->midtransService->checkTransactionStatus($order->midtrans_order_id);
                if ($status) {
                    $transactionStatus = $status['transaction_status'] ?? null;
                    // Only 'settlement' means payment is confirmed - 'capture' is not final
                    if ($transactionStatus === 'settlement') {
                        // Update order status if payment is confirmed (settlement)
                        $order->markAsPaid();
                        $order->refresh();
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't fail the page load
                \Log::warning('Failed to check Midtrans status: ' . $e->getMessage());
            }
        }

        return Inertia::render('Marketplace/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function reorder(Order $order)
    {
        // Validate ownership
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Create new order with same product and quantity
        $newOrder = Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'user_id' => auth()->id(),
            'product_id' => $order->product_id,
            'quantity' => $order->quantity,
            'price' => $order->product->price,
            'total' => $order->product->price * $order->quantity,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        // Redirect to payment page
        return redirect()->route('marketplace.payment', ['order' => $newOrder->id]);
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

    public function storeFromCart(Request $request)
    {
        $validated = $request->validate([
            'cart_items' => 'nullable|array',
            'cart_items.*' => 'exists:cart_items,id',
        ]);

        $userId = auth()->id();

        // Get cart items - either specific items or all items in cart
        $cartItemsQuery = CartItem::where('user_id', $userId)->with('product');
        
        if (!empty($validated['cart_items'])) {
            $cartItemsQuery->whereIn('id', $validated['cart_items']);
        }

        $cartItems = $cartItemsQuery->get();

        if ($cartItems->isEmpty()) {
            return back()->withErrors(['error' => 'Cart is empty']);
        }

        $orders = [];
        $errors = [];
        $orderedCartItemIds = [];

        DB::beginTransaction();
        try {
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;

                // Validate product availability
                if (!$product->is_active) {
                    $errors[] = "Product '{$product->name}' is not available";
                    continue;
                }

                // Validate stock
                if ($product->stock !== null && $product->stock < $cartItem->quantity) {
                    $errors[] = "Insufficient stock for '{$product->name}'";
                    continue;
                }

                // Create order
                $order = $this->marketplaceService->createOrder(
                    $product,
                    $userId,
                    $cartItem->quantity
                );

                $orders[] = $order;
                $orderedCartItemIds[] = $cartItem->id;
            }

            if (empty($orders)) {
                DB::rollBack();
                return back()->withErrors(['error' => implode(', ', $errors)]);
            }

            // Clear cart items that were successfully ordered
            CartItem::whereIn('id', $orderedCartItemIds)->delete();

            DB::commit();

            // If there were any errors but some orders were created, show warning
            if (!empty($errors)) {
                return redirect()->route('marketplace.orders.index')
                    ->with('success', count($orders) . ' order(s) created successfully')
                    ->with('warning', implode(', ', $errors));
            }

            return redirect()->route('marketplace.orders.index')
                ->with('success', count($orders) . ' order(s) created successfully. Please complete payment for each order.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create orders: ' . $e->getMessage()]);
        }
    }

    public function downloadInvoice(Order $order)
    {
        $this->authorize('view', $order);

        // Only allow download for paid or completed orders
        if (!in_array($order->payment_status, ['paid', 'completed'])) {
            abort(403, 'Invoice is only available for paid or completed orders');
        }

        $order->load(['product', 'product.seller', 'buyer']);

        $pdf = Pdf::loadView('marketplace.invoice', [
            'order' => $order,
            'buyer' => $order->buyer,
            'seller' => $order->product->seller,
            'product' => $order->product,
        ]);

        return $pdf->download('invoice-' . $order->order_number . '.pdf');
    }
}
