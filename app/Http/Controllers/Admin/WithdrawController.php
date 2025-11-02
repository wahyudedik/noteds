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

class WithdrawController extends Controller
{
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

        try {
            DB::beginTransaction();

            $withdraw->status = $request->status;
            $withdraw->admin_notes = $request->admin_notes;
            $withdraw->processed_by = auth()->id();
            $withdraw->processed_at = now();
            $withdraw->save();

            // If approved, deduct from wallet
            if ($request->status === 'approved') {
                $wallet = Wallet::where('user_id', $withdraw->user_id)->first();
                
                if (!$wallet || $wallet->balance < $withdraw->amount) {
                    DB::rollBack();
                    return redirect()->route('admin.withdraws.show', $withdraw)
                        ->with('error', 'Saldo wallet user tidak mencukupi untuk approve withdraw ini.');
                }

                $wallet->balance -= $withdraw->amount;
                $wallet->save();

                $user = $withdraw->user;
                $user->wallet_balance = $wallet->balance;
                $user->save();

                // Create transaction record for withdraw
                Transaction::create([
                    'buyer_id' => $withdraw->user_id,
                    'seller_id' => $withdraw->user_id, // Self transaction
                    'note_id' => null,
                    'amount' => $withdraw->amount,
                    'commission' => 0,
                    'status' => 'success',
                    'payment_method' => 'withdraw',
                    'notes' => 'Withdraw saldo: ' . $withdraw->amount,
                ]);
            }

            DB::commit();

            $statusText = $request->status === 'approved' ? 'disetujui' : 'ditolak';
            return redirect()->route('admin.withdraws.show', $withdraw)
                ->with('success', "Withdraw berhasil {$statusText}.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.withdraws.show', $withdraw)
                ->with('error', 'Terjadi kesalahan saat memproses withdraw.');
        }
    }
}
