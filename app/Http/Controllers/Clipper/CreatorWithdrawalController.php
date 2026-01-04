<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WalletService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreatorWithdrawalController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private NotificationService $notificationService
    ) {}

    public function index()
    {
        $withdrawals = Withdrawal::where('user_id', auth()->id())
            ->where('user_type', 'creator')
            ->with('admin')
            ->latest()
            ->paginate(15);

        return Inertia::render('Clipper/Withdrawals/CreatorIndex', [
            'withdrawals' => $withdrawals,
        ]);
    }

    public function create()
    {
        $wallet = $this->walletService->getCreatorWallet(auth()->user());
        $minWithdrawal = config('clipper.min_withdrawal', 50000);

        // Only allow withdrawal from available balance (not locked)
        if ($wallet->balance_available < $minWithdrawal) {
            return redirect()->route('clipper.wallet.creator')
                ->withErrors(['error' => 'Insufficient available balance for withdrawal. Minimum: Rp ' . number_format($minWithdrawal, 0, ',', '.')]);
        }

        return Inertia::render('Clipper/Withdrawals/CreatorCreate', [
            'wallet' => $wallet,
            'minWithdrawal' => $minWithdrawal,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:' . config('clipper.min_withdrawal', 50000),
            'method' => 'required|in:bank_transfer,ewallet',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
            'bank_name' => 'required_if:method,bank_transfer|nullable|string',
            'ewallet_type' => 'required_if:method,ewallet|nullable|in:OVO,GoPay,DANA,LinkAja',
        ]);

        $user = auth()->user();
        $wallet = $this->walletService->getCreatorWallet($user);

        // Only allow withdrawal from available balance (not locked in campaigns)
        if ($wallet->balance_available < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient available balance. You can only withdraw from available balance (not locked in campaigns).']);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            ...$validated,
            'status' => 'pending',
            'user_type' => 'creator',
        ]);

        $this->notificationService->notifyWithdrawalRequest($withdrawal);

        return redirect()->route('clipper.withdrawals.creator.index')
            ->with('success', 'Withdrawal request submitted successfully. Admin will process your request.');
    }

    public function show(Withdrawal $withdrawal)
    {
        if ($withdrawal->user_id !== auth()->id() || $withdrawal->user_type !== 'creator') {
            abort(403);
        }

        $withdrawal->load('admin');

        // Add proof URLs to withdrawal data
        $withdrawalData = $withdrawal->toArray();
        $withdrawalData['transfer_proof_approve_urls'] = $withdrawal->getTransferProofApproveUrls();
        $withdrawalData['transfer_proof_complete_urls'] = $withdrawal->getTransferProofCompleteUrls();

        return Inertia::render('Clipper/Withdrawals/CreatorShow', [
            'withdrawal' => $withdrawalData,
        ]);
    }
}

