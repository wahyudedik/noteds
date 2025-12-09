@component('mail::message')
# Dispute Filed

Hi {{ $notifiable->name }},

A dispute has been filed for order **#{{ $order->id }}**.

**Dispute Details:**
- Initiated by: {{ $dispute->initiator->name }}
- Reason: {{ $dispute->reason }}
- Status: {{ $dispute->getStatusLabel() }}

Our team will review both parties' evidence and resolve the dispute within 5-7 business days.

@component('mail::button', ['url' => route('disputes.show', $dispute)])
View Dispute Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
