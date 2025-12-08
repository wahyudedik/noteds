<?php

namespace App\Services;

use App\Models\AffiliateFraudLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FraudDetectionService
{
    private const FRAUD_CACHE_TTL = 3600; // 1 hour
    private const MAX_CONVERSIONS_PER_MINUTE = 10;
    private const MAX_CONVERSIONS_PER_HOUR = 100;
    private const MAX_CONVERSIONS_PER_DAY = 500;

    /**
     * Deteksi fraud dari affiliate click/conversion
     */
    public function detectAffiliateClickFraud(
        User $affiliate,
        string $ipAddress,
        string $userAgent,
        ?array $metadata = null
    ): array {
        $fraudIndicators = [];
        $riskFactors = [];

        // 1. Check multiple accounts dari same device
        $deviceFingerprint = AffiliateFraudLog::generateDeviceFingerprint($ipAddress, $userAgent);
        $otherAccounts = User::where('device_fingerprint', $deviceFingerprint)
            ->where('id', '!=', $affiliate->id)
            ->count();

        if ($otherAccounts >= 2) {
            $fraudIndicators[] = 'multiple_accounts';
            $riskFactors[] = "Found {$otherAccounts} other accounts from same device";
        }

        // 2. Check rapid conversions
        $recentConversions = AffiliateFraudLog::where('affiliate_id', $affiliate->id)
            ->where('activity_type', 'conversion')
            ->where('created_at', '>=', now()->subMinute())
            ->count();

        if ($recentConversions >= self::MAX_CONVERSIONS_PER_MINUTE) {
            $fraudIndicators[] = 'rapid_conversions';
            $riskFactors[] = "{$recentConversions} conversions in last minute";
        }

        // 3. Check VPN/Proxy
        if ($this->isVpnOrProxy($ipAddress)) {
            $fraudIndicators[] = 'vpn_proxy';
            $riskFactors[] = 'VPN or proxy IP detected';
        }

        // 4. Check impossible location change
        if ($affiliate->last_ip_address && $affiliate->last_ip_address !== $ipAddress) {
            $timeDiff = cache()->get("location_check_{$affiliate->id}");
            if ($timeDiff && $timeDiff < 300) { // Less than 5 minutes
                $fraudIndicators[] = 'impossible_location';
                $riskFactors[] = 'Impossible location change detected';
            }
        }

        // 5. Check conversion rate
        $hourlyConversions = AffiliateFraudLog::where('affiliate_id', $affiliate->id)
            ->where('activity_type', 'conversion')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($hourlyConversions > self::MAX_CONVERSIONS_PER_HOUR) {
            $fraudIndicators[] = 'high_conversion_rate';
            $riskFactors[] = "High conversion rate: {$hourlyConversions}/hour";
        }

        // 6. Check high-value transaction
        if (isset($metadata['amount']) && $metadata['amount'] > 10000000) { // > 10M IDR
            $fraudIndicators[] = 'high_value_transaction';
            $riskFactors[] = "High transaction value: " . number_format($metadata['amount']);
        }

        return [
            'fraud_indicators' => $fraudIndicators,
            'risk_factors' => $riskFactors,
            'is_suspicious' => count($fraudIndicators) >= 2,
        ];
    }

    /**
     * Deteksi fraud dari converter (pembeli)
     */
    public function detectConverterFraud(
        User $converter,
        string $ipAddress,
        string $userAgent,
        array $metadata = []
    ): array {
        $fraudIndicators = [];
        $riskFactors = [];

        // 1. Check multiple accounts dari same device
        $deviceFingerprint = AffiliateFraudLog::generateDeviceFingerprint($ipAddress, $userAgent);
        $otherAccounts = User::where('device_fingerprint', $deviceFingerprint)
            ->where('id', '!=', $converter->id)
            ->count();

        if ($otherAccounts >= 1) {
            $fraudIndicators[] = 'multiple_accounts';
            $riskFactors[] = "Found {$otherAccounts} other accounts from same device";
        }

        // 2. Check new account + high purchase
        if ($converter->created_at->diffInHours(now()) < 24 && isset($metadata['amount']) && $metadata['amount'] > 5000000) {
            $fraudIndicators[] = 'unusual_pattern';
            $riskFactors[] = 'New account with high purchase amount';
        }

        // 3. Check multiple conversions same product dalam short time
        if (isset($metadata['product_id'])) {
            $sameProductConversions = AffiliateFraudLog::where('converter_id', $converter->id)
                ->whereJsonContains('metadata->product_id', $metadata['product_id'])
                ->where('created_at', '>=', now()->subHour())
                ->count();

            if ($sameProductConversions >= 3) {
                $fraudIndicators[] = 'rapid_conversions';
                $riskFactors[] = "Multiple conversions of same product: {$sameProductConversions}";
            }
        }

        // 4. Check payment method changes
        if ($converter->last_ip_address && $converter->last_ip_address !== $ipAddress) {
            $fraudIndicators[] = 'unusual_pattern';
            $riskFactors[] = 'Different IP address from previous transaction';
        }

        return [
            'fraud_indicators' => $fraudIndicators,
            'risk_factors' => $riskFactors,
            'is_suspicious' => count($fraudIndicators) >= 2,
        ];
    }

    /**
     * Check apakah IP menggunakan VPN/Proxy
     */
    private function isVpnOrProxy(string $ipAddress): bool
    {
        // Cache result untuk 24 jam
        $cacheKey = "vpn_check_{$ipAddress}";

        return Cache::remember($cacheKey, 86400, function () use ($ipAddress) {
            try {
                // Gunakan IPQualityScore atau similar service
                // Untuk sekarang, return false (implementasi real bergantung service pilihan)
                return $this->checkVpnService($ipAddress);
            } catch (\Exception) {
                return false;
            }
        });
    }

    /**
     * Check VPN via external service
     */
    private function checkVpnService(string $ipAddress): bool
    {
        // Implementasi dengan layanan eksternal seperti:
        // - IPQualityScore
        // - MaxMind GeoIP2
        // - IP2Proxy

        // Contoh sederhana:
        $vpnIndicators = ['vpn', 'proxy', 'datacenter'];
        // Cek gegen known VPN list atau external API

        return false; // Placeholder
    }

    /**
     * Log activity dan deteksi fraud
     */
    public function logAndDetectFraud(
        ?User $affiliate,
        ?User $converter,
        string $activityType,
        string $ipAddress,
        string $userAgent,
        array $metadata = []
    ): AffiliateFraudLog {
        $fraudIndicators = [];
        $riskFactors = [];

        // Deteksi fraud berdasarkan tipe activity
        if ($affiliate && $activityType === 'click') {
            $detection = $this->detectAffiliateClickFraud($affiliate, $ipAddress, $userAgent, $metadata);
            $fraudIndicators = array_merge($fraudIndicators, $detection['fraud_indicators']);
            $riskFactors = array_merge($riskFactors, $detection['risk_factors']);
        } elseif ($converter && $activityType === 'conversion') {
            $detection = $this->detectConverterFraud($converter, $ipAddress, $userAgent, $metadata);
            $fraudIndicators = array_merge($fraudIndicators, $detection['fraud_indicators']);
            $riskFactors = array_merge($riskFactors, $detection['risk_factors']);
        }

        // Log activity
        $log = AffiliateFraudLog::logActivity(
            affiliate_id: $affiliate?->id,
            converter_id: $converter?->id,
            activityType: $activityType,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            fraudIndicators: $fraudIndicators,
            notes: implode(' | ', $riskFactors),
            metadata: $metadata
        );

        // Update user fraud status jika high risk
        if ($log->is_flagged && $log->risk_score >= 70) {
            $user = $affiliate ?? $converter;
            if ($user) {
                $user->update([
                    'is_fraud_suspected' => true,
                    'fraud_notes' => implode(', ', $riskFactors),
                ]);
            }
        }

        // Update device fingerprint
        if ($affiliate) {
            $affiliate->update([
                'last_ip_address' => $ipAddress,
                'last_user_agent' => $userAgent,
                'device_fingerprint' => AffiliateFraudLog::generateDeviceFingerprint($ipAddress, $userAgent),
            ]);
        }

        return $log;
    }

    /**
     * Get fraud summary untuk admin dashboard
     */
    public function getFraudSummary(): array
    {
        return [
            'flagged_logs_24h' => AffiliateFraudLog::where('is_flagged', true)
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'high_risk_users' => User::where('is_fraud_suspected', true)->count(),
            'fraud_by_type' => AffiliateFraudLog::select('activity_type')
                ->selectRaw('COUNT(*) as count')
                ->where('is_flagged', true)
                ->groupBy('activity_type')
                ->get(),
            'top_fraud_indicators' => AffiliateFraudLog::where('is_flagged', true)
                ->get()
                ->flatMap(fn($log) => $log->fraud_indicators ?? [])
                ->countBy()
                ->sort(),
        ];
    }
}
