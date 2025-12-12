<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * Midtrans Webhook Security Service
 * Provides comprehensive validation and protection against spoofed webhooks
 * 
 * IMPORTANT: Signature verification (SHA512) is the PRIMARY security layer
 * IP verification is a secondary defense layer
 */
class MidtransWebhookSecurityService
{
    /**
     * Verify webhook is legitimate from Midtrans (not spoofed)
     * Multiple security checks:
     * 1. Signature verification (SHA512 with server key) - PRIMARY
     * 2. IP whitelist check (optional, can be disabled)
     * 3. Amount validation
     * 4. Order existence check
     */
    public static function verifyWebhook(Request $request, array $notification): void
    {
        // Check 1: Verify signature (CRITICAL - PRIMARY SECURITY)
        self::verifySignature($notification);

        // Check 2: Verify IP (if enabled - SECONDARY DEFENSE)
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
     * NOTE: This is SECONDARY defense. Signature verification is PRIMARY.
     * Can be disabled via config if causing issues with Midtrans webhooks via proxies/CDN
     */
    private static function verifyIP(Request $request): void
    {
        $clientIP = $request->ip();

        // Midtrans official IP ranges (production)
        $midtransIPs = [
            // Midtrans production IPs
            '119.110.75.51',
            '103.58.103.188',
            '103.58.103.189',
            '119.110.75.35',
            // Additional Midtrans IPs (Veritrans gateway)
            '162.158.162.44',
            '162.158.162.45',
            '103.23.100.0',
            // Midtrans API IPs
            '119.110.74.0',
            '119.110.75.0',
        ];

        // For local development, allow localhost
        if (in_array($clientIP, ['127.0.0.1', '::1', 'localhost'])) {
            Log::debug('✅ Webhook from localhost (development/testing)', ['ip' => $clientIP]);
            return;
        }

        // Check exact match
        if (!in_array($clientIP, $midtransIPs)) {
            Log::warning('⚠️ Webhook from IP not in whitelist (but signature verified)', [
                'ip' => $clientIP,
                'note' => 'Signature verification passed. IP check disabled or client using proxy.',
            ]);

            // Only throw if strict IP check enabled (most don't need this)
            if (config('services.midtrans.webhook_strict_ip_check', false)) {
                throw new \Exception("Webhook from unauthorized IP: {$clientIP}");
            }
        } else {
            Log::debug('✅ Webhook IP verified', ['ip' => $clientIP]);
        }
    }

    /**
     * Verify order exists and amount matches (prevents amount tampering)
     * Allows small variance for payment gateway fees (up to 5% or 100,000 IDR)
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

        // Verify amount is within tolerance
        // Allow up to 5% variance or 100,000 IDR (whichever is larger) for gateway fees
        $expectedAmount = (float) $transaction->amount;
        $receivedAmount = (float) $grossAmount;
        $variance = abs($receivedAmount - $expectedAmount);
        $tolerance = max($expectedAmount * 0.05, 100000); // 5% or 100k, whichever is larger

        if ($variance > $tolerance) {
            Log::error('🚨 SECURITY: Amount mismatch exceeds tolerance in webhook', [
                'order_id' => $orderId,
                'expected_amount' => $expectedAmount,
                'webhook_amount' => $receivedAmount,
                'variance' => $variance,
                'tolerance' => $tolerance,
                'ip_address' => request()->ip(),
            ]);

            throw new \Exception(
                "Amount mismatch for {$orderId}. " .
                    "Expected: {$expectedAmount}, Received: {$receivedAmount}, " .
                    "Variance: {$variance} exceeds tolerance of {$tolerance}"
            );
        }

        // Log if there's variance (for auditing)
        if ($variance > 0) {
            Log::info('ℹ️ Amount variance detected (within tolerance)', [
                'order_id' => $orderId,
                'expected_amount' => $expectedAmount,
                'received_amount' => $receivedAmount,
                'variance' => $variance,
                'likely_cause' => 'Payment gateway fee or currency conversion',
            ]);
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
