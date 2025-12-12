@component('mail::message')
    # Work Submitted

    Hi {{ $buyer->name }},

    The vendor **{{ $vendor->name }}** has submitted their work for your order:

    @php
        $currencyService = app(\App\Services\CurrencyService::class);
        $userCurrency = $currencyService->getUserCurrency($buyer);
        $budgetDisplay = currency($order->budget, $userCurrency, 'IDR');
    @endphp

    @component('mail::panel')
        **{{ $order->title }}**
        Budget: {{ $budgetDisplay }}
    @endcomponent

    The work is now pending your review. Please review the submission and approve or reject it.

    @component('mail::button', ['url' => $actionUrl])
        Review Work
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
