<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralTransactionController extends Controller
{
    /**
     * Display a listing of referral transactions.
     */
    public function index(Request $request): View
    {
        $query = ReferralTransaction::with('user', 'admin', 'referral')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Summary statistics
        $totalTransactions = $query->count();
        $totalSent = (clone $query)->where('status', 'sent')->sum('amount');
        $totalPending = (clone $query)->where('status', 'pending')->sum('amount');
        $totalFailed = (clone $query)->where('status', 'failed')->sum('amount');

        $transactions = $query->paginate(25);
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.referral-transactions.index', compact(
            'transactions',
            'users',
            'totalTransactions',
            'totalSent',
            'totalPending',
            'totalFailed'
        ));
    }

    /**
     * Show the details of a specific referral transaction.
     */
    public function show(ReferralTransaction $referralTransaction): View
    {
        $referralTransaction->load('user', 'admin', 'referral');

        return view('admin.referral-transactions.show', compact('referralTransaction'));
    }

    /**
     * Export referral transactions to CSV.
     */
    public function export(Request $request)
    {
        $query = ReferralTransaction::with('user', 'admin')
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $transactions = $query->get();

        $filename = 'referral-transactions-' . now()->format('Y-m-d') . '.csv';

        $headers = array(
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        );

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User', 'Email', 'Amount', 'Type', 'Status', 'Sent At', 'Created At']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->user->name,
                    $transaction->user->email,
                    'Rp ' . number_format((float) $transaction->amount, 0, ',', '.'),
                    ucfirst(str_replace('_', ' ', $transaction->type)),
                    strtoupper($transaction->status),
                    $transaction->sent_at?->format('Y-m-d H:i:s') ?? '-',
                    $transaction->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
