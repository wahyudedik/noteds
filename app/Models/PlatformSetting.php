<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "platform_setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $description
     * @return static
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => static::encodeValue($value, $type),
                'type' => $type,
                'description' => $description,
            ]
        );

        // Clear cache
        Cache::forget("platform_setting_{$key}");

        return $setting;
    }

    /**
     * Cast value based on type.
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'number' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => (string) $value,
        };
    }

    /**
     * Encode value based on type.
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected static function encodeValue($value, string $type): string
    {
        return match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    /**
     * Clear cache for a specific key.
     *
     * @param string $key
     * @return void
     */
    public static function clearCache(string $key): void
    {
        Cache::forget("platform_setting_{$key}");
    }

    /**
     * Clear all settings cache.
     *
     * @return void
     */
    public static function clearAllCache(): void
    {
        static::all()->each(function ($setting) {
            Cache::forget("platform_setting_{$setting->key}");
        });
    }
}
