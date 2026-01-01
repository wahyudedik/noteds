<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\BalanceService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminWithdrawalController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private NotificationService $notificationService
    ) {
    }

    public function index(Request $request)
    {
        $query = Withdrawal::with(['user', 'admin']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        $withdrawals = $query->latest()->paginate(20);

        return Inertia::render('Admin/Withdrawals/Index', [
            'withdrawals' => $withdrawals,
            'filters' => $request->only(['status', 'user_type']),
        ]);
    }

    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load(['user', 'admin']);

        return Inertia::render('Admin/Withdrawals/Show', [
            'withdrawal' => $withdrawal,
        ]);
    }

    public function approve(Request $request, Withdrawal $withdrawal)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $withdrawal->approve(auth()->id(), $validated['admin_notes'] ?? null);
        $this->notificationService->notifyWithdrawalStatus($withdrawal);

        return redirect()->route('admin.withdrawals.index')
            ->with('success', 'Withdrawal approved successfully.');
    }

    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $withdrawal->reject(auth()->id(), $validated['admin_notes']);
        $this->notificationService->notifyWithdrawalStatus($withdrawal);

        return redirect()->route('admin.withdrawals.index')
            ->with('success', 'Withdrawal rejected.');
    }

    public function complete(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'approved') {
            return back()->withErrors(['error' => 'Withdrawal must be approved first']);
        }

        // Handle different withdrawal types
        $walletService = app(\App\Services\WalletService::class);
        
        if ($withdrawal->user_type === 'clipper') {
            // Use clipper wallet service
            $clipperWallet = $walletService->getClipperWallet($withdrawal->user);
            $clipperWallet->lockForWithdrawal($withdrawal->amount);
            $clipperWallet->markAsWithdrawn($withdrawal->amount);
        } elseif ($withdrawal->user_type === 'creator') {
            // Use creator wallet service - deduct from available balance
            $creatorWallet = $walletService->getCreatorWallet($withdrawal->user);
            if ($creatorWallet->balance_available < $withdrawal->amount) {
                return back()->withErrors(['error' => 'Insufficient available balance in creator wallet']);
            }
            // Deduct from available balance
            $creatorWallet->balance_available -= $withdrawal->amount;
            $creatorWallet->save();
        } else {
            // Use balance service for seller withdrawals
            $this->balanceService->deductBalance(
                $withdrawal->user,
                $withdrawal->amount,
                "Withdrawal #{$withdrawal->id}",
                $withdrawal->id
            );
        }

        $withdrawal->complete();
        $this->notificationService->notifyWithdrawalStatus($withdrawal);

        return redirect()->route('admin.withdrawals.index')
            ->with('success', 'Withdrawal completed successfully.');
    }
}
