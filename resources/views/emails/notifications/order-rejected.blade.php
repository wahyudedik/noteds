@component('mail::message')
    # Order Rejected

    Hi {{ $buyer->name }},

    Unfortunately, the work submitted for your order **{{ $order->title }}** did not meet the quality standards and has been
    rejected by our admin team.

    @component('mail::panel')
        **Reason for Rejection:**

        {{ $rejectionReason }}
    @endcomponent

    **Refund Processed:**
    - Amount Refunded: Rp {{ number_format($refundAmount, 0, ',', '.') }}
    - Status: Returned to your wallet ✓

    You have two options:
    1. **Work with the vendor again** - Request the vendor to resubmit with improvements addressing the feedback
    2. **Start a new order** - Post a new order for a different vendor to complete the work

    @component('mail::button', ['url' => $actionUrl])
        View Order
    @endcomponent

    We apologize for the inconvenience. Our team is committed to ensuring quality work for all orders.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
