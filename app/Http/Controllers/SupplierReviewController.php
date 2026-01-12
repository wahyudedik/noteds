<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierReviewRequest;
use App\Models\Supplier;
use App\Models\SupplierReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierReviewController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierReviewRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('create', SupplierReview::class);

        SupplierReview::create([
            'supplier_id' => $supplier->id,
            'user_id' => $request->user()->id,
            'post_id' => $request->input('post_id'),
            'rating' => $request->input('rating'),
            'review' => $request->input('review'),
            'tags' => $request->input('tags'),
            'is_verified_purchase' => $request->boolean('is_verified_purchase', false),
        ]);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Review berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSupplierReviewRequest $request, Supplier $supplier, SupplierReview $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Review berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Supplier $supplier, SupplierReview $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Review berhasil dihapus.');
    }
}
