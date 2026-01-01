<x-mail::message>
# Order Status Updated

Your order status has been updated.

**Order Number:** {{ $order->order_number }}

**Product:** {{ $order->product->name }}

**New Status:** {{ ucfirst($order->status) }}

<x-mail::button :url="route('marketplace.orders.show', $order)">
View Order Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

