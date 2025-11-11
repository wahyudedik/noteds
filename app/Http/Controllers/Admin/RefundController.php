<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of refunds.
     */
    public function index(Request $request): View
    {
        $refunds = Refund::with(['buyer', 'seller', 'note', 'transaction'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->whereHas('buyer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending' => Refund::where('status', 'pending')->count(),
            'approved' => Refund::where('status', 'approved')->count(),
            'rejected' => Refund::where('status', 'rejected')->count(),
            'processed' => Refund::where('status', 'processed')->count(),
            'total_amount_pending' => Refund::where('status', 'pending')->sum('amount'),
        ];

        return view('admin.refunds.index', compact('refunds', 'stats'));
    }

    /**
     * Display the specified refund.
     */
    public function show(Refund $refund): View
    {
        $refund->load(['buyer', 'seller', 'note', 'transaction', 'processedBy']);

        return view('admin.refunds.show', compact('refund'));
    }

    /**
     * Approve a refund request.
     */
    public function approve(Request $request, Refund $refund): RedirectResponse
    {
        if ($refund->status !== 'pending') {
            return redirect()->route('admin.refunds.show', $refund)
                ->with('error', 'Refund request is not pending.');
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($refund, $validated) {
            // Update refund status
            $refund->update([
                'status' => 'approved',
                'admin_notes' => $validated['admin_notes'] ?? null,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Process refund: Add amount back to buyer's wallet
            $buyer = $refund->buyer;
            $buyer->increment('wallet_balance', $refund->amount);

            // Sync Wallet model
            $wallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $buyer->id],
                ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
            );
            $wallet->balance = $buyer->wallet_balance;
            $wallet->save();

            // Deduct from seller's wallet (if they still have balance)
            $seller = $refund->seller;
            if ($seller->wallet_balance >= $refund->amount) {
                $seller->decrement('wallet_balance', $refund->amount);
                
                $sellerWallet = \App\Models\Wallet::firstOrCreate(
                    ['user_id' => $seller->id],
                    ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
                );
                $sellerWallet->balance = $seller->wallet_balance;
                $sellerWallet->save();
            }

            // Update refund status to processed
            $refund->update(['status' => 'processed']);

            // Notify buyer
            $this->notificationService->create(
                $buyer,
                'refund_approved',
                '✅ Refund Approved',
                'Your refund request for ' . $refund->note->title . ' has been approved. Amount: Rp ' . number_format($refund->amount, 0, ',', '.') . ' has been added to your wallet.',
                route('refunds.show', $refund),
                ['refund_id' => $refund->id, 'amount' => $refund->amount]
            );

            // Notify seller
            $this->notificationService->create(
                $seller,
                'refund_approved_seller',
                '💰 Refund Processed',
                'A refund has been processed for: ' . $refund->note->title . '. Amount: Rp ' . number_format($refund->amount, 0, ',', '.') . ' has been deducted from your wallet.',
                route('refunds.show', $refund),
                ['refund_id' => $refund->id]
            );
        });

        return redirect()->route('admin.refunds.show', $refund)
            ->with('success', 'Refund approved and processed successfully.');
    }

    /**
     * Reject a refund request.
     */
    public function reject(Request $request, Refund $refund): RedirectResponse
    {
        if ($refund->status !== 'pending') {
            return redirect()->route('admin.refunds.show', $refund)
                ->with('error', 'Refund request is not pending.');
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $refund->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Notify buyer
        $this->notificationService->create(
            $refund->buyer,
            'refund_rejected',
            '❌ Refund Request Rejected',
            'Your refund request for ' . $refund->note->title . ' has been rejected. Reason: ' . $validated['admin_notes'],
            route('refunds.show', $refund),
            ['refund_id' => $refund->id]
        );

        return redirect()->route('admin.refunds.show', $refund)
            ->with('success', 'Refund request rejected.');
    }
}
