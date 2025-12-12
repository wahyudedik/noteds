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
        'AED' => [
            'symbol' => 'د.إ',
            'name' => 'United Arab Emirates Dirham',
            'decimal_places' => 2,
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            'locale' => 'ar_AE',
        ],
        'SAR' => [
            'symbol' => '﷼',
            'name' => 'Saudi Riyal',
            'decimal_places' => 2,
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            'locale' => 'ar_SA',
        ],
    ];

    /**
     * Format currency based on user's locale/currency preference
     */
    public static function format(float $amount, ?string $currency = null, ?string $fromCurrency = null): string
    {
        $currencyService = app(\App\Services\CurrencyService::class);
        $targetCurrency = $currency ?? self::getDefaultCurrency();
        $sourceCurrency = $fromCurrency ?? config('currency.base_currency', 'IDR');

        if (!isset(self::$currencies[$targetCurrency])) {
            $targetCurrency = 'USD'; // Fallback
        }

        if ($sourceCurrency !== $targetCurrency) {
            $amount = $currencyService->convert($amount, $sourceCurrency, $targetCurrency);
        }

        $config = self::$currencies[$targetCurrency];

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
     * Uses CurrencyService for consistent locale-to-currency mapping
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

        // Use CurrencyService for locale-based defaults
        $currencyService = app(\App\Services\CurrencyService::class);
        return $currencyService->getDefaultCurrencyForLocale();
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

        // Fallback: use updated default rates if no database entry
        $defaultRates = [
            'USD' => ['IDR' => 15500, 'AED' => 3.67, 'SAR' => 3.75],
            'IDR' => ['USD' => 1 / 15500, 'AED' => 3.67 / 15500, 'SAR' => 3.75 / 15500],
            'AED' => ['USD' => 1 / 3.67, 'IDR' => 15500 / 3.67, 'SAR' => 3.75 / 3.67],
            'SAR' => ['USD' => 1 / 3.75, 'IDR' => 15500 / 3.75, 'AED' => 3.67 / 3.75],
        ];

        if (isset($defaultRates[$from][$to])) {
            return $amount * $defaultRates[$from][$to];
        }

        // If no rate found, return original amount
        return $amount;
    }
}
