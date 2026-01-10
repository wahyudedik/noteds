<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\ProductSubscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Get user's subscriptions.
     */
    public function index(Request $request): Response
    {
        $subscriptions = ProductSubscription::where('user_id', auth()->id())
            ->with(['product.seller', 'renewals'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Marketplace/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Show subscription details.
     */
    public function show(ProductSubscription $subscription): Response
    {
        // Verify ownership
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $subscription->load(['product.seller', 'order', 'renewals.order']);

        return Inertia::render('Marketplace/Subscriptions/Show', [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(ProductSubscription $subscription): RedirectResponse
    {
        // Verify ownership
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $this->subscriptionService->cancelSubscription($subscription);
            return back()->with('success', 'Subscription cancelled successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Pause subscription.
     */
    public function pause(ProductSubscription $subscription): RedirectResponse
    {
        // Verify ownership
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $this->subscriptionService->pauseSubscription($subscription);
            return back()->with('success', 'Subscription paused successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Resume subscription.
     */
    public function resume(ProductSubscription $subscription): RedirectResponse
    {
        // Verify ownership
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $this->subscriptionService->resumeSubscription($subscription);
            return back()->with('success', 'Subscription resumed successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
