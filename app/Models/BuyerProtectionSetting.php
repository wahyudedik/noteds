<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyerProtectionSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'money_back_guarantee_enabled',
        'money_back_guarantee_days',
        'auto_approve_refunds',
        'max_refund_amount',
        'refund_policy_rules',
        'quality_assurance_enabled',
        'quality_check_criteria',
        'dispute_resolution_enabled',
        'dispute_resolution_days',
    ];

    protected function casts(): array
    {
        return [
            'money_back_guarantee_enabled' => 'boolean',
            'money_back_guarantee_days' => 'integer',
            'auto_approve_refunds' => 'boolean',
            'max_refund_amount' => 'decimal:2',
            'refund_policy_rules' => 'array',
            'quality_assurance_enabled' => 'boolean',
            'quality_check_criteria' => 'array',
            'dispute_resolution_enabled' => 'boolean',
            'dispute_resolution_days' => 'integer',
        ];
    }

    /**
     * Get singleton instance
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'money_back_guarantee_enabled' => true,
            'money_back_guarantee_days' => 7,
            'auto_approve_refunds' => false,
            'quality_assurance_enabled' => true,
            'dispute_resolution_enabled' => true,
            'dispute_resolution_days' => 14,
        ]);
    }
}

