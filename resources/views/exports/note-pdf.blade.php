<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $note->title }} - Noteds</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }
        .header {
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        h1 {
            color: #1F2937;
            font-size: 28px;
            margin: 0 0 10px 0;
        }
        .meta {
            color: #6B7280;
            font-size: 14px;
            margin: 10px 0;
        }
        .content {
            margin-top: 30px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 12px;
        }
        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $note->title }}</h1>
        <div class="meta">
            <p><strong>Author:</strong> {{ $note->user->name }}</p>
            <p><strong>Published:</strong> {{ $note->created_at->format('F d, Y') }}</p>
            @if($note->summary)
                <p><strong>Summary:</strong> {{ $note->summary }}</p>
            @endif
        </div>
    </div>
    
    <div class="content">
        {!! $note->content !!}
    </div>
    
    <div class="footer">
        <p>Exported from Noteds - {{ now()->format('F d, Y H:i') }}</p>
        <p>© {{ date('Y') }} Noteds. All rights reserved.</p>
    </div>
</body>
</html>

