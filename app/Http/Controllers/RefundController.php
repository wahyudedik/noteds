<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Transaction;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of user's refunds.
     */
    public function index(): View
    {
        $refunds = Refund::where('buyer_id', auth()->id())
            ->with(['note', 'transaction', 'seller'])
            ->latest()
            ->paginate(15);

        return view('refunds.index', compact('refunds'));
    }

    /**
     * Show the form for creating a new refund.
     */
    public function create(Transaction $transaction): View
    {
        // Ensure user owns this transaction
        if ($transaction->buyer_id !== auth()->id()) {
            abort(403);
        }

        // Check if refund already exists
        $existingRefund = Refund::where('transaction_id', $transaction->id)
            ->where('buyer_id', auth()->id())
            ->first();

        if ($existingRefund) {
            return redirect()->route('refunds.show', $existingRefund)
                ->with('info', 'Refund request already exists for this transaction.');
        }

        // Check if transaction is eligible for refund (within 7 days)
        $daysSincePurchase = $transaction->created_at->diffInDays(now());
        if ($daysSincePurchase > 7) {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'Refund can only be requested within 7 days of purchase.');
        }

        return view('refunds.create', compact('transaction'));
    }

    /**
     * Store a newly created refund request.
     */
    public function store(Request $request, Transaction $transaction): RedirectResponse
    {
        // Ensure user owns this transaction
        if ($transaction->buyer_id !== auth()->id()) {
            abort(403);
        }

        // Check if refund already exists
        $existingRefund = Refund::where('transaction_id', $transaction->id)
            ->where('buyer_id', auth()->id())
            ->exists();

        if ($existingRefund) {
            return redirect()->route('refunds.index')
                ->with('error', 'Refund request already exists for this transaction.');
        }

        // Check if transaction is eligible for refund
        $daysSincePurchase = $transaction->created_at->diffInDays(now());
        if ($daysSincePurchase > 7) {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'Refund can only be requested within 7 days of purchase.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'in:not_as_described,duplicate_purchase,technical_issue,changed_mind,other'],
            'reason_description' => ['required', 'string', 'min:20', 'max:1000'],
        ]);

        $refund = Refund::create([
            'transaction_id' => $transaction->id,
            'buyer_id' => auth()->id(),
            'seller_id' => $transaction->seller_id,
            'note_id' => $transaction->note_id,
            'amount' => $transaction->amount,
            'status' => 'pending',
            'reason' => $validated['reason'],
            'reason_description' => $validated['reason_description'],
        ]);

        // Notify seller
        $this->notificationService->create(
            $transaction->seller,
            'refund_requested',
            '💰 Refund Request Received',
            auth()->user()->name . ' has requested a refund for: ' . $transaction->note->title,
            route('refunds.show', $refund),
            ['refund_id' => $refund->id, 'transaction_id' => $transaction->id]
        );

        // Notify admins
        $admins = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            $this->notificationService->create(
                $admin,
                'refund_requested_admin',
                '🔔 New Refund Request',
                'A new refund request has been submitted. Amount: Rp ' . number_format($refund->amount, 0, ',', '.'),
                route('admin.refunds.show', $refund),
                ['refund_id' => $refund->id]
            );
        }

        return redirect()->route('refunds.show', $refund)
            ->with('success', 'Refund request submitted successfully. We will review it within 24-48 hours.');
    }

    /**
     * Display the specified refund.
     */
    public function show(Refund $refund): View
    {
        // Ensure user owns this refund or is admin
        if ($refund->buyer_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $refund->load(['note', 'transaction', 'seller', 'buyer', 'processedBy']);

        return view('refunds.show', compact('refund'));
    }
}
