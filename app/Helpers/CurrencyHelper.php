<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Supported currencies with their symbols and formats
     */
    protected static $currencies = [
        'IDR' => [
            'symbol' => 'Rp',
            'name' => 'Indonesian Rupiah',
            'decimal_places' => 0,
            'thousands_separator' => '.',
            'decimal_separator' => ',',
            'locale' => 'id_ID',
        ],
        'USD' => [
            'symbol' => '$',
            'name' => 'US Dollar',
            'decimal_places' => 2,
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            'locale' => 'en_US',
        ],
    ];

    /**
     * Format currency based on user's locale/currency preference
     */
    public static function format(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? self::getDefaultCurrency();

        if (!isset(self::$currencies[$currency])) {
            $currency = 'USD'; // Fallback
        }

        $config = self::$currencies[$currency];

        $formatted = number_format(
            $amount,
            $config['decimal_places'],
            $config['decimal_separator'],
            $config['thousands_separator']
        );

        return $config['symbol'] . ' ' . $formatted;
    }

    /**
     * Get user's default currency (from session or user preference)
     */
    public static function getDefaultCurrency(): string
    {
        // Check if user is authenticated and has currency preference
        if (auth()->check() && auth()->user()->currency) {
            return auth()->user()->currency;
        }

        // Check session
        if (session()->has('currency')) {
            return session('currency');
        }

        // Default based on locale
        $locale = app()->getLocale();

        return match ($locale) {
            'id' => 'IDR',
            'ar' => 'USD', // Arabic countries often use USD
            'en' => 'USD',
            default => 'IDR', // Default to IDR for Indonesia
        };
    }

    /**
     * Get all supported currencies
     */
    public static function getSupportedCurrencies(): array
    {
        return array_keys(self::$currencies);
    }

    /**
     * Get currency details
     */
    public static function getCurrencyInfo(string $currency): ?array
    {
        return self::$currencies[$currency] ?? null;
    }

    /**
     * Convert amount from one currency to another using exchange rates from database
     */
    public static function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        // Get exchange rates from database (admin-configurable)
        $exchangeRate = \App\Models\ExchangeRate::where('from_currency', $from)
            ->where('to_currency', $to)
            ->where('is_active', true)
            ->first();

        if ($exchangeRate) {
            if ($from === $exchangeRate->from_currency) {
                // Direct conversion
                return $amount * $exchangeRate->rate;
            } else {
                // Reverse conversion
                return $amount / $exchangeRate->rate;
            }
        }

        // Fallback: use default rates if no database entry
        $defaultRates = [
            'USD' => ['IDR' => 15000], // 1 USD = 15000 IDR (default, should be updated by admin)
        ];

        if (isset($defaultRates[$from][$to])) {
            return $amount * $defaultRates[$from][$to];
        }

        if (isset($defaultRates[$to][$from])) {
            return $amount / $defaultRates[$to][$from];
        }

        // If no rate found, return original amount
        return $amount;
    }
}
