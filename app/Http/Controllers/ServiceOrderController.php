<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceOrderController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $orders = ServiceOrder::latest()->paginate(12);
        } elseif ($user->hasRole('vendor')) {
            $orders = ServiceOrder::where('assigned_user_id', $user->id)->latest()->paginate(12);
        } else {
            $orders = ServiceOrder::where('user_id', $user->id)->latest()->paginate(12);
        }
        return view('studio.orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('studio.orders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = ServiceOrder::create([
            'user_id' => auth()->id(),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'budget' => (float) $request->input('budget', 0),
            'status' => 'submitted',
            'escrow_amount' => 0,
            'milestones' => [],
        ]);

        return redirect()->route('studio.orders.show', $order)->with('success', 'Brief terkirim. Tim kami akan memberikan quote.');
    }

    public function show(ServiceOrder $order): View
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->hasRole('admin') || auth()->user()->id === $order->assigned_user_id, 403);

        $vendors = [];
        if (auth()->user()->hasRole('admin')) {
            // Get vendors list for manual assignment
            $vendors = \App\Models\User::role('vendor')->orderBy('name')->get(['id', 'name']);
        }

        // Preload related data to avoid N+1 queries in view
        $ledger = \App\Models\EscrowLedger::where('service_order_id', $order->id)
            ->latest()
            ->get();

        $activities = \App\Models\OrderActivity::where('service_order_id', $order->id)
            ->latest()
            ->get();

        $quotes = \App\Models\ServiceQuote::where('service_order_id', $order->id)
            ->latest()
            ->get();

        return view('studio.orders.show', compact('order', 'vendors', 'ledger', 'activities', 'quotes'));
    }

    public function assignVendor(Request $request, ServiceOrder $order): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $request->validate([
            'vendor_id' => ['required', 'uuid', 'exists:users,id'],
        ]);
        $vendor = \App\Models\User::find($request->input('vendor_id'));
        if (!$vendor || !$vendor->hasRole('vendor')) {
            return back()->with('error', 'User terpilih bukan vendor.');
        }
        $order->assigned_user_id = $vendor->id;
        if ($order->status === 'submitted') {
            $order->status = 'quoted'; // assigned, waiting funding/quote confirmation
        }
        $order->save();

        \App\Models\AppNotification::create([
            'user_id' => $vendor->id,
            'type' => 'studio_order',
            'title' => 'Order ditugaskan ke Anda',
            'message' => 'Anda ditugaskan sebagai vendor untuk order "' . $order->title . '".',
            'link' => route('studio.orders.show', $order),
            'is_read' => false,
            'data' => ['order_id' => $order->id],
        ]);
        if ((bool) \App\Models\Setting::getSetting('studio_email_vendor_assigned', 'studio', true)) {
            try {
                \Illuminate\Support\Facades\Mail::to($vendor)->queue(
                    new \App\Mail\StudioNotification(
                        'Order ditugaskan ke Anda',
                        'Anda ditugaskan sebagai vendor untuk order "' . $order->title . '".',
                        route('studio.orders.show', $order)
                    )
                );
            } catch (\Throwable $e) {
            }
        }

        \App\Models\OrderActivity::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => 'vendor_assigned',
            'description' => 'Admin menetapkan vendor ke order',
            'meta' => ['vendor_id' => $vendor->id],
        ]);

        return back()->with('success', 'Vendor berhasil ditetapkan.');
    }

    public function fundEscrow(Request $request, ServiceOrder $order): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = (float) $request->input('amount');
        $buyer = auth()->user();
        $buyerWallet = \App\Models\Wallet::firstOrCreate(['user_id' => $buyer->id], ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]);
        if ($buyerWallet->balance < $amount) {
            return back()->with('error', 'Saldo wallet tidak cukup untuk funding escrow.');
        }

        // Deduct buyer wallet
        $buyerWallet->balance -= $amount;
        $buyerWallet->save();
        $buyer->wallet_balance = $buyerWallet->balance;
        $buyer->save();

        // Increase escrow
        $order->escrow_amount += $amount;
        if ($order->status === 'quoted') {
            $order->status = 'in_progress';
        }
        // Auto-assign vendor to first admin if not set (placeholder)
        if (!$order->assigned_user_id) {
            $admin = \App\Models\User::where('role', 'admin')->first();
            if ($admin) {
                $order->assigned_user_id = $admin->id;
            }
        }
        $order->save();

        // Ledger
        \App\Models\EscrowLedger::create([
            'service_order_id' => $order->id,
            'user_id' => $buyer->id,
            'type' => 'fund',
            'amount' => $amount,
            'milestone_index' => null,
            'meta' => [],
        ]);
        \App\Models\OrderActivity::create([
            'service_order_id' => $order->id,
            'user_id' => $buyer->id,
            'action' => 'escrow_funded',
            'description' => 'Buyer mendanai escrow',
            'meta' => ['amount' => $amount],
        ]);

        // Notify vendor if assigned, otherwise notify buyer
        if ($order->assigned_user_id) {
            \App\Models\AppNotification::create([
                'user_id' => $order->assigned_user_id,
                'type' => 'studio_escrow',
                'title' => 'Escrow didanai',
                'message' => 'Buyer mendanai escrow untuk order "' . $order->title . '".',
                'link' => route('studio.orders.show', $order),
                'is_read' => false,
                'data' => ['amount' => $amount],
            ]);
            if ((bool) \App\Models\Setting::getSetting('studio_email_escrow_funded', 'studio', true)) {
                try {
                    $vendor = \App\Models\User::find($order->assigned_user_id);
                    if ($vendor) {
                        \Illuminate\Support\Facades\Mail::to($vendor)->queue(
                            new \App\Mail\StudioNotification(
                                'Escrow didanai',
                                'Buyer mendanai escrow untuk order "' . $order->title . '".',
                                route('studio.orders.show', $order)
                            )
                        );
                    }
                } catch (\Throwable $e) {
                }
            }
        } else {
            \App\Models\AppNotification::create([
                'user_id' => $order->user_id,
                'type' => 'studio_escrow',
                'title' => 'Escrow didanai',
                'message' => 'Escrow berhasil didanai untuk order "' . $order->title . '".',
                'link' => route('studio.orders.show', $order),
                'is_read' => false,
                'data' => ['amount' => $amount],
            ]);
            if ((bool) \App\Models\Setting::getSetting('studio_email_escrow_funded', 'studio', true)) {
                try {
                    \Illuminate\Support\Facades\Mail::to($order->user)->queue(
                        new \App\Mail\StudioNotification(
                            'Escrow didanai',
                            'Escrow berhasil didanai untuk order "' . $order->title . '".',
                            route('studio.orders.show', $order)
                        )
                    );
                } catch (\Throwable $e) {
                }
            }
        }

        return back()->with('success', 'Escrow berhasil di-fund.');
    }

    public function releaseEscrow(Request $request, ServiceOrder $order): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->hasRole('admin'), 403);
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'milestone_index' => ['nullable', 'integer', 'min:0'],
        ]);
        $amount = (float) $request->input('amount');
        $milestoneIndex = $request->input('milestone_index');
        if ($amount > $order->escrow_amount) {
            return back()->with('error', 'Jumlah release melebihi escrow.');
        }
        // Per-milestone validation
        if ($milestoneIndex !== null && !empty($order->milestones)) {
            $milestones = $order->milestones ?? [];
            if (!isset($milestones[$milestoneIndex])) {
                return back()->with('error', 'Milestone tidak valid.');
            }
            $cap = (float) ($milestones[$milestoneIndex]['amount'] ?? 0);
            $releasedSoFar = \App\Models\EscrowLedger::where('service_order_id', $order->id)
                ->where('type', 'release')
                ->where('milestone_index', $milestoneIndex)
                ->sum('amount');
            if (($releasedSoFar + $amount) > $cap) {
                return back()->with('error', 'Release melebihi batas milestone.');
            }
        }
        $vendorId = $order->assigned_user_id;
        if (!$vendorId) {
            return back()->with('error', 'Vendor belum ditetapkan.');
        }
        $vendor = \App\Models\User::find($vendorId);
        if (!$vendor) {
            return back()->with('error', 'Vendor tidak ditemukan.');
        }

        // Platform fee
        $platformPercent = (float) (\App\Models\Setting::getSetting('studio_platform_fee_percent', 'studio', 10) ?? 10);
        $platformFee = $amount * ($platformPercent / 100);
        $vendorNet = $amount - $platformFee;
        if ($vendorNet < 0) {
            $vendorNet = 0;
        }

        // Decrease escrow
        $order->escrow_amount -= $amount;
        if ($order->escrow_amount <= 0 && $order->status === 'in_progress') {
            $order->status = 'completed';
        }
        $order->save();

        // Credit vendor wallet (net)
        $vendorWallet = \App\Models\Wallet::firstOrCreate(['user_id' => $vendor->id], ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]);
        $vendorWallet->balance += $vendorNet;
        $vendorWallet->save();
        $vendor->wallet_balance = $vendorWallet->balance;
        $vendor->save();

        // Credit platform fee to admin wallet
        if ($platformFee > 0) {
            $admin = \App\Models\User::where('role', 'admin')->first();
            if ($admin) {
                $adminWallet = \App\Models\Wallet::firstOrCreate(['user_id' => $admin->id], ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]);
                $adminWallet->balance += $platformFee;
                $adminWallet->save();
                $admin->wallet_balance = $adminWallet->balance;
                $admin->save();
                // Fee ledger
                \App\Models\EscrowLedger::create([
                    'service_order_id' => $order->id,
                    'user_id' => $admin->id,
                    'type' => 'fee',
                    'amount' => $platformFee,
                    'milestone_index' => $milestoneIndex,
                    'meta' => ['percent' => $platformPercent],
                ]);
            }
        }

        // Release ledger
        \App\Models\EscrowLedger::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'type' => 'release',
            'amount' => $amount,
            'milestone_index' => $milestoneIndex,
            'meta' => ['vendor_net' => $vendorNet, 'platform_fee' => $platformFee],
        ]);
        \App\Models\OrderActivity::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => 'escrow_released',
            'description' => 'Buyer merilis escrow',
            'meta' => ['amount' => $amount, 'milestone_index' => $milestoneIndex, 'vendor_net' => $vendorNet, 'platform_fee' => $platformFee],
        ]);

        // Notify vendor & buyer
        \App\Models\AppNotification::create([
            'user_id' => $vendor->id,
            'type' => 'studio_escrow',
            'title' => 'Escrow dirilis',
            'message' => 'Dana escrow dirilis untuk order "' . $order->title . '".',
            'link' => route('studio.orders.show', $order),
            'is_read' => false,
            'data' => ['amount' => $amount, 'net' => $vendorNet],
        ]);
        if ((bool) \App\Models\Setting::getSetting('studio_email_escrow_released', 'studio', true)) {
            try {
                \Illuminate\Support\Facades\Mail::to($vendor)->queue(
                    new \App\Mail\StudioNotification(
                        'Escrow dirilis',
                        'Dana escrow dirilis untuk order "' . $order->title . '".',
                        route('studio.orders.show', $order)
                    )
                );
            } catch (\Throwable $e) {
            }
        }
        \App\Models\AppNotification::create([
            'user_id' => $order->user_id,
            'type' => 'studio_escrow',
            'title' => 'Escrow dirilis',
            'message' => 'Anda merilis dana escrow untuk order "' . $order->title . '".',
            'link' => route('studio.orders.show', $order),
            'is_read' => false,
            'data' => ['amount' => $amount],
        ]);
        if ((bool) \App\Models\Setting::getSetting('studio_email_escrow_released', 'studio', true)) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user)->queue(
                    new \App\Mail\StudioNotification(
                        'Escrow dirilis',
                        'Anda merilis dana escrow untuk order "' . $order->title . '".',
                        route('studio.orders.show', $order)
                    )
                );
            } catch (\Throwable $e) {
            }
        }

        return back()->with('success', 'Escrow dilepas ke vendor.');
    }

    public function refundEscrow(Request $request, ServiceOrder $order): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->hasRole('admin'), 403);
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);
        $amount = (float) $request->input('amount');
        if ($amount > $order->escrow_amount) {
            return back()->with('error', 'Jumlah refund melebihi escrow.');
        }

        // Decrease escrow
        $order->escrow_amount -= $amount;
        if ($order->escrow_amount <= 0 && in_array($order->status, ['submitted', 'quoted', 'in_progress'])) {
            $order->status = 'cancelled';
        }
        $order->save();

        // Credit back to buyer wallet
        $buyer = $order->user;
        $buyerWallet = \App\Models\Wallet::firstOrCreate(['user_id' => $buyer->id], ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]);
        $buyerWallet->balance += $amount;
        $buyerWallet->save();
        $buyer->wallet_balance = $buyerWallet->balance;
        $buyer->save();

        // Ledger
        \App\Models\EscrowLedger::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'type' => 'refund',
            'amount' => $amount,
            'milestone_index' => null,
            'meta' => [],
        ]);
        \App\Models\OrderActivity::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => 'escrow_refunded',
            'description' => 'Buyer menerima refund escrow',
            'meta' => ['amount' => $amount],
        ]);

        \App\Models\AppNotification::create([
            'user_id' => $order->user_id,
            'type' => 'studio_escrow',
            'title' => 'Escrow direfund',
            'message' => 'Dana escrow dikembalikan ke wallet Anda untuk order "' . $order->title . '".',
            'link' => route('studio.orders.show', $order),
            'is_read' => false,
            'data' => ['amount' => $amount],
        ]);
        if ((bool) \App\Models\Setting::getSetting('studio_email_escrow_refunded', 'studio', true)) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user)->queue(
                    new \App\Mail\StudioNotification(
                        'Escrow direfund',
                        'Dana escrow dikembalikan ke wallet Anda untuk order "' . $order->title . '".',
                        route('studio.orders.show', $order)
                    )
                );
            } catch (\Throwable $e) {
            }
        }

        return back()->with('success', 'Escrow dikembalikan ke wallet Anda.');
    }
}
