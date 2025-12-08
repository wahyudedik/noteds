@component('mail::message')
# Share Commission Payment Received

Hi **{{ $seller->name }}**,

Great news! Your share commission for **{{ $month }}** has been processed and paid to your wallet.

## Payment Details

- **Month**: {{ $month }}
- **Total Shares**: {{ number_format($shareCount) }} share{{ $shareCount !== 1 ? 's' : '' }}
- **Commission Amount**: {{ config('currency.symbol') }}{{ number_format($amount, 2) }}

## What's Included

This payment includes all commissions accumulated from note shares during {{ $month }}. The commission was automatically transferred to your wallet account.

@component('mail::button', ['url' => $dashboardUrl, 'color' => 'success'])
View Share Analytics
@endcomponent

Thank you for being part of our community and sharing great content!

---

**Share Analytics Team**

@endcomponent
