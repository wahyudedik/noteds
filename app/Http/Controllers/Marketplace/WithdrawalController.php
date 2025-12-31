<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\BalanceService;
use App\Services\WalletService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private WalletService $walletService,
        private NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $query = Withdrawal::where('user_id', auth()->id())
            ->with('admin');

        // Filter by user_type if provided (for clipper vs seller)
        if ($request->has('user_type')) {
            $query->where('user_type', $request->user_type);
        } else {
            // Default to seller for marketplace withdrawals
            $query->where('user_type', 'seller');
        }

        $withdrawals = $query->latest()->paginate(15);

        return Inertia::render('Marketplace/Withdrawals/Index', [
            'withdrawals' => $withdrawals,
            'filters' => $request->only('user_type'),
        ]);
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $userType = $request->get('user_type', 'seller');
        
        // Get balance based on user type
        if ($userType === 'clipper') {
            $wallet = $this->walletService->getClipperWallet($user);
            $balance = $wallet->balance_available;
            $minWithdrawal = config('clipper.min_withdrawal', 50000);
        } else {
            $balance = $this->balanceService->getBalance($user);
            $minWithdrawal = 50000; // Default for seller
        }

        return Inertia::render('Marketplace/Withdrawals/Create', [
            'balance' => $balance,
            'minWithdrawal' => $minWithdrawal,
            'userType' => $userType,
        ]);
    }

    public function store(Request $request)
    {
        $userType = $request->get('user_type', 'seller');
        $minAmount = $userType === 'clipper' 
            ? config('clipper.min_withdrawal', 50000) 
            : 50000;

        $validated = $request->validate([
            'amount' => "required|numeric|min:{$minAmount}",
            'method' => 'required|in:bank_transfer,ewallet',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
            'bank_name' => 'required_if:method,bank_transfer|nullable|string',
            'ewallet_type' => 'required_if:method,ewallet|nullable|in:OVO,GoPay,DANA,LinkAja',
            'user_type' => 'nullable|in:seller,clipper',
        ]);

        $user = auth()->user();
        $userType = $validated['user_type'] ?? $userType;

        // Get balance based on user type
        if ($userType === 'clipper') {
            $wallet = $this->walletService->getClipperWallet($user);
            $balance = $wallet->balance_available;
        } else {
            $balance = $this->balanceService->getBalance($user);
        }

        if ($balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient balance']);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            ...$validated,
            'status' => 'pending',
            'user_type' => $userType,
        ]);

        $this->notificationService->notifyWithdrawalRequest($withdrawal);

        $route = $userType === 'clipper' 
            ? 'clipper.withdrawals.index' 
            : 'marketplace.withdrawals.index';

        return redirect()->route($route)
            ->with('success', 'Withdrawal request submitted successfully.');
    }

    public function show(Withdrawal $withdrawal)
    {
        $this->authorize('view', $withdrawal);

        $withdrawal->load('admin');

        return Inertia::render('Marketplace/Withdrawals/Show', [
            'withdrawal' => $withdrawal,
        ]);
    }
}
