@component('mail::message')
    # Order Verified

    Hi {{ $notifiable->name }},

    The order **{{ $order->title }}** has been verified by our admin team and approved for final payment release.

    @php
        $currencyService = app(\App\Services\CurrencyService::class);
        $userCurrency = $currencyService->getUserCurrency($notifiable);
        $paymentAmount = $order->budget * 0.9;
        $paymentDisplay = currency($paymentAmount, $userCurrency, 'IDR');
    @endphp

    @component('mail::panel')
        **Order Details:**
        - Title: {{ $order->title }}
        - Status: ✓ Verified & Approved
        @if ($isVendor)
            - Your Payment: {{ $paymentDisplay }} (after 10% platform fee)
        @endif

        **Admin Notes:**
        {{ $verificationNotes ?? 'No additional notes.' }}
    @endcomponent

    @if ($isVendor)
        The payment has been released to your wallet. You can now withdraw or use it for other orders.
    @else
        The payment has been released to the vendor's wallet. Your escrow funds are confirmed as used for this transaction.
    @endif

    @component('mail::button', ['url' => $actionUrl])
        {{ $isVendor ? 'View Wallet' : 'View Order' }}
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
