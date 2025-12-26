<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kontak dari Noteds</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }

        .info-box {
            background: white;
            border-left: 4px solid #6366f1;
            padding: 15px;
            margin: 20px 0;
        }

        .label {
            font-weight: 600;
            color: #6366f1;
            display: inline-block;
            min-width: 100px;
        }

        .message-box {
            background: white;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
            border: 1px solid #e5e7eb;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 24px;">Pesan Kontak Baru</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">dari Noteds Platform</p>
    </div>

    <div class="content">
        <p>Anda menerima pesan kontak baru dari website Noteds:</p>

        <div class="info-box">
            <div style="margin-bottom: 10px;">
                <span class="label">Nama:</span>
                <span>{{ $name }}</span>
            </div>
            <div style="margin-bottom: 10px;">
                <span class="label">Email:</span>
                <span>{{ $email }}</span>
            </div>
            <div>
                <span class="label">Subjek:</span>
                <span>{{ $subject }}</span>
            </div>
        </div>

        <div class="message-box">
            <h3 style="margin-top: 0; color: #6366f1;">Pesan:</h3>
            <p style="white-space: pre-wrap; margin: 0;">{{ $message }}</p>
        </div>

        <div
            style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 6px; border-left: 4px solid #f59e0b;">
            <p style="margin: 0; font-size: 14px;">
                <strong>💡 Tip:</strong> Balas email ini langsung ke <strong>{{ $email }}</strong> untuk merespons
                pengirim.
            </p>
        </div>
    </div>

    <div class="footer">
        <p>Email ini dikirim otomatis dari sistem Noteds.</p>
        <p>&copy; {{ date('Y') }} Noteds. Social Network for Business Thinkers & Doers.</p>
    </div>
</body>

</html>