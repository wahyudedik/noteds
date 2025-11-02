<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $subscription = auth()->user()->subscription;
        
        return view('subscription.index', compact('subscription'));
    }

    public function create(): View|RedirectResponse
    {
        // Check if user already has an active or pending subscription
        $existingSubscription = auth()->user()->subscription;
        
        if ($existingSubscription && in_array($existingSubscription->status, ['active', 'pending'])) {
            return redirect()->route('subscription.index')
                ->with('error', 'You already have a subscription request or active subscription.');
        }

        return view('subscription.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_proof' => 'required|url|max:500',
        ], [
            'payment_proof.required' => 'Payment proof URL is required.',
            'payment_proof.url' => 'Please provide a valid URL for payment proof.',
        ]);

        Subscription::create([
            'user_id' => auth()->id(),
            'plan' => 'premium',
            'status' => 'pending',
            'payment_proof' => $request->payment_proof,
        ]);

        return redirect()->route('subscription.index')
            ->with('success', 'Subscription request submitted! Please wait for admin approval.');
    }

    public function show(Subscription $subscription): View
    {
        // Ensure user can only view their own subscription
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        return view('subscription.show', compact('subscription'));
    }
}
