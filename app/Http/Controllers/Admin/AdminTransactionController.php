<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Sale;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminTransactionController extends Controller
{
    /**
     * Display list of all transactions with filters
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorize('manage-transactions');

        $query = Transaction::with('buyer', 'seller', 'note');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by transaction type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Search by transaction ID, buyer, seller, or note
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhereHas('buyer', fn($q) => $q->where('name', 'like', "%$search%"))
                    ->orWhereHas('seller', fn($q) => $q->where('name', 'like', "%$search%"))
                    ->orWhereHas('note', fn($q) => $q->where('title', 'like', "%$search%"));
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

        $transactions = $query->latest('created_at')->paginate(15);

        $stats = $this->getTransactionStats();

        return view('admin.data-management.transactions', [
            'transactions' => $transactions,
            'stats' => $stats,
        ]);
    }

    /**
     * Show transaction details
     *
     * @param Transaction $transaction
     * @return View
     */
    public function show(Transaction $transaction): View
    {
        $this->authorize('manage-transactions');

        return view('admin.data-management.transaction-detail', [
            'transaction' => $transaction->load('buyer', 'seller', 'note'),
        ]);
    }

    /**
     * Refund transaction
     *
     * @param Request $request
     * @param Transaction $transaction
     * @return RedirectResponse
     */
    public function refund(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('manage-transactions');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Check if already refunded
        if ($transaction->status === 'refunded') {
            return redirect()->back()->with('error', 'Transaksi sudah dikembalikan');
        }

        // Create refund record
        $refund = Refund::create([
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Update transaction status
        $transaction->update([
            'status' => 'refunding',
        ]);

        activity('admin')
            ->performedOn($transaction)
            ->withProperties(['reason' => $request->reason])
            ->log('Transaction refund initiated');

        return redirect()->back()->with('success', 'Pengembalian dana dimulai');
    }

    /**
     * Mark transaction as completed
     *
     * @param Transaction $transaction
     * @return RedirectResponse
     */
    public function markCompleted(Transaction $transaction): RedirectResponse
    {
        $this->authorize('manage-transactions');

        if ($transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya transaksi pending yang dapat ditandai selesai');
        }

        $transaction->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        activity('admin')
            ->performedOn($transaction)
            ->log('Transaction marked as completed');

        return redirect()->back()->with('success', 'Transaksi ditandai selesai');
    }

    /**
     * Mark transaction as failed
     *
     * @param Request $request
     * @param Transaction $transaction
     * @return RedirectResponse
     */
    public function markFailed(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('manage-transactions');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $transaction->update([
            'status' => 'failed',
            'failure_reason' => $request->reason,
        ]);

        activity('admin')
            ->performedOn($transaction)
            ->withProperties(['reason' => $request->reason])
            ->log('Transaction marked as failed');

        return redirect()->back()->with('success', 'Transaksi ditandai gagal');
    }

    /**
     * Get transaction statistics
     *
     * @return array
     */
    private function getTransactionStats(): array
    {
        $today = Carbon::now()->startOfDay();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total' => Transaction::count(),
            'completed' => Transaction::where('status', 'completed')->count(),
            'pending' => Transaction::where('status', 'pending')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
            'totalRevenue' => Transaction::where('status', 'completed')->sum('amount'),
            'platformCommission' => Transaction::where('status', 'completed')->sum('platform_commission'),
            'sellerEarnings' => Transaction::where('status', 'completed')->sum('seller_amount'),
            'todayTransactions' => Transaction::whereDate('created_at', $today)->count(),
            'todayRevenue' => Transaction::where('status', 'completed')->whereDate('created_at', $today)->sum('amount'),
            'monthlyRevenue' => Transaction::where('status', 'completed')->whereDate('created_at', '>=', $thisMonth)->sum('amount'),
        ];
    }

    /**
     * Export transactions as CSV
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $this->authorize('export-transactions');

        $query = Transaction::query();

        // Apply same filters as index
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=transactions-' . now()->format('Y-m-d') . '.csv',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            // Write headers
            fputcsv($file, [
                'ID', 'Buyer', 'Seller', 'Note', 'Amount', 'Commission', 'Status', 'Date'
            ]);

            // Write data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->buyer->name,
                    $transaction->seller->name,
                    $transaction->note->title,
                    $transaction->amount,
                    $transaction->platform_commission,
                    $transaction->status,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
