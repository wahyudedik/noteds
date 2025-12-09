@component('mail::message')
# Dispute Resolved

Hi {{ $notifiable->name }},

The dispute for order **#{{ $order->id }}** has been resolved.

**Resolution Details:**
- Resolution Type: {{ $dispute->getResolutionTypeLabel() }}
- Resolution: {{ $dispute->resolution }}
- Resolved by: {{ $dispute->resolver->name }}

@if($dispute->resolution_type === 'refund_buyer')
A refund has been issued to the buyer's wallet.
@elseif($dispute->resolution_type === 'payment_vendor')
Payment has been released to the vendor's wallet.
@elseif($dispute->resolution_type === 'partial_refund')
A partial refund and payment have been processed according to the resolution.
@endif

@component('mail::button', ['url' => route('disputes.show', $dispute)])
View Resolution Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
