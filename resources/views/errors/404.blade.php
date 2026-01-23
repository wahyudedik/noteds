<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background: #0f172a; color: #e5e7eb; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .card { background:#1f2937; border:1px solid #374151; border-radius:12px; padding:24px; max-width:520px; box-shadow: 0 8px 24px rgba(0,0,0,0.25); }
        h1 { margin:0 0 8px; font-size:20px; }
        p { margin:0 0 12px; font-size:14px; color:#cbd5e1; }
        a { color:#93c5fd; text-decoration:none; }
        a:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div class="card">
        <h1>404 Not Found</h1>
        <p>Halaman yang Anda akses tidak ditemukan.</p>
        <p><a href="{{ url('/') }}">Kembali ke beranda</a></p>
    </div>
</body>
</html>
