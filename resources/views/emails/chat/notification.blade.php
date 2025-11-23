@component('mail::message')
# {{ $subject }}

{{ $messageBody }}

@if($actionUrl)
@component('mail::button', ['url' => $actionUrl])
{{ __('chat.view_conversation') }}
@endcomponent
@endif

{{ __('chat.email_footer') }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent

