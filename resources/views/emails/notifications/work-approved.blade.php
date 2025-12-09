@component('mail::message')
    # Work Approved

    Hi {{ $vendor->name }},

    Great news! Your work for the order **{{ $order->title }}** has been approved by the buyer **{{ $buyer->name }}**.

    @component('mail::panel')
        **{{ $order->title }}**
        Status: Work Approved ✓
    @endcomponent

    The order is now waiting for admin verification before payment is released to your wallet.

    @component('mail::button', ['url' => $actionUrl])
        View Order
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
