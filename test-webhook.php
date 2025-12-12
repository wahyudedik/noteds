<?php

/**
 * Test Midtrans Webhook Signature
 * 
 * Usage:
 * php test-webhook.php <order_id> <status> <amount>
 * 
 * Example:
 * php test-webhook.php topup-1765564392-019b13d6 settlement 10000
 */

require 'vendor/autoload.php';

$orderId = $argv[1] ?? 'topup-test-' . time();
$transactionStatus = $argv[2] ?? 'settlement';
$grossAmount = $argv[3] ?? '10000';
$statusCode = ($transactionStatus === 'settlement' || $transactionStatus === 'capture') ? '200' : '201';
$fraudStatus = 'accept';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get server key
$serverKey = config('services.midtrans.server_key');

if (!$serverKey) {
    die("ERROR: MIDTRANS_SERVER_KEY not configured!\n");
}

// Generate signature exactly as Midtrans does
$inputString = $orderId . $statusCode . $grossAmount . $serverKey;
$signatureKey = hash('sha512', $inputString);

// Build webhook payload
$payload = [
    'transaction_id' => 'a' . time(),
    'order_id' => $orderId,
    'merchant_id' => config('services.midtrans.merchant_id'),
    'gross_amount' => $grossAmount,
    'currency' => 'IDR',
    'payment_type' => 'qris',
    'transaction_time' => now()->toDateTimeString(),
    'transaction_status' => $transactionStatus,
    'fraud_status' => $fraudStatus,
    'status_code' => $statusCode,
    'signature_key' => $signatureKey,
    'bank' => null,
    'va_numbers' => [],
    'paymentz_type' => 'qris',
    'pdf_url' => null,
    'finish_redirect_url' => null,
    'pending_reason' => null,
];

echo "═══════════════════════════════════════════════════════════\n";
echo "🧪 Midtrans Webhook Test\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n📋 Test Parameters:\n";
echo "  Order ID: {$orderId}\n";
echo "  Status: {$transactionStatus}\n";
echo "  Amount: {$grossAmount}\n";
echo "  Fraud Status: {$fraudStatus}\n";
echo "  Status Code: {$statusCode}\n";
echo "\n🔐 Signature Verification:\n";
echo "  Input String: {$orderId}{$statusCode}{$grossAmount}[SERVER_KEY]\n";
echo "  Generated Signature: " . substr($signatureKey, 0, 20) . "...\n";

echo "\n📤 Sending webhook to: https://noteds.com/wallet/webhook\n\n";

// Send webhook
$curlOptions = [
    'method' => 'POST',
    'header' => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    'content' => json_encode($payload),
    'verify_ssl' => false, // For testing only
];

$context = stream_context_create(['http' => $curlOptions]);
$response = @file_get_contents('https://noteds.com/wallet/webhook', false, $context);

if ($response === false) {
    echo "❌ Failed to reach webhook endpoint!\n";
    echo "Check:\n";
    echo "1. HTTPS is working: curl -I https://noteds.com/wallet/webhook\n";
    echo "2. Route is configured: php artisan route:list | grep wallet/webhook\n";
    die("\n");
}

echo "✅ Response:\n";
echo $response . "\n";
echo "\n═══════════════════════════════════════════════════════════\n";

// Check logs
echo "\n📋 Check logs with:\n";
echo "tail -20 storage/logs/laravel.log | grep webhook\n";
echo "tail -20 storage/logs/laravel.log | grep '{$orderId}'\n";
