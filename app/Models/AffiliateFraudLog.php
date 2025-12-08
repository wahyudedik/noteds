<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateFraudLog extends Model
{
    use HasUuids;

    protected $table = 'affiliate_fraud_logs';

    protected $fillable = [
        'affiliate_id',
        'converter_id',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'activity_type',
        'fraud_indicators',
        'risk_score',
        'is_flagged',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'fraud_indicators' => 'json',
        'metadata' => 'json',
        'is_flagged' => 'boolean',
        'risk_score' => 'integer',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function converter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converter_id');
    }

    /**
     * Log suspicious activity with fraud detection
     */
    public static function logActivity(
        ?string $affiliateId,
        ?string $converterId,
        string $activityType,
        string $ipAddress,
        string $userAgent,
        array $fraudIndicators = [],
        ?string $notes = null,
        array $metadata = []
    ): self {
        $riskScore = self::calculateRiskScore($fraudIndicators);

        return self::create([
            'affiliate_id' => $affiliateId,
            'converter_id' => $converterId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_fingerprint' => self::generateDeviceFingerprint($ipAddress, $userAgent),
            'activity_type' => $activityType,
            'fraud_indicators' => $fraudIndicators,
            'risk_score' => $riskScore,
            'is_flagged' => $riskScore >= 60,
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Generate device fingerprint dari IP dan User-Agent
     */
    public static function generateDeviceFingerprint(string $ipAddress, string $userAgent): string
    {
        return hash('sha256', "{$ipAddress}|{$userAgent}");
    }

    /**
     * Hitung risk score dari fraud indicators
     */
    public static function calculateRiskScore(array $fraudIndicators): int
    {
        $score = 0;

        $weights = [
            'multiple_accounts' => 30,
            'impossible_location' => 25,
            'rapid_conversions' => 20,
            'vpn_proxy' => 20,
            'same_device_multiple_users' => 35,
            'high_conversion_rate' => 15,
            'unusual_pattern' => 15,
            'new_device' => 10,
            'high_value_transaction' => 10,
        ];

        foreach ($fraudIndicators as $indicator) {
            $score += $weights[$indicator] ?? 5;
        }

        return min($score, 100); // Cap at 100
    }

    /**
     * Check apakah log ini berindikasi fraud
     */
    public function isFraudulent(): bool
    {
        return $this->is_flagged && $this->risk_score >= 60;
    }

    /**
     * Get fraud indicators description
     */
    public function getFraudDescription(): string
    {
        if (!$this->fraud_indicators) {
            return 'No fraud indicators detected';
        }

        $descriptions = [
            'multiple_accounts' => 'Multiple accounts detected from same device',
            'impossible_location' => 'Impossible location change in short time',
            'rapid_conversions' => 'Rapid conversions detected',
            'vpn_proxy' => 'VPN or proxy usage detected',
            'same_device_multiple_users' => 'Same device used by multiple users',
            'high_conversion_rate' => 'Unusually high conversion rate',
            'unusual_pattern' => 'Unusual activity pattern',
            'new_device' => 'Activity from new device',
            'high_value_transaction' => 'High-value transaction',
        ];

        return collect($this->fraud_indicators)
            ->map(fn($indicator) => $descriptions[$indicator] ?? $indicator)
            ->implode(', ');
    }
}
