@component('mail::message')
    # New Share - Earn Commission!

    Hi **{{ $seller->name }}**,

    Congratulations! Someone just shared your note and you've earned a commission!

    ## Share Details

    - **Note**: {{ $noteTitle }}
    - **Shared By**: {{ $sharedByName }}
    - **Commission**: {{ config('currency.symbol') }}{{ number_format($commissionAmount, 2) }}

    ## Commission Status

    ✅ **Commission tracked and will be processed**

    Your commission will be paid according to your payment mode settings (immediate or monthly).

    Each time someone purchases or accesses your note through this share link, you'll earn a commission! Build your passive
    income by sharing your valuable content.

    @component('mail::button', ['url' => $dashboardUrl, 'color' => 'primary'])
        View Full Analytics
    @endcomponent

    Keep sharing and earning!

    ---

    **Share Analytics Team**
@endcomponent
