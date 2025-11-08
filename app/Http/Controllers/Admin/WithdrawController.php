<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdraw;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Services\NotificationService;

class WithdrawController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function index(Request $request): View
    {
        $withdraws = Withdraw::with(['user', 'processedBy'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.withdraws.index', compact('withdraws'));
    }

    public function show(Withdraw $withdraw): View
    {
        $withdraw->load(['user', 'processedBy']);

        return view('admin.withdraws.show', compact('withdraw'));
    }

    public function update(Request $request, Withdraw $withdraw): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($withdraw->status !== 'pending') {
            return redirect()->route('admin.withdraws.show', $withdraw)
                ->with('error', 'Withdraw ini sudah diproses sebelumnya.');
        }

        // Check minimum 24 hours requirement
        $hoursSinceRequest = $withdraw->created_at->diffInHours(now());
        if ($request->status === 'approved' && $hoursSinceRequest < 24) {
            return redirect()->route('admin.withdraws.show', $withdraw)
                ->with('error', 'Withdraw harus menunggu minimal 24 jam sebelum dapat disetujui. Sisa waktu: ' . (24 - $hoursSinceRequest) . ' jam.');
        }

        $remainingBalance = null;

        try {
            DB::beginTransaction();

            $withdraw->status = $request->status;
            $withdraw->admin_notes = $request->admin_notes;
            $withdraw->processed_by = auth()->id();
            $withdraw->processed_at = now();
            $withdraw->save();

            $baseCurrency = config('currency.base_currency', 'IDR');

            // If approved, deduct from wallet
            if ($request->status === 'approved') {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $withdraw->user_id],
                    ['balance' => 0, 'currency' => $baseCurrency]
                );
                if ($wallet->currency !== $baseCurrency) {
                    $wallet->currency = $baseCurrency;
                }
                
                // Sync wallet balance with user wallet_balance before checking
                $user = $withdraw->user;
                if ($wallet->balance != $user->wallet_balance) {
                    $wallet->balance = $user->wallet_balance;
                    $wallet->save();
                }
                
                if ($wallet->balance < $withdraw->amount) {
                    DB::rollBack();
                    return redirect()->route('admin.withdraws.show', $withdraw)
                        ->with('error', 'Saldo wallet user tidak mencukupi untuk approve withdraw ini.');
                }

                $wallet->balance -= $withdraw->amount;
                $wallet->save();

                $user->wallet_balance = $wallet->balance;
                $user->save();

                $remainingBalance = (float) $wallet->balance;

                // Create transaction record for withdraw
                Transaction::create([
                    'buyer_id' => $withdraw->user_id,
                    'seller_id' => $withdraw->user_id, // Self transaction
                    'note_id' => null,
                    'amount' => $withdraw->amount,
                    'commission' => 0,
                    'currency' => $baseCurrency,
                    'original_amount' => $withdraw->amount,
                    'original_currency' => $baseCurrency,
                    'exchange_rate' => 1,
                    'status' => 'success',
                    'payment_method' => 'withdraw',
                    'notes' => 'Withdraw saldo: ' . $withdraw->amount,
                ]);
            }

            DB::commit();

            $statusText = $request->status === 'approved' ? 'disetujui' : 'ditolak';

            $withdraw->refresh()->loadMissing('user');
            if ($withdraw->user) {
                $this->notificationService->notifyWithdrawProcessed(
                    $withdraw->user,
                    $request->status,
                    (float) $withdraw->amount,
                    $request->admin_notes,
                    $remainingBalance
                );
            }

            return redirect()->route('admin.withdraws.show', $withdraw)
                ->with('success', "Withdraw berhasil {$statusText}.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.withdraws.show', $withdraw)
                ->with('error', 'Terjadi kesalahan saat memproses withdraw.');
        }
    }
}
