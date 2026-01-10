<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WaitlistService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductWaitlistController extends Controller
{
    protected WaitlistService $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }

    /**
     * Join waitlist.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        try {
            $this->waitlistService->addToWaitlist($product, $request->user());
            return back()->with('success', 'You have been added to the waitlist');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Leave waitlist.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->waitlistService->removeFromWaitlist($product, auth()->user());
            return back()->with('success', 'You have been removed from the waitlist');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get user's waitlist.
     */
    public function index(Request $request): Response
    {
        $waitlists = \App\Models\ProductWaitlist::where('user_id', auth()->id())
            ->with(['product.seller'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Marketplace/Waitlist', [
            'waitlists' => $waitlists,
        ]);
    }
}
