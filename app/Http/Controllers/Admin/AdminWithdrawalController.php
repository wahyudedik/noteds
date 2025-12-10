<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\User;
use App\Models\BankAccount;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminWithdrawalController extends Controller
{
    /**
     * Display list of withdrawals
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorize('manage-withdrawals');

        $query = Withdrawal::with('user', 'bankAccount');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by method
        if ($request->has('method') && $request->method !== '') {
            $query->where('method', $request->method);
        }

        // Search by user or bank account
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($q) => $q->where('name', 'like', "%$search%"))
                    ->orWhereHas('bankAccount', fn($q) => $q->where('account_number', 'like', "%$search%"));
            });
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by amount range
        if ($request->has('min_amount') && $request->min_amount !== '') {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->has('max_amount') && $request->max_amount !== '') {
            $query->where('amount', '<=', $request->max_amount);
        }

        $withdrawals = $query->latest('created_at')->paginate(15);

        $stats = $this->getWithdrawalStats();

        return view('admin.data-management.withdrawals', [
            'withdrawals' => $withdrawals,
            'stats' => $stats,
        ]);
    }

    /**
     * Show withdrawal details
     *
     * @param Withdrawal $withdrawal
     * @return View
     */
    public function show(Withdrawal $withdrawal): View
    {
        $this->authorize('manage-withdrawals');

        return view('admin.data-management.withdrawal-detail', [
            'withdrawal' => $withdrawal->load('user', 'bankAccount'),
        ]);
    }

    /**
     * Approve withdrawal
     *
     * @param Request $request
     * @param Withdrawal $withdrawal
     * @return RedirectResponse
     */
    public function approve(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $this->authorize('manage-withdrawals');

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if already processed
        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Penarikan dana ini sudah diproses');
        }

        DB::transaction(function () use ($withdrawal, $request) {
            // Update withdrawal status
            $withdrawal->update([
                'status' => 'approved',
                'approved_at' => now(),
                'admin_notes' => $request->notes,
                'approved_by' => auth()->id(),
            ]);

            // Update user balance
            $withdrawal->user->decrement('wallet_balance', $withdrawal->amount);

            // Log activity
            activity('admin')
                ->performedOn($withdrawal)
                ->withProperties(['notes' => $request->notes])
                ->log('Withdrawal approved');
        });

        return redirect()->back()->with('success', 'Penarikan dana berhasil disetujui');
    }

    /**
     * Reject withdrawal
     *
     * @param Request $request
     * @param Withdrawal $withdrawal
     * @return RedirectResponse
     */
    public function reject(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $this->authorize('manage-withdrawals');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Check if already processed
        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Penarikan dana ini sudah diproses');
        }

        DB::transaction(function () use ($withdrawal, $request) {
            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $request->reason,
                'rejected_by' => auth()->id(),
            ]);

            // Refund user balance
            $withdrawal->user->increment('wallet_balance', $withdrawal->amount);

            // Log activity
            activity('admin')
                ->performedOn($withdrawal)
                ->withProperties(['reason' => $request->reason])
                ->log('Withdrawal rejected');
        });

        return redirect()->back()->with('success', 'Penarikan dana berhasil ditolak');
    }

    /**
     * Mark withdrawal as transferred
     *
     * @param Request $request
     * @param Withdrawal $withdrawal
     * @return RedirectResponse
     */
    public function markTransferred(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $this->authorize('manage-withdrawals');

        $request->validate([
            'proof_url' => 'nullable|string',
        ]);

        // Check if approved
        if ($withdrawal->status !== 'approved') {
            return redirect()->back()->with('error', 'Hanya penarikan yang disetujui dapat ditandai selesai');
        }

        $withdrawal->update([
            'status' => 'transferred',
            'transferred_at' => now(),
            'proof_url' => $request->proof_url,
        ]);

        activity('admin')
            ->performedOn($withdrawal)
            ->log('Withdrawal marked as transferred');

        return redirect()->back()->with('success', 'Penarikan dana ditandai selesai');
    }

    /**
     * Mark withdrawal as disputed
     *
     * @param Request $request
     * @param Withdrawal $withdrawal
     * @return RedirectResponse
     */
    public function markDisputed(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $this->authorize('manage-withdrawals');

        $request->validate([
            'dispute_reason' => 'required|string|max:500',
        ]);

        $withdrawal->update([
            'status' => 'disputed',
            'dispute_reason' => $request->dispute_reason,
        ]);

        activity('admin')
            ->performedOn($withdrawal)
            ->withProperties(['reason' => $request->dispute_reason])
            ->log('Withdrawal marked as disputed');

        return redirect()->back()->with('success', 'Penarikan dana ditandai sebagai dispute');
    }

    /**
     * Get withdrawal statistics
     *
     * @return array
     */
    private function getWithdrawalStats(): array
    {
        return [
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'approved' => Withdrawal::where('status', 'approved')->count(),
            'transferred' => Withdrawal::where('status', 'transferred')->count(),
            'rejected' => Withdrawal::where('status', 'rejected')->count(),
            'disputed' => Withdrawal::where('status', 'disputed')->count(),
            'totalPending' => Withdrawal::where('status', 'pending')->sum('amount'),
            'totalApproved' => Withdrawal::where('status', 'approved')->sum('amount'),
            'totalTransferred' => Withdrawal::where('status', 'transferred')->sum('amount'),
            'totalRejected' => Withdrawal::where('status', 'rejected')->sum('amount'),
        ];
    }

    /**
     * Bulk approve withdrawals
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $this->authorize('manage-withdrawals');

        $request->validate([
            'withdrawal_ids' => 'required|array|min:1',
            'withdrawal_ids.*' => 'exists:withdrawals,id',
        ]);

        $withdrawals = Withdrawal::whereIn('id', $request->withdrawal_ids)
            ->where('status', 'pending')
            ->get();

        foreach ($withdrawals as $withdrawal) {
            $withdrawal->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            $withdrawal->user->decrement('wallet_balance', $withdrawal->amount);
        }

        activity('admin')
            ->log('Bulk approved ' . $withdrawals->count() . ' withdrawals');

        return redirect()->back()->with('success', 'Penarikan dana berhasil disetujui');
    }

    /**
     * Export withdrawals as CSV
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $this->authorize('export-withdrawals');

        $query = Withdrawal::with('user', 'bankAccount');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=withdrawals-' . now()->format('Y-m-d') . '.csv',
        ];

        $callback = function () use ($withdrawals) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'User', 'Bank Account', 'Amount', 'Method', 'Status', 'Date'
            ]);

            foreach ($withdrawals as $withdrawal) {
                fputcsv($file, [
                    $withdrawal->id,
                    $withdrawal->user->name,
                    $withdrawal->bankAccount->account_number,
                    $withdrawal->amount,
                    $withdrawal->method,
                    $withdrawal->status,
                    $withdrawal->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
