<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $sequence->subject }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">📚 {{ config('app.name', 'Noteds') }}</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 24px;">
            {{ $sequence->subject }}
        </h2>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            {!! nl2br(e($content)) !!}
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('marketplace.index') }}" 
               style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                Visit Marketplace
            </a>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            <a href="{{ route('profile.edit') }}" style="color: #667eea;">Manage your email preferences</a>.
        </p>
        
        <p style="color: #6b7280; font-size: 12px; margin-top: 20px; text-align: center;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>
</body>
</html>

