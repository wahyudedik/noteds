<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderExportService
{
    /**
     * Export orders to PDF.
     */
    public function exportToPdf(Collection $orders, User $user): string
    {
        $orders->load(['product', 'product.seller', 'buyer', 'items.product', 'trackingHistory']);

        $pdf = Pdf::loadView('exports.orders-pdf', [
            'orders' => $orders,
            'user' => $user,
        ]);

        $filename = 'orders-' . now()->format('Y-m-d-His') . '.pdf';
        $directory = 'exports/orders';
        $filepath = $directory . '/' . $filename;

        // Ensure directory exists
        if (!Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }

        $fullPath = storage_path('app/' . $filepath);
        $pdf->save($fullPath);

        return $fullPath;
    }

    /**
     * Export orders to Excel (CSV format).
     */
    public function exportToExcel(Collection $orders, User $user): string
    {
        return $this->exportToCsv($orders, $user);
    }

    /**
     * Export orders to CSV.
     */
    public function exportToCsv(Collection $orders, User $user): StreamedResponse
    {
        $orders->load(['product', 'items.product', 'trackingHistory']);

        $filename = 'orders-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Add headers
            fputcsv($file, [
                'Order Number',
                'Date',
                'Product(s)',
                'Quantity',
                'Price',
                'Total',
                'Discount Amount',
                'Status',
                'Payment Status',
                'Payment Method',
            ]);

            // Add rows
            foreach ($orders as $order) {
                if ($order->isBulkOrder() && $order->items->isNotEmpty()) {
                    // Bulk order - list all items
                    foreach ($order->items as $item) {
                        fputcsv($file, [
                            $order->order_number,
                            $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
                            $item->product->name ?? 'N/A',
                            $item->quantity,
                            number_format($item->price, 2, '.', ''),
                            number_format($item->subtotal, 2, '.', ''),
                            number_format($order->discount_amount ?? 0, 2, '.', ''),
                            $order->status,
                            $order->payment_status,
                            $order->midtrans_order_id ? 'Midtrans' : 'N/A',
                        ]);
                    }
                } else {
                    // Single product order
                    fputcsv($file, [
                        $order->order_number,
                        $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
                        $order->product->name ?? 'N/A',
                        $order->quantity,
                        number_format($order->price, 2, '.', ''),
                        number_format($order->total, 2, '.', ''),
                        number_format($order->discount_amount ?? 0, 2, '.', ''),
                        $order->status,
                        $order->payment_status,
                        $order->midtrans_order_id ? 'Midtrans' : 'N/A',
                    ]);
                }
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Format orders for export.
     */
    public function formatOrdersForExport(Collection $orders): array
    {
        $orders->load(['product', 'items.product', 'trackingHistory', 'buyer']);

        return $orders->map(function ($order) {
            $data = [
                'order_number' => $order->order_number,
                'date' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total' => number_format($order->total, 2, '.', ''),
                'discount_amount' => number_format($order->discount_amount ?? 0, 2, '.', ''),
            ];

            if ($order->isBulkOrder() && $order->items->isNotEmpty()) {
                $data['items'] = $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'price' => number_format($item->price, 2, '.', ''),
                        'subtotal' => number_format($item->subtotal, 2, '.', ''),
                    ];
                })->toArray();
            } else {
                $data['product_name'] = $order->product->name ?? 'N/A';
                $data['quantity'] = $order->quantity;
                $data['price'] = number_format($order->price, 2, '.', '');
            }

            $data['tracking_timeline'] = $order->trackingHistory->map(function ($tracking) {
                return [
                    'status' => $tracking->status,
                    'payment_status' => $tracking->payment_status,
                    'message' => $tracking->message,
                    'date' => $tracking->created_at ? $tracking->created_at->format('Y-m-d H:i:s') : '',
                ];
            })->toArray();

            return $data;
        })->toArray();
    }
}

