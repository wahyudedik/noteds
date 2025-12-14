<?php

namespace App\Http\Controllers;

use App\Models\Escrow;
use App\Models\Transaction;
use App\Services\EscrowService;
use App\Services\BuyerProtectionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EscrowController extends Controller
{
    public function __construct(
        private EscrowService $escrowService,
        private BuyerProtectionService $buyerProtectionService
    ) {
        $this->middleware('auth');
    }

    /**
     * List user's escrows
     */
    public function index(): View
    {
        $escrows = Escrow::where('buyer_id', auth()->id())
            ->orWhere('seller_id', auth()->id())
            ->with(['note', 'transaction', 'buyer', 'seller'])
            ->latest()
            ->paginate(15);

        return view('40-shared/escrows/index', compact('escrows'));
    }

    /**
     * Show escrow details
     */
    public function show(Escrow $escrow): View
    {
        // Ensure user is buyer or seller
        if ($escrow->buyer_id !== auth()->id() && $escrow->seller_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $escrow->load(['note', 'transaction', 'buyer', 'seller', 'dispute', 'releaser', 'refunder']);

        return view('40-shared/escrows/show', compact('escrow'));
    }

    /**
     * Confirm receipt and release escrow (buyer)
     */
    public function confirmReceipt(Request $request, Escrow $escrow): RedirectResponse
    {
        if ($escrow->buyer_id !== auth()->id()) {
            abort(403);
        }

        if (!$escrow->isFunded()) {
            return back()->with('error', 'Escrow is not funded.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->escrowService->releaseEscrow(
                $escrow,
                auth()->user(),
                $validated['notes'] ?? 'Buyer confirmed receipt'
            );

            return redirect()->route('escrows.show', $escrow)
                ->with('success', 'Payment has been released to the seller.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create dispute for escrow
     */
    public function createDispute(Request $request, Escrow $escrow): RedirectResponse
    {
        if ($escrow->buyer_id !== auth()->id()) {
            abort(403);
        }

        if (!$escrow->isFunded()) {
            return back()->with('error', 'Escrow is not funded.');
        }

        $validated = $request->validate([
            'type' => 'required|in:refund,quality,delivery,other',
            'complaint' => 'required|string|min:50|max:2000',
            'evidence' => 'nullable|array',
            'evidence.*' => 'string|url',
        ]);

        try {
            $dispute = $this->buyerProtectionService->createDispute(
                $escrow->transaction,
                auth()->user(),
                $validated['type'],
                $validated['complaint'],
                $validated['evidence'] ?? null,
                null // No refund linked yet
            );

            // Mark escrow as disputed
            $this->escrowService->markAsDisputed($escrow, $dispute);

            return redirect()->route('disputes.show', $dispute)
                ->with('success', 'Dispute created and escrow has been put on hold.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

