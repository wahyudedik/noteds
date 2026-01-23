<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Search Analytics</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 16px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Search Analytics ({{ $dateFrom }} - {{ $dateTo }})</h1>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Query</th>
                <th>Zero Result</th>
                <th>Duration (ms)</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row->user_id }}</td>
                <td>{{ $row->query }}</td>
                <td>{{ $row->zero_result ? 'Yes' : 'No' }}</td>
                <td>{{ $row->duration_ms }}</td>
                <td>{{ $row->created_at }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
