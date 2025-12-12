<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * Midtrans Webhook Security Service
 * Provides comprehensive validation and protection against spoofed webhooks
 */
class MidtransWebhookSecurityService
{
    /**
     * Verify webhook is legitimate from Midtrans (not spoofed)
     * Multiple security checks:
     * 1. Signature verification (SHA512 with server key)
     * 2. IP whitelist check (Midtrans IP ranges)
     * 3. Amount validation
     * 4. Order existence check
     */
    public static function verifyWebhook(Request $request, array $notification): void
    {
        // Check 1: Verify signature (CRITICAL)
        self::verifySignature($notification);

        // Check 2: Verify IP (if enabled)
        if (config('services.midtrans.webhook_ip_check', true)) {
            self::verifyIP($request);
        }

        // Check 3: Verify order exists and amount matches
        self::verifyOrderAmount($notification);

        Log::info('✅ Webhook security verification passed', [
            'order_id' => $notification['order_id'] ?? null,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Verify Midtrans webhook signature to prevent spoofed callbacks
     * ⚠️ CRITICAL SECURITY: This prevents attackers from faking payment confirmations
     * Uses timing-safe comparison to prevent timing attacks
     */
    private static function verifySignature(array $notification): void
    {
        $orderId = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? null;
        $serverKey = config('services.midtrans.server_key');
        $signatureKey = $notification['signature_key'] ?? null;

        if (!$signatureKey) {
            throw new \Exception('Missing signature_key in webhook payload - possible spoofed request');
        }

        if (!$serverKey) {
            throw new \Exception('Midtrans Server Key not configured');
        }

        if (!$orderId || !$statusCode || $grossAmount === null) {
            throw new \Exception('Missing critical fields in webhook payload');
        }

        // Reconstruct signature exactly as Midtrans does
        // Formula: SHA512(order_id + status_code + gross_amount + server_key)
        $inputString = $orderId . $statusCode . $grossAmount . $serverKey;
        $computedSignature = hash('sha512', $inputString);

        // Use timing-safe comparison to prevent timing attacks
        if (!hash_equals($computedSignature, $signatureKey)) {
            Log::error('🚨 SECURITY: Midtrans Signature Verification FAILED', [
                'order_id' => $orderId,
                'provided_signature' => substr($signatureKey, 0, 10) . '...',
                'computed_signature' => substr($computedSignature, 0, 10) . '...',
                'ip_address' => request()->ip(),
                'timestamp' => now()->toIso8601String(),
            ]);

            throw new \Exception('Invalid Midtrans signature. Webhook rejected as potential spoofed request.');
        }

        Log::debug('✅ Midtrans signature verified', [
            'order_id' => $orderId,
        ]);
    }

    /**
     * Verify webhook source IP is from Midtrans
     * Midtrans uses specific IP ranges for webhook delivery
     * Reference: https://midtrans.com/en/technical-reference/ip-addresses
     */
    private static function verifyIP(Request $request): void
    {
        $clientIP = $request->ip();

        // Midtrans IP ranges (production and sandbox)
        $midtransIPs = [
            // Midtrans production IP ranges
            '119.110.75.51',
            '103.58.103.188',
            '103.58.103.189',
            '119.110.75.35',
            // Midtrans sandbox can come from various IPs during testing
        ];

        // For local development or sandbox, allow localhost
        if (in_array($clientIP, ['127.0.0.1', '::1', 'localhost'])) {
            Log::debug('✅ Webhook from localhost (development/testing)', [
                'ip' => $clientIP,
            ]);
            return;
        }

        // Check if IP is in allowed ranges
        $isValidIP = false;
        foreach ($midtransIPs as $midtransIP) {
            if ($clientIP === $midtransIP) {
                $isValidIP = true;
                break;
            }
        }

        if (!$isValidIP) {
            Log::warning('⚠️ Webhook from unexpected IP address', [
                'ip' => $clientIP,
                'allowed_ips' => $midtransIPs,
            ]);

            // In production, this should throw exception
            if (config('app.env') === 'production') {
                throw new \Exception("Webhook from unauthorized IP: {$clientIP}");
            }
        } else {
            Log::debug('✅ Webhook IP verified', ['ip' => $clientIP]);
        }
    }

    /**
     * Verify order exists and amount matches (prevents amount tampering)
     */
    private static function verifyOrderAmount(array $notification): void
    {
        $orderId = $notification['order_id'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? null;

        if (!$orderId) {
            throw new \Exception('Missing order_id in webhook');
        }

        // Check if transaction exists
        $transaction = \App\Models\Transaction::where('midtrans_order_id', $orderId)->first();

        if (!$transaction) {
            Log::warning('⚠️ Webhook for non-existent transaction', [
                'order_id' => $orderId,
            ]);
            throw new \Exception("Transaction not found: {$orderId}");
        }

        // Verify amount matches (prevent amount tampering)
        if ((float) $grossAmount !== (float) $transaction->amount) {
            Log::error('🚨 SECURITY: Amount mismatch in webhook', [
                'order_id' => $orderId,
                'expected_amount' => $transaction->amount,
                'webhook_amount' => $grossAmount,
                'ip_address' => request()->ip(),
            ]);

            throw new \Exception(
                "Amount mismatch for {$orderId}. " .
                "Expected: {$transaction->amount}, Received: {$grossAmount}"
            );
        }

        Log::debug('✅ Order amount verified', [
            'order_id' => $orderId,
            'amount' => $grossAmount,
        ]);
    }

    /**
     * Rate limit webhook processing per order_id
     * Prevents rapid-fire exploit attempts
     */
    public static function checkRateLimit(string $orderId): void
    {
        $cacheKey = "webhook_process_{$orderId}";
        $maxAttempts = 5; // Max 5 attempts per hour
        $timeWindow = 3600; // 1 hour

        $attempts = \Illuminate\Support\Facades\Cache::get($cacheKey, 0);

        if ($attempts >= $maxAttempts) {
            Log::warning('⚠️ Webhook rate limit exceeded', [
                'order_id' => $orderId,
                'attempts' => $attempts,
            ]);

            throw new \Exception(
                "Webhook rate limit exceeded for {$orderId}. " .
                "Please try again in an hour."
            );
        }

        \Illuminate\Support\Facades\Cache::put($cacheKey, $attempts + 1, $timeWindow);
    }

    /**
     * Get security audit log for webhook
     * Helps debug security issues
     */
    public static function auditLog(Request $request, array $notification, $status = 'success'): array
    {
        return [
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'order_id' => $notification['order_id'] ?? null,
            'transaction_status' => $notification['transaction_status'] ?? null,
            'fraud_status' => $notification['fraud_status'] ?? null,
            'gross_amount' => $notification['gross_amount'] ?? null,
            'client_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'headers' => [
                'content_type' => $request->header('content-type'),
                'accept' => $request->header('accept'),
            ],
        ];
    }
}
