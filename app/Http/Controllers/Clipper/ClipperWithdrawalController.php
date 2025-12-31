<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WalletService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClipperWithdrawalController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private NotificationService $notificationService
    ) {}

    public function index()
    {
        $withdrawals = Withdrawal::where('user_id', auth()->id())
            ->where('user_type', 'clipper')
            ->with('admin')
            ->latest()
            ->paginate(15);

        return Inertia::render('Clipper/Withdrawals/Index', [
            'withdrawals' => $withdrawals,
        ]);
    }

    public function create()
    {
        $wallet = $this->walletService->getClipperWallet(auth()->user());
        $minWithdrawal = config('clipper.min_withdrawal', 50000);

        return Inertia::render('Clipper/Withdrawals/Create', [
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
        $wallet = $this->walletService->getClipperWallet($user);

        if ($wallet->balance_available < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient balance']);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            ...$validated,
            'status' => 'pending',
            'user_type' => 'clipper',
        ]);

        $this->notificationService->notifyWithdrawalRequest($withdrawal);

        return redirect()->route('clipper.withdrawals.index')
            ->with('success', 'Withdrawal request submitted successfully.');
    }

    public function show(Withdrawal $withdrawal)
    {
        if ($withdrawal->user_id !== auth()->id() || $withdrawal->user_type !== 'clipper') {
            abort(403);
        }

        $withdrawal->load('admin');

        return Inertia::render('Clipper/Withdrawals/Show', [
            'withdrawal' => $withdrawal,
        ]);
    }
}

