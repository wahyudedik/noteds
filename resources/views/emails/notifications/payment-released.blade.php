@component('mail::message')
    # Payment Released

    Hi {{ $vendor->name }},

    Your payment for the order **{{ $order->title }}** has been released to your wallet!

    @php
        $currencyService = app(\App\Services\CurrencyService::class);
        $userCurrency = $currencyService->getUserCurrency($vendor);
        $amountDisplay = currency($amountReceived, $userCurrency, 'IDR');
    @endphp

    @component('mail::panel')
        **Amount Received:** {{ $amountDisplay }}

        **Order:** {{ $order->title }}

        **Previous Status:**
        - Buyer Approved: ✓
        - Admin Verified: ✓
        - Payment Released: ✓
    @endcomponent

    The amount is now available in your wallet. You can use it to fund new orders, withdraw to your bank account, or keep it
    for future transactions.

    @component('mail::button', ['url' => $actionUrl])
        View Wallet
    @endcomponent

    Thanks for your excellent work!<br>
    {{ config('app.name') }}
@endcomponent
