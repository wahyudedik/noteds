<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{
    public function index(Request $request)
    {
        // Get all paid orders grouped by product (latest order per product)
        $purchasedProducts = Order::where('user_id', auth()->id())
            ->where('payment_status', 'paid')
            ->with(['product', 'product.seller'])
            ->select('orders.*')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY product_id ORDER BY created_at DESC) as rn')
            ->get()
            ->where('rn', 1)
            ->values();

        // For simpler approach, get distinct products with their latest order
        $purchasedProducts = Order::where('user_id', auth()->id())
            ->where('payment_status', 'paid')
            ->with(['product', 'product.seller'])
            ->select(DB::raw('MAX(id) as id'), 'product_id')
            ->groupBy('product_id')
            ->get()
            ->map(function ($item) {
                return Order::with(['product', 'product.seller'])
                    ->find($item->id);
            })
            ->filter()
            ->sortByDesc('created_at')
            ->values();

        return Inertia::render('Marketplace/Purchases/Index', [
            'purchasedProducts' => $purchasedProducts,
        ]);
    }
}

