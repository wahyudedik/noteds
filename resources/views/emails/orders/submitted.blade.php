<x-mail::message>
# Order Submitted

Hi {{ $order->buyer_name }},

Thank you for your order! We have received your payment proof for **{{ $order->plugin->name }}**.
Our admin will verify your payment shortly.

**Order Details:**
- Order ID: #{{ substr($order->id, 0, 8) }}
- Plugin: {{ $order->plugin->name }}
- Price: Rp {{ number_format($order->price_paid, 0, ',', '.') }}
- Bank: {{ $order->bankAccount->bank_name ?? 'N/A' }}

You will receive another email once your payment is verified with the download link.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
