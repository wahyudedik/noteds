@component('mail::message')
    # New Conversion Recorded

    Great news! You've received a new conversion from your affiliate link **{{ $linkName }}**.

    ## Conversion Details

    - **Converter**: {{ $converterName }}
    - **Commission Earned**: {{ currency($commission) }}
    - **Tier**: Tier {{ $tier }}
    - **Link**: {{ $linkName }}
    - **Date**: {{ $conversion->created_at?->format('M d, Y H:i') }}

    This commission has been added to your pending balance and will be transferred to your wallet on the scheduled payout
    date.

    @component('mail::button', ['url' => route('affiliate.index')])
        View Your Dashboard
    @endcomponent

    Thank you for promoting our platform!
@endcomponent
