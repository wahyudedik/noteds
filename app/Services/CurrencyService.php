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
                'USD' => ['IDR' => 15000],
                'IDR' => ['USD' => 1 / 15000],
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

    protected function round(float $amount, string $currency, ?int $precision = null): float
    {
        $precision ??= match ($currency) {
            'IDR' => 0,
            default => 2,
        };

        return round($amount, $precision);
    }
}

