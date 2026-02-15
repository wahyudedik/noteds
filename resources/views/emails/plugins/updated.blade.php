<x-mail::message>
# New Version Available!

Hi {{ $user->name }},

A new version of **{{ $plugin->name }}** is now available.

**What's New in v{{ $plugin->version }}:**
- Latest features and bug fixes.
- Performance improvements.

<x-mail::button :url="route('plugins.show', $plugin->id)">
Download Update
</x-mail::button>

Thank you for using our plugin!

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
