<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\CartItem;
use App\Services\MarketplaceService;
use App\Services\MidtransService;
use App\Services\OrderTrackingService;
use App\Services\OrderModificationService;
use App\Services\BulkOrderService;
use App\Services\OrderExportService;
use App\Http\Requests\ModifyOrderRequest;
use App\Http\Requests\CreateBulkOrderRequest;
use App\Http\Requests\ExportOrdersRequest;
use App\Events\OrderStatusUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function __construct(
        private MarketplaceService $marketplaceService,
        private MidtransService $midtransService,
        private OrderTrackingService $trackingService,
        private OrderModificationService $modificationService,
        private BulkOrderService $bulkOrderService,
        private OrderExportService $exportService
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

    public function cancel(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        if (!$order->canBeCancelled()) {
            return back()->withErrors(['error' => 'Cannot cancel paid or already cancelled order']);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->input('reason'),
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
            ]);

            // Add tracking entry
            $tracking = $this->trackingService->addTracking(
                $order,
                'cancelled',
                $order->payment_status,
                'Order cancelled by user',
                auth()->user()
            );

            // Broadcast event
            event(new OrderStatusUpdated($order, $tracking));

            // Restore stock if order had items
            if ($order->isBulkOrder() && $order->items->isNotEmpty()) {
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->stock !== null) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            } elseif ($order->product && $order->product->stock !== null) {
                $order->product->increment('stock', $order->quantity);
            }
        });

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

    /**
     * Get real-time tracking status.
     */
    public function track(Order $order)
    {
        $this->authorize('view', $order);

        $latestTracking = $this->trackingService->getLatestStatus($order);

        return response()->json([
            'order' => $order->load(['product', 'items.product']),
            'latest_status' => $latestTracking,
            'can_be_cancelled' => $order->canBeCancelled(),
            'can_be_modified' => $order->canBeModified(),
        ]);
    }

    /**
     * Get tracking timeline/history.
     */
    public function tracking(Order $order)
    {
        $this->authorize('view', $order);

        $timeline = $this->trackingService->getTrackingTimeline($order);

        return Inertia::render('Marketplace/Orders/Tracking', [
            'order' => $order->load(['product', 'items.product', 'buyer']),
            'timeline' => $timeline,
        ]);
    }

    /**
     * Poll for status updates (for real-time tracking).
     */
    public function poll(Order $order)
    {
        $this->authorize('view', $order);

        $latestTracking = $this->trackingService->getLatestStatus($order);
        $order->refresh();

        return response()->json([
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'latest_tracking' => $latestTracking,
            'last_tracked_at' => $order->last_tracked_at?->toIso8601String(),
        ]);
    }

    /**
     * Modify order.
     */
    public function modify(ModifyOrderRequest $request, Order $order)
    {
        try {
            $changes = [];
            if ($request->has('quantity')) {
                $changes['quantity'] = $request->quantity;
            }
            if ($request->has('product_id')) {
                $changes['product_id'] = $request->product_id;
            }
            if ($request->has('coupon_code')) {
                $changes['coupon_code'] = $request->coupon_code;
            }

            $order = $this->modificationService->modifyOrder(
                $order,
                $changes,
                auth()->user(),
                $request->reason
            );

            $latestTracking = $this->trackingService->getLatestStatus($order);
            event(new OrderStatusUpdated($order, $latestTracking));

            return back()->with('success', 'Order modified successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Create bulk order (single order with multiple items).
     */
    public function createBulkOrder(CreateBulkOrderRequest $request)
    {
        try {
            if ($request->order_type === 'single') {
                $order = $this->bulkOrderService->createBulkOrder(
                    $request->items,
                    auth()->user(),
                    $request->coupon_code
                );

                // Create payment transaction
                $payment = $this->midtransService->createTransaction($order);

                if (!$payment['success']) {
                    return back()->withErrors(['payment' => $payment['message']]);
                }

                return Inertia::render('Marketplace/Payment', [
                    'order' => $order->load(['items.product']),
                    'snap_token' => $payment['snap_token'],
                ]);
            } else {
                // Multiple separate orders
                $orders = $this->bulkOrderService->createMultipleOrders(
                    $request->items,
                    auth()->user()
                );

                return redirect()->route('marketplace.orders.index')
                    ->with('success', count($orders) . ' order(s) created successfully. Please complete payment for each order.');
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Export order history.
     */
    public function exportHistory(ExportOrdersRequest $request)
    {
        $query = Order::where('user_id', auth()->id())
            ->with(['product', 'items.product', 'trackingHistory']);

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

        $orders = $query->latest()->get();

        try {
            if ($request->format === 'pdf') {
                $filepath = $this->exportService->exportToPdf($orders, auth()->user());
                return response()->download($filepath)->deleteFileAfterSend(true);
            } elseif ($request->format === 'excel' || $request->format === 'csv') {
                return $this->exportService->exportToCsv($orders, auth()->user());
            }

            return back()->withErrors(['error' => 'Invalid export format']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Export failed: ' . $e->getMessage()]);
        }
    }
}
