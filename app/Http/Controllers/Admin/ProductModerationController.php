<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductModerationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $query = Product::with('seller');

        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->latest()->paginate(20);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'is_active' => 'boolean',
        ]);

        $oldStatus = $product->is_active;
        $product->update($validated);

        // Notify seller about product status change
        if ($oldStatus !== $product->is_active) {
            try {
                if ($product->is_active) {
                    $this->notificationService->notifyProductApproved($product);
                } else {
                    // Product deactivated - could be rejection
                    $this->notificationService->notifyProductRejected(
                        $product,
                        $request->input('reason', 'Product has been deactivated by admin')
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send product status notification', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
