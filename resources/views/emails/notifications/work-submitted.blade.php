@component('mail::message')
    # Work Submitted

    Hi {{ $buyer->name }},

    The vendor **{{ $vendor->name }}** has submitted their work for your order:

    @component('mail::panel')
        **{{ $order->title }}**
        Budget: Rp {{ number_format($order->budget, 0, ',', '.') }}
    @endcomponent

    The work is now pending your review. Please review the submission and approve or reject it.

    @component('mail::button', ['url' => $actionUrl])
        Review Work
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
