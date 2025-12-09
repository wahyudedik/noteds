@component('mail::message')
# New Message

Hi {{ $notifiable->name }},

You have received a new message from **{{ $sender->name }}**.

**Message Preview:**
> {{ substr($message->message, 0, 150) }}{{ strlen($message->message) > 150 ? '...' : '' }}

Click the button below to view the full conversation.

@component('mail::button', ['url' => route('messages.show', $sender)])
View Message
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
