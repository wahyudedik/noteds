@component('mail::message')
# Revision Rejected

Hi {{ $notifiable->name }},

Your submitted revision for order **#{{ $order->id }}** has been rejected.

**Revision Details:**
- Revision #: {{ $revision->revision_number }}
- Rejection Reason: {{ $revision->rejection_reason }}
- Revisions Remaining: {{ $order->getRemainingRevisions() }}

Please submit another revision addressing the feedback. You can review the details and resubmit through your dashboard.

@component('mail::button', ['url' => route('studio.orders.work-detail', $order)])
Submit Next Revision
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
