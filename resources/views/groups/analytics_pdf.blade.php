<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:Arial,sans-serif}
        h1{font-size:18px;margin-bottom:10px}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #ddd;padding:8px;font-size:12px}
        th{background:#f3f4f6}
    </style>
</head>
<body>
    <h1>Analytics Grup: {{ $group->name }}</h1>
    <table>
        <thead>
            <tr><th>Event</th><th>RSVP</th><th>Accepted</th></tr>
        </thead>
        <tbody>
        @foreach($events as $e)
            <tr>
                <td>{{ $e->title }}</td>
                <td>{{ \App\Models\GroupEventParticipant::where('event_id',$e->id)->count() }}</td>
                <td>{{ \App\Models\GroupEventParticipant::where('event_id',$e->id)->where('rsvp_status','accepted')->count() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
