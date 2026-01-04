<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\BalanceService;
use App\Services\LedgerService;
use App\Services\NotificationService;
use App\Services\WithdrawalProofService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminWithdrawalController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private NotificationService $notificationService,
        private LedgerService $ledgerService,
        private WithdrawalProofService $proofService
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

        // Add proof URLs to withdrawal data
        $withdrawalData = $withdrawal->toArray();
        $withdrawalData['transfer_proof_approve_urls'] = $withdrawal->getTransferProofApproveUrls();
        $withdrawalData['transfer_proof_complete_urls'] = $withdrawal->getTransferProofCompleteUrls();

        return Inertia::render('Admin/Withdrawals/Show', [
            'withdrawal' => $withdrawalData,
        ]);
    }

    public function approve(Request $request, Withdrawal $withdrawal)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
            'transfer_proof' => 'nullable|array',
            'transfer_proof.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max per image
        ]);

        $proofPaths = [];
        if ($request->hasFile('transfer_proof')) {
            $validationErrors = $this->proofService->validateProofImages($request->file('transfer_proof'));
            if (!empty($validationErrors)) {
                return back()->withErrors(['transfer_proof' => $validationErrors[0]]);
            }

            $proofPaths = $this->proofService->storeProofImages(
                $request->file('transfer_proof'),
                $withdrawal->id,
                'approve'
            );
        }

        $withdrawal->approve(auth()->id(), $validated['admin_notes'] ?? null);
        
        if (!empty($proofPaths)) {
            $withdrawal->update([
                'transfer_proof_approve' => $proofPaths,
                'transfer_proof_approve_uploaded_at' => now(),
            ]);
        }

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

    public function complete(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'approved') {
            return back()->withErrors(['error' => 'Withdrawal must be approved first']);
        }

        $validated = $request->validate([
            'transfer_proof' => 'nullable|array',
            'transfer_proof.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max per image
        ]);

        $proofPaths = [];
        if ($request->hasFile('transfer_proof')) {
            $validationErrors = $this->proofService->validateProofImages($request->file('transfer_proof'));
            if (!empty($validationErrors)) {
                return back()->withErrors(['transfer_proof' => $validationErrors[0]]);
            }

            $proofPaths = $this->proofService->storeProofImages(
                $request->file('transfer_proof'),
                $withdrawal->id,
                'complete'
            );
        }

        // Handle different withdrawal types
        $walletService = app(\App\Services\WalletService::class);
        
        if ($withdrawal->user_type === 'clipper') {
            // Use clipper wallet service
            $clipperWallet = $walletService->getClipperWallet($withdrawal->user);
            $clipperWallet->lockForWithdrawal($withdrawal->amount);
            $clipperWallet->markAsWithdrawn($withdrawal->amount);
            
            // Create ledger entry for clipper wallet withdrawal
            $platformWallet = $walletService->getPlatformWallet();
            $this->ledgerService->createEntry([
                'from_wallet_type' => 'clipper',
                'from_wallet_id' => $clipperWallet->id,
                'to_wallet_type' => 'platform',
                'to_wallet_id' => $platformWallet->id,
                'amount' => $withdrawal->amount,
                'reason' => 'withdrawal',
                'reference_type' => 'withdrawal',
                'reference_id' => $withdrawal->id,
                'admin_id' => auth()->id(),
            ]);
        } elseif ($withdrawal->user_type === 'creator') {
            // Use creator wallet service - deduct from available balance
            $creatorWallet = $walletService->getCreatorWallet($withdrawal->user);
            if ($creatorWallet->balance_available < $withdrawal->amount) {
                return back()->withErrors(['error' => 'Insufficient available balance in creator wallet']);
            }
            // Deduct from available balance
            $creatorWallet->balance_available -= $withdrawal->amount;
            $creatorWallet->save();
            
            // Create ledger entry for creator wallet withdrawal
            $platformWallet = $walletService->getPlatformWallet();
            $this->ledgerService->createEntry([
                'from_wallet_type' => 'creator',
                'from_wallet_id' => $creatorWallet->id,
                'to_wallet_type' => 'platform',
                'to_wallet_id' => $platformWallet->id,
                'amount' => $withdrawal->amount,
                'reason' => 'withdrawal',
                'reference_type' => 'withdrawal',
                'reference_id' => $withdrawal->id,
                'admin_id' => auth()->id(),
            ]);
        } else {
            // Use balance service for seller withdrawals (marketplace wallet)
            // Marketplace wallet uses Transaction model, not LedgerEntry
            $this->balanceService->deductBalance(
                $withdrawal->user,
                $withdrawal->amount,
                "Withdrawal #{$withdrawal->id}",
                $withdrawal->id
            );
        }

        $withdrawal->complete();
        
        if (!empty($proofPaths)) {
            $withdrawal->update([
                'transfer_proof_complete' => $proofPaths,
                'transfer_proof_complete_uploaded_at' => now(),
            ]);
        }

        $this->notificationService->notifyWithdrawalStatus($withdrawal);

        return redirect()->route('admin.withdrawals.index')
            ->with('success', 'Withdrawal completed successfully.');
    }
}
