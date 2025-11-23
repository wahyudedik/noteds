<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Note from {{ $seller->name }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">📚 {{ config('app.name', 'Noteds') }}</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 24px;">
            New Note from {{ $seller->name }}!
        </h2>
        
        <p style="font-size: 16px; color: #374151;">
            Hello {{ $follower->name }},
        </p>
        
        <p style="font-size: 16px; color: #374151;">
            <strong>{{ $seller->name }}</strong> just published a new note that you might be interested in:
        </p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
            <h3 style="color: #1f2937; margin-top: 0; font-size: 18px;">{{ $note->title }}</h3>
            @if($note->summary)
                <p style="color: #6b7280; margin: 10px 0;">{{ Str::limit(strip_tags($note->summary), 200) }}</p>
            @endif
            <p style="color: #059669; font-weight: bold; margin: 10px 0 0 0;">
                Price: {{ currency($note->price) }}
            </p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $noteUrl }}" 
               style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                View Note
            </a>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            You're receiving this because you're following {{ $seller->name }}. 
            <a href="{{ route('profile.edit') }}" style="color: #667eea;">Manage your email preferences</a>.
        </p>
        
        <p style="color: #6b7280; font-size: 12px; margin-top: 20px; text-align: center;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>
</body>
</html>

