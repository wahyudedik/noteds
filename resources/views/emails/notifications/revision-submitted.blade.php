@component('mail::message')
    # Revision Submitted

    Hi {{ $notifiable->name }},

    The vendor has submitted a revision for order **#{{ $order->id }}**.

    **Revision Details:**
    - Revision #: {{ $revision->revision_number }}
    @if ($revision->submission_notes)
        - Notes: {{ $revision->submission_notes }}
    @endif

    Please review the revised work and either accept or reject it.

    @component('mail::button', ['url' => route('studio.orders.buyer-approval', $order)])
        Review & Approve/Reject
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
