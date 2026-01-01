<x-mail::message>
# Payment Successful

Thank you for your purchase! Your payment has been processed successfully.

**Order Number:** {{ $order->order_number }}

**Product:** {{ $order->product->name }}

**Total:** Rp {{ number_format($order->total, 0, ',', '.') }}

<x-mail::button :url="route('marketplace.orders.show', $order)">
View Order Details
</x-mail::button>

Thank you for shopping with us!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

