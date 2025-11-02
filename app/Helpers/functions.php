<?php

use App\Helpers\CurrencyHelper;
use App\Helpers\TimeHelper;

if (!function_exists('currency')) {
    /**
     * Format currency helper
     */
    function currency(float $amount, ?string $currency = null): string
    {
        return CurrencyHelper::format($amount, $currency);
    }
}

if (!function_exists('localized_time')) {
    /**
     * Format time helper
     */
    function localized_time($datetime, string $format = 'default', ?string $timezone = null): string
    {
        return TimeHelper::format($datetime, $format, $timezone);
    }
}

if (!function_exists('localized_diff_for_humans')) {
    /**
     * Human-readable time difference helper
     */
    function localized_diff_for_humans($datetime, ?string $locale = null): string
    {
        return TimeHelper::diffForHumans($datetime, $locale);
    }
}

