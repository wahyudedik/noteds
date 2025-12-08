@component('mail::message')
# Payout {{ ucfirst($status) }}

@if($status === 'completed')
# Your payout has been processed successfully!

Great! Your affiliate payout has been transferred to your account via **{{ strtoupper($method) }}**.

## Payout Details

- **Amount**: {{ currency($amount) }}
- **Method**: {{ strtoupper($method) }}
- **Status**: ✓ Completed
- **Date**: {{ now()->format('M d, Y H:i') }}

The funds should appear in your account within 1-3 business days depending on your payment method.
@elseif($status === 'failed')
# Payout Failed

Unfortunately, your payout could not be processed. Please review the details below.

## Payout Details

- **Amount**: {{ currency($amount) }}
- **Method**: {{ strtoupper($method) }}
- **Status**: ✗ Failed

@if($notes)
**Reason**: {{ $notes }}
@endif

Please contact support if you need further assistance.
@else
# Payout {{ ucfirst($status) }}

Your payout request has been processed with status: **{{ ucfirst($status) }}**

## Details

- **Amount**: {{ currency($amount) }}
- **Method**: {{ strtoupper($method) }}
- **Status**: {{ ucfirst($status) }}

@if($notes)
**Notes**: {{ $notes }}
@endif
@endif

@component('mail::button', ['url' => route('affiliate.index')])
View Your Dashboard
@endcomponent

Thank you for your continued partnership!

@endcomponent
