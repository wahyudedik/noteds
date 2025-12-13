<?php

/**
 * Production Webhook Diagnostics
 * Untuk check status webhook dan transaction
 */

require __DIR__ . '/vendor/autoload.php';

$env_file = __DIR__ . '/.env';
if (!file_exists($env_file)) {
    die("ERROR: .env file not found\n");
}

// Load env manually
$lines = file($env_file);
$env = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (!$line || strpos($line, '#') === 0) continue;

    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value, '"\'');
    }
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          PRODUCTION WEBHOOK DIAGNOSTICS                        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "📋 CONFIGURATION CHECK:\n";
echo "├─ APP_ENV: " . ($env['APP_ENV'] ?? 'NOT SET') . "\n";
echo "├─ APP_URL: " . ($env['APP_URL'] ?? 'NOT SET') . "\n";
echo "├─ MIDTRANS_SERVER_KEY: " . (isset($env['MIDTRANS_SERVER_KEY']) && !empty($env['MIDTRANS_SERVER_KEY']) ? '✅ SET' : '❌ NOT SET') . "\n";
echo "├─ MIDTRANS_IS_PRODUCTION: " . ($env['MIDTRANS_IS_PRODUCTION'] ?? 'NOT SET') . "\n";
echo "└─ LOG_CHANNEL: " . ($env['LOG_CHANNEL'] ?? 'NOT SET') . "\n\n";

echo "📝 WEBHOOK ENDPOINT:\n";
echo "├─ URL: " . ($env['APP_URL'] ?? 'NOT SET') . "/wallet/webhook\n";
echo "├─ Method: POST\n";
echo "├─ Expected Header: Content-Type: application/json\n";
echo "└─ Authentication: SHA512 Signature (signature_key)\n\n";

echo "🔍 DATABASE STATUS:\n";
// Try to count pending transactions
if (function_exists('mysqli_connect')) {
    $host = $env['DB_HOST'] ?? 'localhost';
    $user = $env['DB_USERNAME'] ?? 'root';
    $pass = $env['DB_PASSWORD'] ?? '';
    $db = $env['DB_DATABASE'] ?? '';

    $conn = @mysqli_connect($host, $user, $pass, $db);
    if ($conn) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM transactions WHERE status = 'pending'");
        $row = mysqli_fetch_assoc($result);
        echo "├─ Pending Transactions: " . ($row['cnt'] ?? 0) . "\n";

        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM transactions WHERE status = 'settlement'");
        $row = mysqli_fetch_assoc($result);
        echo "├─ Settlement Transactions: " . ($row['cnt'] ?? 0) . "\n";

        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM transactions WHERE user_id = 71");
        $row = mysqli_fetch_assoc($result);
        echo "└─ User 71 Transactions: " . ($row['cnt'] ?? 0) . "\n";

        mysqli_close($conn);
    } else {
        echo "└─ ❌ Database connection failed\n";
    }
} else {
    echo "└─ ⚠️  Cannot check (mysqli not available)\n";
}

echo "\n📊 RECENT LOG ACTIVITY:\n";
$logfile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logfile)) {
    echo "├─ Log File: EXISTS\n";
    $size = filesize($logfile);
    echo "├─ Size: " . ($size > 1024 * 1024 ? round($size / (1024 * 1024), 2) . ' MB' : round($size / 1024, 2) . ' KB') . "\n";

    $lines = array_slice(explode("\n", file_get_contents($logfile)), -20);
    echo "├─ Last 5 ERROR/WARNING lines:\n";
    $count = 0;
    foreach (array_reverse($lines) as $line) {
        if (strpos($line, 'ERROR') !== false || strpos($line, 'WARNING') !== false) {
            echo "│  └─ " . substr($line, 0, 100) . "...\n";
            $count++;
            if ($count >= 5) break;
        }
    }
    echo "└─ Last activity: " . end($lines) . "\n";
} else {
    echo "└─ ⚠️  Log file not found\n";
}

echo "\n🚀 NEXT STEPS TO FIX:\n";
echo "1. Verify Midtrans Server Key is set in .env\n";
echo "2. Test webhook endpoint: curl -X POST https://noteds.com/wallet/webhook ...\n";
echo "3. Check Midtrans Dashboard → Settings → Webhook for delivery status\n";
echo "4. Run: php artisan midtrans:sync-status --all (manual sync)\n";
echo "5. Check logs: tail -f storage/logs/laravel.log | grep webhook\n";
echo "\n";
