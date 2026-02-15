<?php

namespace App\Exports;

use App\Models\PluginOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return PluginOrder::with(['user', 'plugin', 'bankAccount'])->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Date',
            'Buyer Name',
            'Buyer Email',
            'Buyer WhatsApp',
            'Plugin Name',
            'Price Paid',
            'Bank Destination',
            'Payment Status',
            'Admin Note',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->created_at->format('Y-m-d H:i:s'),
            $order->buyer_name ?? $order->user->name,
            $order->user->email,
            $order->buyer_whatsapp,
            $order->plugin->name,
            $order->price_paid,
            $order->bankAccount->bank_name ?? 'N/A',
            ucfirst($order->payment_status),
            $order->admin_note,
        ];
    }
}
