@component('mail::message')
    # Affiliate Payout Request

    An affiliate has requested a payout. Please review and process this request.

    ## Request Details

    - **Affiliate**: {{ $affiliate->username }}
    - **Email**: {{ $affiliate->email }}
    - **Amount**: {{ currency($amount) }}
    - **Method**: {{ $method }}
    - **Date**: {{ $payout->created_at?->format('M d, Y H:i') }}

    @component('mail::button', ['url' => route('admin.affiliate.payouts')])
        Review Request in Admin Panel
    @endcomponent

    Please verify that the amount and method are correct before processing.
@endcomponent
