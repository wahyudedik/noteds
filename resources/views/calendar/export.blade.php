<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Calendar Export</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 16px; margin-bottom: 6px; }
        .meta { margin-bottom: 10px; color: #555; }
        .event { border: 1px solid #ccc; padding: 6px; margin-bottom: 6px; border-radius: 4px; }
        .title { font-weight: bold; }
        .row { display: flex; gap: 10px; }
        .badge { display: inline-block; background: #eef; padding: 2px 6px; border-radius: 3px; margin-right: 4px; }
    </style>
</head>
<body>
    <h1>Calendar Export</h1>
    <div class="meta">
        User: {{ $user->name }}<br>
        Range: {{ $params['from'] }} — {{ $params['to'] }}<br>
        View: {{ strtoupper($params['view']) }}
    </div>
    @foreach($events as $e)
        <div class="event">
            <div class="title">{{ $e->title }}</div>
            <div>{{ $e->start_at }} — {{ $e->end_at }}</div>
            @if($e->location)
                <div>{{ $e->location }}</div>
            @endif
            @if($e->is_virtual && $e->meeting_url)
                <div>Virtual: {{ $e->meeting_url }}</div>
            @endif
            <div>Status: {{ $e->status }}</div>
            <div>
                @foreach($e->categories as $c)
                    <span class="badge">{{ $c->name }}</span>
                @endforeach
            </div>
        </div>
    @endforeach
</body>
</html>
