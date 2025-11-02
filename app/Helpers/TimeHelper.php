<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimeHelper
{
    /**
     * Format date/time based on user's locale and timezone
     */
    public static function format($datetime, string $format = 'default', ?string $timezone = null): string
    {
        if (!$datetime) {
            return '-';
        }

        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        
        // Set timezone
        $timezone = $timezone ?? self::getUserTimezone();
        $carbon = $carbon->setTimezone($timezone);

        $locale = app()->getLocale();

        // Format based on locale
        return match($format) {
            'default' => $carbon->isoFormat('LL'),
            'datetime' => $carbon->isoFormat('LLLL'),
            'date' => $carbon->isoFormat('LL'),
            'time' => $carbon->isoFormat('LT'),
            'short' => $carbon->isoFormat('L'),
            'human' => self::diffForHumans($carbon, $locale),
            default => $carbon->format($format),
        };
    }

    /**
     * Get human-readable time difference (localized)
     */
    public static function diffForHumans($datetime, ?string $locale = null): string
    {
        if (!$datetime) {
            return '-';
        }

        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        $locale = $locale ?? app()->getLocale();
        
        // Set locale for Carbon
        Carbon::setLocale($locale);
        
        return $carbon->diffForHumans();
    }

    /**
     * Get user's timezone (from session or user preference)
     */
    public static function getUserTimezone(): string
    {
        // Check if user is authenticated and has timezone preference
        if (auth()->check() && auth()->user()->timezone) {
            return auth()->user()->timezone;
        }

        // Check session
        if (session()->has('timezone')) {
            return session('timezone');
        }

        // Default based on locale
        $locale = app()->getLocale();
        
        return match($locale) {
            'id' => 'Asia/Jakarta',
            'ar' => 'Asia/Riyadh', // Default Arabic timezone
            'en' => 'UTC',
            default => 'UTC',
        };
    }

    /**
     * Get available timezones grouped by region
     */
    public static function getTimezonesByRegion(): array
    {
        $timezones = [];
        $regions = [
            'Asia' => [
                'Asia/Jakarta' => 'Jakarta (WIB)',
                'Asia/Makassar' => 'Makassar (WITA)',
                'Asia/Jayapura' => 'Jayapura (WIT)',
                'Asia/Singapore' => 'Singapore',
                'Asia/Kuala_Lumpur' => 'Kuala Lumpur',
                'Asia/Bangkok' => 'Bangkok',
                'Asia/Manila' => 'Manila',
                'Asia/Ho_Chi_Minh' => 'Ho Chi Minh',
                'Asia/Hong_Kong' => 'Hong Kong',
                'Asia/Tokyo' => 'Tokyo',
                'Asia/Seoul' => 'Seoul',
                'Asia/Shanghai' => 'Shanghai',
                'Asia/Dubai' => 'Dubai',
            ],
            'Europe' => [
                'Europe/London' => 'London',
                'Europe/Paris' => 'Paris',
                'Europe/Berlin' => 'Berlin',
                'Europe/Rome' => 'Rome',
                'Europe/Madrid' => 'Madrid',
            ],
            'America' => [
                'America/New_York' => 'New York',
                'America/Chicago' => 'Chicago',
                'America/Denver' => 'Denver',
                'America/Los_Angeles' => 'Los Angeles',
                'America/Sao_Paulo' => 'São Paulo',
            ],
            'Pacific' => [
                'Pacific/Auckland' => 'Auckland',
                'Australia/Sydney' => 'Sydney',
                'Australia/Melbourne' => 'Melbourne',
            ],
            'Other' => [
                'UTC' => 'UTC',
            ],
        ];

        return $regions;
    }
}

