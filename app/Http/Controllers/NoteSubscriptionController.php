<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteSubscription;
use App\Services\NoteSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoteSubscriptionController extends Controller
{
    public function __construct(
        private NoteSubscriptionService $subscriptionService
    ) {
        $this->middleware(['auth', 'verified', 'username.setup']);
    }

    /**
     * List user's subscriptions
     */
    public function index(): View
    {
        $subscriptions = NoteSubscription::where('user_id', auth()->id())
            ->with(['note', 'payments'])
            ->latest()
            ->paginate(15);

        return view('40-shared/subscriptions/index', compact('subscriptions'));
    }

    /**
     * Show subscription details
     */
    public function show(NoteSubscription $subscription): View
    {
        if ($subscription->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $subscription->load(['note', 'payments.transaction']);

        return view('40-shared/subscriptions/show', compact('subscription'));
    }

    /**
     * Subscribe to a note
     */
    public function subscribe(Request $request, Note $note): RedirectResponse
    {
        $validated = $request->validate([
            'tier' => 'required|in:basic,premium',
        ]);

        try {
            $subscription = $this->subscriptionService->subscribe(
                auth()->user(),
                $note,
                $validated['tier']
            );

            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Successfully subscribed! You now have access to note updates.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request, NoteSubscription $subscription): RedirectResponse
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->subscriptionService->cancel(
                $subscription,
                $validated['reason'] ?? null
            );

            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Subscription cancelled. You will retain access until ' . $subscription->current_period_end->format('M d, Y') . '.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reactivate cancelled subscription
     */
    public function reactivate(NoteSubscription $subscription): RedirectResponse
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$subscription->isCancelled()) {
            return back()->with('error', 'Subscription is not cancelled.');
        }

        if ($subscription->expires_at && $subscription->expires_at->isPast()) {
            return back()->with('error', 'Subscription has expired. Please create a new subscription.');
        }

        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'auto_renew' => true,
        ]);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription reactivated successfully.');
    }
}

