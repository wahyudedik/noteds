<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Transaction;
use App\Services\BuyerProtectionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function __construct(
        private BuyerProtectionService $buyerProtectionService
    ) {
        $this->middleware('auth');
    }

    /**
     * List user's disputes
     */
    public function index(): View
    {
        $disputes = Dispute::where('buyer_id', auth()->id())
            ->orWhere('seller_id', auth()->id())
            ->with(['note', 'transaction', 'buyer', 'seller'])
            ->latest()
            ->paginate(15);

        return view('disputes.index', compact('disputes'));
    }

    /**
     * Show dispute form
     */
    public function create(Transaction $transaction): View
    {
        if ($transaction->buyer_id !== auth()->id()) {
            abort(403);
        }

        // Check if dispute already exists
        $existingDispute = Dispute::where('transaction_id', $transaction->id)
            ->where('buyer_id', auth()->id())
            ->whereIn('status', ['open', 'in_review'])
            ->first();

        if ($existingDispute) {
            return redirect()->route('disputes.show', $existingDispute)
                ->with('info', 'A dispute already exists for this transaction.');
        }

        return view('disputes.create', compact('transaction'));
    }

    /**
     * Store dispute
     */
    public function store(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->buyer_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:refund,quality,delivery,other',
            'complaint' => 'required|string|min:50|max:2000',
            'evidence' => 'nullable|array',
            'evidence.*' => 'string|url',
        ]);

        try {
            $refund = null;
            if ($request->has('refund_id')) {
                $refund = \App\Models\Refund::findOrFail($request->refund_id);
            }

            $dispute = $this->buyerProtectionService->createDispute(
                $transaction,
                auth()->user(),
                $validated['type'],
                $validated['complaint'],
                $validated['evidence'] ?? null,
                $refund
            );

            return redirect()->route('disputes.show', $dispute)
                ->with('success', 'Dispute created successfully. We will review it within 24-48 hours.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show dispute
     */
    public function show(Dispute $dispute): View
    {
        if ($dispute->buyer_id !== auth()->id() 
            && $dispute->seller_id !== auth()->id() 
            && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $dispute->load(['note', 'transaction', 'buyer', 'seller', 'refund', 'resolver']);

        return view('disputes.show', compact('dispute'));
    }

    /**
     * Seller response to dispute
     */
    public function respond(Request $request, Dispute $dispute): RedirectResponse
    {
        if ($dispute->seller_id !== auth()->id()) {
            abort(403);
        }

        if (!$dispute->isOpen()) {
            return back()->with('error', 'Dispute is not open for response.');
        }

        $validated = $request->validate([
            'response' => 'required|string|min:20|max:2000',
        ]);

        $dispute->update([
            'seller_response' => $validated['response'],
            'status' => 'in_review',
        ]);

        // Notify buyer
        app(\App\Services\NotificationService::class)->create(
            $dispute->buyer,
            'dispute_response',
            '💬 Seller Responded',
            "The seller has responded to your dispute for '{$dispute->note->title}'.",
            route('disputes.show', $dispute),
            ['dispute_id' => $dispute->id]
        );

        return redirect()->route('disputes.show', $dispute)
            ->with('success', 'Your response has been submitted.');
    }
}

