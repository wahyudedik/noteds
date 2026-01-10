<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Order;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    public function __construct(
        private ReceiptService $receiptService
    ) {}

    /**
     * Download transaction receipt as PDF.
     */
    public function downloadTransactionReceipt(Transaction $transaction)
    {
        // Authorization - user can only download their own receipts
        $user = auth()->user();
        if ($transaction->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $pdf = $this->receiptService->generateTransactionReceipt($transaction);
        
        return $pdf->download('receipt-' . $transaction->id . '.pdf');
    }

    /**
     * View transaction receipt (in browser).
     */
    public function viewTransactionReceipt(Transaction $transaction)
    {
        // Authorization
        $user = auth()->user();
        if ($transaction->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $pdf = $this->receiptService->generateTransactionReceipt($transaction);
        
        return $pdf->stream('receipt-' . $transaction->id . '.pdf');
    }

    /**
     * Download order receipt as PDF.
     */
    public function downloadOrderReceipt(Order $order)
    {
        // Authorization - user can only download their own receipts
        $user = auth()->user();
        if ($order->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Only allow download for paid or completed orders
        if (!in_array($order->payment_status, ['paid', 'completed'])) {
            abort(403, 'Receipt is only available for paid or completed orders');
        }

        $pdf = $this->receiptService->generateOrderReceipt($order);
        
        return $pdf->download('receipt-' . $order->order_number . '.pdf');
    }

    /**
     * Export transactions to CSV.
     */
    public function exportTransactions(Request $request)
    {
        $user = auth()->user();
        
        $query = Transaction::where('user_id', $user->id);

        // Apply filters
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $filepath = $this->receiptService->exportTransactionsToCsv($transactions);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    /**
     * Get transaction timeline.
     */
    public function getTransactionTimeline(Transaction $transaction)
    {
        // Authorization
        $user = auth()->user();
        if ($transaction->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $timeline = $this->receiptService->getTransactionTimeline($transaction);

        return response()->json([
            'timeline' => $timeline,
        ]);
    }

    /**
     * Get order timeline.
     */
    public function getOrderTimeline(Order $order)
    {
        // Authorization
        $user = auth()->user();
        if ($order->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $timeline = $this->receiptService->getOrderTimeline($order);

        return response()->json([
            'timeline' => $timeline,
        ]);
    }
}
