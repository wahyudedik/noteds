<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body{font-family:Arial,sans-serif;background:#f7fafc;color:#1a202c;margin:0;padding:0}
        .container{max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden}
        .header{background:#1f2937;color:#ffffff;padding:20px}
        .content{padding:20px}
        .btn{display:inline-block;padding:12px 16px;border-radius:6px;text-decoration:none}
        .btn-primary{background:#2563eb;color:#ffffff}
        .btn-danger{background:#ef4444;color:#ffffff}
        .footer{padding:16px;color:#6b7280;font-size:12px;text-align:center}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Undangan Bergabung: {{ $group->name }}</h2>
        </div>
        <div class="content">
            <p>Anda diundang untuk bergabung dengan grup <strong>{{ $group->name }}</strong>.</p>
            <p>Silakan konfirmasi kehadiran Anda melalui tombol berikut:</p>
            <p>
                <a class="btn btn-primary" href="{{ $acceptUrl }}">Terima Undangan</a>
                <a class="btn btn-danger" href="{{ $declineUrl }}">Tolak Undangan</a>
            </p>
            <p>Tambahkan ke kalender Anda dengan melampirkan file invite.ics.</p>
        </div>
        <div class="footer">
            <p>Jika Anda tidak mengenal undangan ini, abaikan email ini.</p>
        </div>
    </div>
    <img src="{{ $openPixel }}" alt="" width="1" height="1" style="display:none;">
</body>
</html>
