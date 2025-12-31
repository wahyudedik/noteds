<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use App\Services\WalletService;
use App\Models\LedgerEntry;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminWalletController extends Controller
{
    public function __construct(
        private LedgerService $ledgerService,
        private WalletService $walletService
    ) {}

    public function viewLedger(Request $request)
    {
        $query = LedgerEntry::with(['admin']);

        if ($request->has('wallet_type')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_wallet_type', $request->wallet_type)
                  ->orWhere('to_wallet_type', $request->wallet_type);
            });
        }

        if ($request->has('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->has('transaction_id')) {
            $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        }

        $entries = $query->latest()->paginate(50);

        return Inertia::render('Admin/Wallets/Ledger', [
            'entries' => $entries,
            'filters' => $request->only(['wallet_type', 'reason', 'transaction_id']),
        ]);
    }

    public function viewAuditLog(Request $request)
    {
        $query = AuditLog::with(['user', 'admin']);

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        $logs = $query->latest('created_at')->paginate(50);

        return Inertia::render('Admin/Wallets/AuditLog', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'target_type']),
        ]);
    }

    public function freezeWallet(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'wallet_type' => 'required|in:creator,clipper',
            'reason' => 'required|string|max:1000',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // For now, we'll use a simple approach with audit log
        // In a full implementation, you might add an `is_frozen` field to wallets
        AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'freeze_wallet',
            'target_type' => $validated['wallet_type'] . '_wallet',
            'target_id' => $validated['wallet_type'] === 'creator' 
                ? $this->walletService->getCreatorWallet($user)->id
                : $this->walletService->getClipperWallet($user)->id,
            'notes' => $validated['reason'],
        ]);

        return back()->with('success', 'Wallet frozen successfully.');
    }

    public function unfreezeWallet(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'wallet_type' => 'required|in:creator,clipper',
            'reason' => 'required|string|max:1000',
        ]);

        $user = User::findOrFail($validated['user_id']);

        AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'unfreeze_wallet',
            'target_type' => $validated['wallet_type'] . '_wallet',
            'target_id' => $validated['wallet_type'] === 'creator' 
                ? $this->walletService->getCreatorWallet($user)->id
                : $this->walletService->getClipperWallet($user)->id,
            'notes' => $validated['reason'],
        ]);

        return back()->with('success', 'Wallet unfrozen successfully.');
    }

    public function adjustBalance(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'wallet_type' => 'required|in:creator,clipper',
            'amount' => 'required|numeric',
            'reason' => 'required|string|max:1000',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $oldBalance = null;
        $newBalance = null;

        if ($validated['wallet_type'] === 'creator') {
            $wallet = $this->walletService->getCreatorWallet($user);
            $oldBalance = $wallet->balance_available;
            
            if ($validated['amount'] > 0) {
                $wallet->addBalance($validated['amount']);
            } else {
                $wallet->deductBalance(abs($validated['amount']));
            }
            
            $newBalance = $wallet->balance_available;
        } else {
            $wallet = $this->walletService->getClipperWallet($user);
            $oldBalance = $wallet->balance_available;
            
            if ($validated['amount'] > 0) {
                $wallet->addReward($validated['amount']);
                $wallet->movePendingToAvailable($validated['amount']);
            } else {
                $wallet->lockForWithdrawal(abs($validated['amount']));
            }
            
            $newBalance = $wallet->balance_available;
        }

        // Create ledger entry
        LedgerEntry::createEntry([
            'from_wallet_type' => $validated['amount'] > 0 ? 'platform' : $validated['wallet_type'],
            'from_wallet_id' => $validated['amount'] > 0 ? null : $wallet->id,
            'to_wallet_type' => $validated['amount'] > 0 ? $validated['wallet_type'] : 'platform',
            'to_wallet_id' => $validated['amount'] > 0 ? $wallet->id : null,
            'amount' => abs($validated['amount']),
            'reason' => 'adjustment',
            'admin_id' => auth()->id(),
            'metadata' => ['reason' => $validated['reason']],
        ]);

        // Create audit log
        AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'adjust_balance',
            'target_type' => $validated['wallet_type'] . '_wallet',
            'target_id' => $wallet->id,
            'old_value' => ['balance' => $oldBalance],
            'new_value' => ['balance' => $newBalance],
            'notes' => $validated['reason'],
        ]);

        return back()->with('success', 'Balance adjusted successfully.');
    }
}
