@component('mail::message')
    # Work Rejected

    Hi {{ $vendor->name }},

    Unfortunately, your work submission for **{{ $order->title }}** has been rejected by the buyer **{{ $buyer->name }}**.

    @component('mail::panel')
        **Feedback:**

        {{ $rejectionReason }}
    @endcomponent

    Please review the feedback and resubmit your work. The buyer is ready to approve it once it meets their requirements.

    @component('mail::button', ['url' => $actionUrl])
        Resubmit Work
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
