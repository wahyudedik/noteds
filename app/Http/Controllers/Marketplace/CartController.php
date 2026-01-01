<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::where('user_id', auth()->id())
            ->with('product')
            ->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->totalPrice();
        });

        return Inertia::render('Marketplace/Cart', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Check if product is active
        if (!$product->is_active) {
            return back()->withErrors(['error' => 'Product is not available']);
        }

        // Check stock if applicable
        if ($product->stock !== null && $product->stock < $validated['quantity']) {
            return back()->withErrors(['error' => 'Insufficient stock']);
        }

        // Check if item already exists in cart
        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cartItem) {
            // Update quantity if item exists
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            
            // Check stock again with new quantity
            if ($product->stock !== null && $product->stock < $newQuantity) {
                return back()->withErrors(['error' => 'Insufficient stock']);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        return back()->with('success', 'Product added to cart');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        // Validate ownership
        if ($cartItem->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = $cartItem->product;

        // Check stock if applicable
        if ($product->stock !== null && $product->stock < $validated['quantity']) {
            return back()->withErrors(['error' => 'Insufficient stock']);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Cart updated');
    }

    public function destroy(CartItem $cartItem)
    {
        // Validate ownership
        if ($cartItem->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart');
    }
}
