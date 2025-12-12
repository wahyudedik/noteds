<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    protected string $baseCurrency;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->baseCurrency = config('currency.base_currency', 'IDR');
        $this->cacheTtl = (int) config('currency.cache_ttl', 300);
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    /**
     * Get default currency for a specific locale
     * Maps language → currency (en→USD, id→IDR, ar→AED)
     * Also returns timezone for that locale
     */
    public function getDefaultCurrencyForLocale(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'en' => 'USD',
            'id' => 'IDR',
            'ar' => 'AED',
            default => $this->baseCurrency,
        };
    }

    /**
     * Get default timezone for a specific locale
     * Maps language → timezone
     */
    public function getDefaultTimezoneForLocale(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'en' => 'UTC',
            'id' => 'Asia/Jakarta',
            'ar' => 'Asia/Riyadh',
            default => 'UTC',
        };
    }

    public function getSupportedCurrencies(): array
    {
        return config('currency.supported_currencies', [$this->baseCurrency]);
    }

    public function getCurrencySymbol(string $currency): string
    {
        $info = \App\Helpers\CurrencyHelper::getCurrencyInfo($currency);

        return $info['symbol'] ?? $currency;
    }

    public function getUserCurrency(?\App\Models\User $user = null): string
    {
        $user ??= auth()->user();

        if ($user && ! empty($user->currency)) {
            return $user->currency;
        }

        if (session()->has('currency')) {
            return session('currency');
        }

        return $this->baseCurrency;
    }

    public function convert(float $amount, string $from, string $to, ?int $precision = null): float
    {
        if ($from === $to) {
            return $this->round($amount, $to, $precision);
        }

        $rate = $this->getRate($from, $to);

        return $this->round($amount * $rate, $to, $precision);
    }

    public function convertToBase(float $amount, string $from, ?int $precision = null): float
    {
        return $this->convert($amount, $from, $this->baseCurrency, $precision);
    }

    public function convertFromBase(float $amount, string $to, ?int $precision = null): float
    {
        return $this->convert($amount, $this->baseCurrency, $to, $precision);
    }

    protected function getRate(string $from, string $to): float
    {
        $key = "currency-rate-{$from}-{$to}";

        return Cache::remember($key, $this->cacheTtl, function () use ($from, $to) {
            if ($from === $to) {
                return 1.0;
            }

            $direct = ExchangeRate::where('from_currency', $from)
                ->where('to_currency', $to)
                ->where('is_active', true)
                ->orderByDesc('updated_at')
                ->first();

            if ($direct) {
                return (float) $direct->rate;
            }

            $inverse = ExchangeRate::where('from_currency', $to)
                ->where('to_currency', $from)
                ->where('is_active', true)
                ->orderByDesc('updated_at')
                ->first();

            if ($inverse && (float) $inverse->rate !== 0.0) {
                return 1 / (float) $inverse->rate;
            }

            $fallbacks = [
                'USD' => ['IDR' => 15500],
                'IDR' => ['USD' => 1 / 15500],
            ];

            if (isset($fallbacks[$from][$to])) {
                return (float) $fallbacks[$from][$to];
            }

            if (isset($fallbacks[$to][$from]) && (float) $fallbacks[$to][$from] !== 0.0) {
                return 1 / (float) $fallbacks[$to][$from];
            }

            return 1.0;
        });
    }

    public function getExchangeRate(string $from, string $to): float
    {
        return $this->getRate($from, $to);
    }
    protected function round(float $amount, string $currency, ?int $precision = null): float
    {
        $precision ??= match ($currency) {
            'IDR' => 0,
            default => 2,
        };

        return round($amount, $precision);
    }

    /**
     * Format amount dengan currency symbol
     */
    public function format(float $amount, string $currency, ?string $locale = null): string
    {
        $symbol = $this->getCurrencySymbol($currency);
        $precision = match ($currency) {
            'IDR' => 0,
            default => 2,
        };

        if (in_array($currency, ['IDR', 'VND', 'JPY', 'PHP', 'THB'])) {
            return $symbol . ' ' . number_format($amount, $precision, ',', '.');
        }

        return $symbol . number_format($amount, $precision, '.', ',');
    }

    /**
     * Set user's preferred currency
     */
    public function setUserCurrency(\App\Models\User $user, string $currency): bool
    {
        if (!in_array($currency, $this->getSupportedCurrencies())) {
            throw new \InvalidArgumentException("Unsupported currency: {$currency}");
        }

        $user->update(['currency' => $currency]);
        Cache::forget("user_currency_{$user->id}");

        return true;
    }

    /**
     * Format untuk API response
     */
    public function formatForApi(float $amount, string $currency): array
    {
        return [
            'amount' => $this->round($amount, $currency),
            'currency' => $currency,
            'symbol' => $this->getCurrencySymbol($currency),
            'formatted' => $this->format($amount, $currency),
        ];
    }

    /**
     * Validate currency code
     */
    public function isValidCurrency(string $currency): bool
    {
        return in_array($currency, $this->getSupportedCurrencies());
    }

    /**
     * Get default currency untuk country
     */
    public function getDefaultCurrencyForCountry(string $countryCode): string
    {
        $currencyMap = [
            'US' => 'USD',
            'GB' => 'GBP',
            'DE' => 'EUR',
            'FR' => 'EUR',
            'ID' => 'IDR',
            'JP' => 'JPY',
            'AU' => 'AUD',
            'CA' => 'CAD',
            'SG' => 'SGD',
            'MY' => 'MYR',
            'TH' => 'THB',
            'PH' => 'PHP',
            'VN' => 'VND',
            'SA' => 'SAR',
            'AE' => 'AED',
        ];

        return $currencyMap[strtoupper($countryCode)] ?? $this->baseCurrency;
    }
}
