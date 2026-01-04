<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Refund;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BalanceService;
use App\Services\LedgerService;
use App\Services\NotificationService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminRefundController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private LedgerService $ledgerService,
        private NotificationService $notificationService,
        private BalanceService $balanceService
    ) {}

    /**
     * Display a listing of refunds.
     */
    public function index(Request $request)
    {
        $query = Refund::with(['user', 'admin']);

        // Filter by wallet type
        if ($request->has('wallet_type') && $request->wallet_type !== 'all') {
            $query->where('wallet_type', $request->wallet_type);
        }

        // Filter by type (refund/adjustment)
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Search by user name or email
        if ($request->has('search') && $request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $refunds = $query->latest()->paginate(20);

        return Inertia::render('Admin/Refunds/Index', [
            'refunds' => $refunds,
            'filters' => [
                'wallet_type' => $request->wallet_type ?? 'all',
                'type' => $request->type ?? 'all',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Show the form for creating a new refund.
     */
    public function create()
    {
        return Inertia::render('Admin/Refunds/Create');
    }

    /**
     * Store a newly created refund.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'wallet_type' => 'required|in:creator,marketplace',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:refund,adjustment',
            'reason' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = User::findOrFail($validated['user_id']);
            $adminId = auth()->id();

            // Get wallet and balance
            $balanceBefore = 0;
            $balanceAfter = 0;

            if ($validated['wallet_type'] === 'creator') {
                // Creator wallet
                $wallet = $this->walletService->getCreatorWallet($user);
                $balanceBefore = $wallet->balance_available;

                // Process refund or adjustment
                if ($validated['type'] === 'refund') {
                    $wallet->addBalance($validated['amount']);
                } else {
                    // Deduct balance (adjustment)
                    if (!$wallet->deductBalance($validated['amount'])) {
                        return back()->withErrors(['amount' => 'Insufficient balance for adjustment.']);
                    }
                }

                $balanceAfter = $wallet->balance_available;
            } else {
                // Marketplace wallet (seller) - uses user.balance
                $balanceBefore = $user->balance;

                // Process refund or adjustment
                if ($validated['type'] === 'refund') {
                    $this->balanceService->addBalance(
                        $user,
                        $validated['amount'],
                        'Admin refund: ' . ($validated['reason'] ?? 'No reason provided'),
                        null,
                        'refund'
                    );
                } else {
                    // Deduct balance (adjustment) - we need to manually create transaction with 'adjustment' type
                    if ($user->balance < $validated['amount']) {
                        return back()->withErrors(['amount' => 'Insufficient balance for adjustment.']);
                    }

                    $user->decrement('balance', $validated['amount']);
                    $user->refresh();
                    $balanceAfter = $user->balance;

                    // Create transaction with 'adjustment' type
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'adjustment',
                        'amount' => $validated['amount'],
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'status' => 'completed',
                        'reference_id' => null,
                        'description' => 'Admin adjustment: ' . ($validated['reason'] ?? 'No reason provided'),
                    ]);
                }
            }

            // Create refund record
            $refund = Refund::create([
                'user_id' => $validated['user_id'],
                'wallet_type' => $validated['wallet_type'],
                'amount' => $validated['amount'],
                'type' => $validated['type'],
                'reason' => $validated['reason'] ?? null,
                'admin_id' => $adminId,
                'admin_notes' => $validated['admin_notes'] ?? null,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);

            // Create ledger entry (only for creator wallet, marketplace uses Transaction model)
            if ($validated['wallet_type'] === 'creator') {
                $platformWallet = $this->walletService->getPlatformWallet();
                $wallet = $this->walletService->getCreatorWallet($user);
                $ledgerEntry = $this->ledgerService->createEntry([
                    'from_wallet_type' => $validated['type'] === 'refund' ? 'platform' : 'creator',
                    'from_wallet_id' => $validated['type'] === 'refund' ? $platformWallet->id : $wallet->id,
                    'to_wallet_type' => $validated['type'] === 'refund' ? 'creator' : 'platform',
                    'to_wallet_id' => $validated['type'] === 'refund' ? $wallet->id : $platformWallet->id,
                    'amount' => $validated['amount'],
                    'reason' => 'refund',
                    'reference_type' => 'refund',
                    'reference_id' => $refund->id,
                    'admin_id' => $adminId,
                    'metadata' => [
                        'type' => $validated['type'],
                        'reason' => $validated['reason'],
                        'admin_notes' => $validated['admin_notes'],
                    ],
                ]);

                $refund->update(['ledger_entry_id' => $ledgerEntry->id]);
            }

            // Create audit log
            $wallet = $validated['wallet_type'] === 'creator' ? $this->walletService->getCreatorWallet($user) : null;
            $targetId = $validated['wallet_type'] === 'creator' ? $wallet->id : $user->id;
            $notes = ($validated['reason'] ?? '') . ($validated['admin_notes'] ? ($validated['reason'] ? ' | ' : '') . 'Admin Notes: ' . $validated['admin_notes'] : '');
            AuditLog::logAction([
                'user_id' => $validated['user_id'],
                'admin_id' => $adminId,
                'action' => $validated['type'] === 'refund' ? 'refund_balance' : 'adjust_balance',
                'target_type' => $validated['wallet_type'] === 'creator' ? 'wallet' : 'user',
                'target_id' => $targetId,
                'old_value' => ['balance' => $balanceBefore],
                'new_value' => ['balance' => $balanceAfter],
                'notes' => $notes ?: null,
            ]);

            // Notify user about refund/adjustment
            $this->notificationService->notifyRefundProcessed($refund);

            return redirect()->route('admin.refunds.index')
                ->with('success', ucfirst($validated['type']) . ' processed successfully.');
        });
    }

    /**
     * Display the specified refund.
     */
    public function show(Refund $refund)
    {
        $refund->load(['user', 'admin', 'ledgerEntry']);

        return Inertia::render('Admin/Refunds/Show', [
            'refund' => $refund,
        ]);
    }

    /**
     * Search users by name or email (API endpoint).
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
                ->orWhere('email', 'like', '%' . $query . '%');
        })
            ->select('id', 'name', 'email', 'avatar')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
