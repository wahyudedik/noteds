<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\BalanceService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private NotificationService $notificationService
    ) {}

    public function index()
    {
        $withdrawals = Withdrawal::where('user_id', auth()->id())
            ->with('admin')
            ->latest()
            ->paginate(15);

        return Inertia::render('Marketplace/Withdrawals/Index', [
            'withdrawals' => $withdrawals,
        ]);
    }

    public function create()
    {
        $balance = $this->balanceService->getBalance(auth()->user());

        return Inertia::render('Marketplace/Withdrawals/Create', [
            'balance' => $balance,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:50000',
            'method' => 'required|in:bank_transfer,ewallet',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
            'bank_name' => 'required_if:method,bank_transfer|nullable|string',
            'ewallet_type' => 'required_if:method,ewallet|nullable|in:OVO,GoPay,DANA,LinkAja',
        ]);

        $user = auth()->user();
        $balance = $this->balanceService->getBalance($user);

        if ($balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient balance']);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            ...$validated,
            'status' => 'pending',
        ]);

        $this->notificationService->notifyWithdrawalRequest($withdrawal);

        return redirect()->route('marketplace.withdrawals.index')
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
