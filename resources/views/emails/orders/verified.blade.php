<x-mail::message>
    # Payment Verified!

    Hi {{ $order->buyer_name }},

    Great news! Your payment for **{{ $order->plugin->name }}** has been verified.
    You can now download your plugin using the button below.

    <x-mail::button :url="route('marketplace.download', $order->plugin->id)">
        Download Product
    </x-mail::button>

@if(!empty($adminWhatsapp))
<x-mail::button :url="'https://wa.me/' . $adminWhatsapp">
Chat Admin via WhatsApp
</x-mail::button>
@endif

    If you have any issues, please reply to this email.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
