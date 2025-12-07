<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderboardSetting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'description',
        'category',
    ];

    protected $table = 'leaderboard_settings';

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        // Cast value based on type
        return match ($setting->type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'boolean' => (bool) $value,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    /**
     * Set a setting value by key
     */
    public static function set($key, $value, $label = null, $type = 'int', $category = 'leaderboard')
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'label' => $label ?? $key,
                'value' => $value,
                'type' => $type,
                'category' => $category,
            ]
        );
    }

    /**
     * Get all settings grouped by category
     */
    public static function getByCategory($category)
    {
        return static::where('category', $category)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });
    }
}
