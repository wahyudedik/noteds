<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PointsSystemConfig extends Model
{
    use HasUuids;

    protected $table = 'points_system_config';
    protected $connection = 'mysql';

    protected $fillable = [
        'key',
        'value',
        'type', // string, integer, decimal, boolean, json
        'description',
        'is_active',
        'category', // earning, redemption, marketplace, fraud_prevention
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get config value by key
     */
    public static function getValue($key, $default = null)
    {
        $config = static::where('key', $key)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return $default;
        }

        // Cast value based on type
        return match ($config->type) {
            'integer' => (int)$config->value,
            'decimal' => (float)$config->value,
            'boolean' => (bool)$config->value,
            'json' => json_decode($config->value, true),
            default => $config->value,
        };
    }

    /**
     * Set config value
     */
    public static function setValue($key, $value, $type = 'string', $category = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string)$value,
                'type' => $type,
                'category' => $category,
                'is_active' => true,
            ],
        );
    }

    /**
     * Get all earning rules
     */
    public static function getEarningRules()
    {
        return static::where('category', 'earning')
            ->where('is_active', true)
            ->pluck('value', 'key');
    }

    /**
     * Get all redemption rules
     */
    public static function getRedemptionRules()
    {
        return static::where('category', 'redemption')
            ->where('is_active', true)
            ->pluck('value', 'key');
    }

    /**
     * Get all marketplace rules
     */
    public static function getMarketplaceRules()
    {
        return static::where('category', 'marketplace')
            ->where('is_active', true)
            ->pluck('value', 'key');
    }
}
