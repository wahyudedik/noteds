<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('You Received a Gift Note!') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🎁 {{ config('app.name', 'Noteds') }}</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 24px;">
            {{ __('You Received a Gift Note!') }}
        </h2>
        
        <p style="font-size: 16px; color: #374151;">
            {{ __('Hello') }},
        </p>
        
        <p style="font-size: 16px; color: #374151;">
            <strong>{{ $gifter->name }}</strong> {{ __('has sent you a gift note!') }}
        </p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
            <h3 style="color: #1f2937; margin-top: 0; font-size: 18px;">{{ $note->title }}</h3>
            @if($note->summary)
                <p style="color: #6b7280; margin: 10px 0;">{{ Str::limit(strip_tags($note->summary), 200) }}</p>
            @endif
            <p style="color: #059669; font-weight: bold; margin: 10px 0 0 0;">
                {{ __('Price') }}: {{ currency($note->price) }}
            </p>
        </div>
        
        @if($giftNote->message)
            <div style="background: #fef3c7; padding: 20px; border-radius: 8px; border: 1px solid #fbbf24; margin: 20px 0;">
                <p style="margin: 0; font-size: 15px; color: #92400e; font-style: italic;">
                    "{{ $giftNote->message }}"
                </p>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #92400e;">
                    — {{ $gifter->name }}
                </p>
            </div>
        @endif
        
        <p style="font-size: 16px; color: #374151;">
            {{ __('Click the button below to claim your gift note. The note will be added to your library once claimed.') }}
        </p>
        
        @if($giftNote->expires_at)
            <p style="font-size: 14px; color: #dc2626; margin: 10px 0;">
                ⏰ {{ __('This gift expires on') }} {{ $giftNote->expires_at->format('F d, Y') }}.
            </p>
        @endif
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $claimUrl }}" 
               style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('Claim Your Gift Note') }} 🎁
            </a>
        </div>
        
        <div style="background: #e0e7ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #3730a3;">
                💡 <strong>{{ __('Tip') }}:</strong> {{ __('You must have an account on') }} {{ config('app.name') }} {{ __('to claim this gift. If you don\'t have an account yet, you can create one for free!') }}
            </p>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            {{ __('If you have any questions, please contact our support team.') }}
        </p>
        
        <p style="color: #6b7280; font-size: 12px; margin-top: 20px; text-align: center;">
            © {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
        </p>
    </div>
</body>
</html>

