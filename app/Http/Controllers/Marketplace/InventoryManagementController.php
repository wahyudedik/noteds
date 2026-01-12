<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\InventoryManagementService;
use App\Http\Requests\UpdateStockRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryManagementController extends Controller
{
    public function __construct(
        private InventoryManagementService $inventoryService
    ) {}

    /**
     * List all products with stock status.
     */
    public function index(Request $request): Response
    {
        $seller = $request->user();
        $filter = $request->get('filter', 'all'); // all, low_stock, out_of_stock

        $query = Product::where('user_id', $seller->id);

        if ($filter === 'low_stock') {
            $query->lowStock();
        } elseif ($filter === 'out_of_stock') {
            $query->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '<=', 0);
            });
        }

        $products = $query->with(['seller'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Marketplace/Seller/Inventory/Index', [
            'products' => $products,
            'filter' => $filter,
        ]);
    }

    /**
     * Show product stock details and history.
     */
    public function show(Product $product, Request $request): Response
    {
        // Authorize: seller must own the product
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $product->load(['seller', 'stockHistory.updatedBy', 'stockHistory.order']);

        $stockHistory = $this->inventoryService->getStockHistory($product, 30);

        return Inertia::render('Marketplace/Seller/Inventory/Show', [
            'product' => $product,
            'stock_history' => $stockHistory,
        ]);
    }

    /**
     * Update stock manually.
     */
    public function updateStock(UpdateStockRequest $request, Product $product)
    {
        // Authorize: seller must own the product
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validated();
        $quantity = (int) $validated['quantity'];
        $type = $validated['type'];
        $reason = $validated['reason'] ?? null;

        if ($type === 'restock') {
            $history = $this->inventoryService->recordRestock(
                $product,
                $quantity,
                $reason,
                $request->user()
            );
        } else { // adjustment
            $newQuantity = $quantity;
            $history = $this->inventoryService->recordAdjustment(
                $product,
                $newQuantity,
                $reason ?? 'Manual adjustment',
                $request->user()
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Stock updated successfully',
                'product' => $product->fresh(),
                'history' => $history,
            ]);
        }

        return back()->with('success', 'Stock updated successfully');
    }

    /**
     * Restock product.
     */
    public function restock(UpdateStockRequest $request, Product $product)
    {
        // Authorize: seller must own the product
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validated();
        $quantity = (int) $validated['quantity'];
        $reason = $validated['reason'] ?? 'Manual restock';

        $history = $this->inventoryService->recordRestock(
            $product,
            $quantity,
            $reason,
            $request->user()
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product restocked successfully',
                'product' => $product->fresh(),
                'history' => $history,
            ]);
        }

        return back()->with('success', 'Product restocked successfully');
    }

    /**
     * Manual stock adjustment.
     */
    public function adjustStock(UpdateStockRequest $request, Product $product)
    {
        // Authorize: seller must own the product
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validated();
        $newQuantity = (int) $validated['quantity'];
        $reason = $validated['reason'] ?? 'Manual adjustment';

        $history = $this->inventoryService->recordAdjustment(
            $product,
            $newQuantity,
            $reason,
            $request->user()
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Stock adjusted successfully',
                'product' => $product->fresh(),
                'history' => $history,
            ]);
        }

        return back()->with('success', 'Stock adjusted successfully');
    }

    /**
     * Get stock history (API).
     */
    public function getStockHistory(Product $product, Request $request)
    {
        // Authorize: seller must own the product
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $days = $request->get('days', 30);
        $history = $this->inventoryService->getStockHistory($product, $days);

        return response()->json($history);
    }

    /**
     * Update low stock alert settings.
     */
    public function updateAlertSettings(Request $request)
    {
        $validated = $request->validate([
            'low_stock_alert_threshold' => ['nullable', 'integer', 'min:0'],
            'low_stock_alert_enabled' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $user->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Alert settings updated successfully',
                'settings' => [
                    'low_stock_alert_threshold' => $user->low_stock_alert_threshold,
                    'low_stock_alert_enabled' => $user->low_stock_alert_enabled,
                ],
            ]);
        }

        return back()->with('success', 'Alert settings updated successfully');
    }

    /**
     * Get low stock products (API).
     */
    public function getLowStockAlerts(Request $request)
    {
        $seller = $request->user();
        $lowStockProducts = $this->inventoryService->checkLowStockForSeller($seller);

        return response()->json([
            'products' => $lowStockProducts->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'stock' => $p->stock,
                'threshold' => $p->low_stock_threshold ?? $seller->low_stock_alert_threshold ?? config('seller.inventory.default_low_stock_threshold', 10),
            ]),
        ]);
    }
}
