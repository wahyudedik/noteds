<x-mail::message>
# {{ $title }}

{{ $messageBody }}

@if($actionUrl)
<x-mail::button :url="$actionUrl">
View on Noteds
</x-mail::button>
@endif

Terima kasih,
<br>
{{ config('app.name') }} Team
</x-mail::message>
