<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoteSubscription;
use App\Services\NoteSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteSubscriptionController extends Controller
{
    public function __construct(
        private NoteSubscriptionService $subscriptionService
    ) {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * List all subscriptions
     */
    public function index(Request $request): View
    {
        $subscriptions = NoteSubscription::with(['user', 'note', 'payments'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->tier, function ($query) use ($request) {
                return $query->where('tier', $request->tier);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'active' => NoteSubscription::where('status', 'active')->count(),
            'cancelled' => NoteSubscription::where('status', 'cancelled')->count(),
            'expired' => NoteSubscription::where('status', 'expired')->count(),
            'suspended' => NoteSubscription::where('status', 'suspended')->count(),
            'total_revenue' => NoteSubscriptionPayment::where('status', 'success')->sum('amount'),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'stats'));
    }

    /**
     * Show subscription details
     */
    public function show(NoteSubscription $subscription): View
    {
        $subscription->load(['user', 'note', 'payments.transaction']);

        return view('admin.subscriptions.show', compact('subscription'));
    }
}

