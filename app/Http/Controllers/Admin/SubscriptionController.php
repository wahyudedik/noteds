<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $subscriptions = Subscription::with(['user', 'approvedBy'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->plan, function ($query) use ($request) {
                return $query->where('plan', $request->plan);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create(): View
    {
        $users = User::whereNotIn('role', ['admin'])->orderBy('name')->get();

        return view('admin.subscriptions.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'plan' => ['required', 'in:basic,premium'],
            'expired_at' => ['required', 'date', 'after:now'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check if user already has an active subscription
        $existingSubscription = Subscription::where('user_id', $request->user_id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            return redirect()->route('admin.subscriptions.create')
                ->with('error', 'User already has an active subscription. Please cancel it first.');
        }

        Subscription::create([
            'user_id' => $request->user_id,
            'plan' => $request->plan,
            'status' => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'expired_at' => $request->expired_at,
            'admin_notes' => $request->admin_notes ?? 'Manually created by admin.',
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription created successfully!');
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['user', 'approvedBy']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function approve(Request $request, Subscription $subscription): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'expired_at' => ['required', 'date', 'after:now'],
        ]);

        $subscription->update([
            'status' => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'expired_at' => $request->expired_at,
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription approved successfully!');
    }

    public function reject(Request $request, Subscription $subscription): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $subscription->update([
            'status' => 'cancelled',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription request rejected.');
    }
}
