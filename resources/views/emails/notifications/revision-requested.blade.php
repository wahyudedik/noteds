@component('mail::message')
    # Revision Requested

    Hi {{ $notifiable->name }},

    A revision has been requested for your work on order **#{{ $order->id }}**.

    **Revision Details:**
    - Revision #: {{ $revision->revision_number }}
    - Reason: {{ $revision->request_reason }}
    - Requested by: {{ $revision->requester->name }}

    Please submit the revised work through your dashboard. You have a limited number of revisions allowed.

    @component('mail::button', ['url' => route('studio.orders.work-detail', $order)])
        View Order Details
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
