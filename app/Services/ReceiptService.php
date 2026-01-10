<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Order;
use App\Models\Withdrawal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class ReceiptService
{
    /**
     * Generate PDF receipt for a transaction.
     */
    public function generateTransactionReceipt(Transaction $transaction): \Barryvdh\DomPDF\PDF
    {
        $transaction->load('user');

        // Get related order or withdrawal if exists
        $order = null;
        $withdrawal = null;
        
        if ($transaction->reference_id) {
            if ($transaction->type === 'sale') {
                $order = Order::find($transaction->reference_id);
                if ($order) {
                    $order->load(['product', 'product.seller']);
                }
            } elseif ($transaction->type === 'withdrawal') {
                $withdrawal = Withdrawal::find($transaction->reference_id);
            }
        }

        return Pdf::loadView('receipts.transaction', [
            'transaction' => $transaction,
            'order' => $order,
            'withdrawal' => $withdrawal,
        ]);
    }

    /**
     * Generate PDF receipt for an order.
     */
    public function generateOrderReceipt(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->load(['product', 'product.seller', 'buyer']);

        return Pdf::loadView('receipts.order', [
            'order' => $order,
        ]);
    }

    /**
     * Export transactions to CSV.
     */
    public function exportTransactionsToCsv(Collection $transactions): string
    {
        $filename = 'transactions-' . now()->format('Y-m-d-His') . '.csv';
        $filepath = storage_path('app/temp/' . $filename);
        
        // Ensure directory exists
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Add BOM for Excel UTF-8 compatibility
        fputs($file, "\xEF\xBB\xBF");

        // Add headers
        fputcsv($file, [
            'Transaction ID',
            'Date',
            'Type',
            'Amount',
            'Status',
            'Balance Before',
            'Balance After',
            'Description',
            'Reference ID',
        ]);

        // Add rows
        foreach ($transactions as $transaction) {
            fputcsv($file, [
                $transaction->id,
                $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : '',
                $transaction->type,
                number_format($transaction->amount, 2, '.', ''),
                $transaction->status,
                number_format($transaction->balance_before, 2, '.', ''),
                number_format($transaction->balance_after, 2, '.', ''),
                $transaction->description ?? '',
                $transaction->reference_id ?? '',
            ]);
        }

        fclose($file);

        return $filepath;
    }

    /**
     * Get transaction timeline.
     */
    public function getTransactionTimeline(Transaction $transaction): array
    {
        $timeline = [];

        // Created
        $timeline[] = [
            'status' => 'created',
            'label' => 'Transaction Created',
            'timestamp' => $transaction->created_at,
            'description' => 'Transaction was created',
        ];

        // Status changes
        if ($transaction->status === 'completed') {
            $timeline[] = [
                'status' => 'completed',
                'label' => 'Completed',
                'timestamp' => $transaction->updated_at,
                'description' => 'Transaction was completed successfully',
            ];
        } elseif ($transaction->status === 'failed') {
            $timeline[] = [
                'status' => 'failed',
                'label' => 'Failed',
                'timestamp' => $transaction->updated_at,
                'description' => 'Transaction failed',
            ];
        } else {
            $timeline[] = [
                'status' => 'pending',
                'label' => 'Pending',
                'timestamp' => $transaction->created_at,
                'description' => 'Transaction is pending',
            ];
        }

        // Sort by timestamp
        usort($timeline, function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        return $timeline;
    }

    /**
     * Get order timeline.
     */
    public function getOrderTimeline(Order $order): array
    {
        $timeline = [];

        // Created
        $timeline[] = [
            'status' => 'created',
            'label' => 'Order Created',
            'timestamp' => $order->created_at,
            'description' => 'Order was created',
        ];

        // Payment status
        if ($order->payment_status === 'paid') {
            $timeline[] = [
                'status' => 'paid',
                'label' => 'Payment Received',
                'timestamp' => $order->updated_at, // In real implementation, track payment_date
                'description' => 'Payment was received',
            ];
        }

        // Order status
        if ($order->status === 'completed') {
            $timeline[] = [
                'status' => 'completed',
                'label' => 'Order Completed',
                'timestamp' => $order->updated_at,
                'description' => 'Order was completed',
            ];
        } elseif ($order->status === 'cancelled') {
            $timeline[] = [
                'status' => 'cancelled',
                'label' => 'Order Cancelled',
                'timestamp' => $order->updated_at,
                'description' => 'Order was cancelled',
            ];
        }

        // Sort by timestamp
        usort($timeline, function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        return $timeline;
    }
}

