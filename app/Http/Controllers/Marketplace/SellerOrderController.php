<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class SellerOrderController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Order::whereHas('product', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['product', 'buyer']);

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

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Marketplace/Seller/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'date_from', 'date_to']),
        ]);
    }

    public function show(Order $order)
    {
        // Validate that user is the seller
        if ($order->product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $order->load(['product', 'buyer']);

        return Inertia::render('Marketplace/Seller/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        // Validate that user is the seller
        if ($order->product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'required|in:processing,completed,cancelled',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        // Send notification based on status change
        if ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled') {
            // Notify buyer and seller about cancellation
            $reason = $validated['reason'] ?? 'Order has been cancelled by seller';
            $this->notificationService->notifyOrderCancelled($order, $reason);
        } elseif ($order->buyer) {
            // Send regular status update notification
            $this->notificationService->notifyOrderStatusUpdate($order);
        }

        return back()->with('success', 'Order status updated successfully');
    }

    public function downloadInvoice(Order $order)
    {
        // Validate that user is the seller
        if ($order->product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

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

