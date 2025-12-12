<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class LocaleService
{
    private const SUPPORTED_LOCALES = [
        'en' => ['name' => 'English', 'flag' => '🇺🇸'],
        'id' => ['name' => 'Indonesian', 'flag' => '🇮🇩'],
        'ar' => ['name' => 'Arabic', 'flag' => '🇸🇦'],
    ];

    private const SUPPORTED_TIMEZONES = [
        'UTC' => 'UTC',
        'America/New_York' => 'Eastern Time',
        'America/Chicago' => 'Central Time',
        'America/Denver' => 'Mountain Time',
        'America/Los_Angeles' => 'Pacific Time',
        'Europe/London' => 'GMT',
        'Europe/Paris' => 'Central European Time',
        'Asia/Jakarta' => 'Western Indonesian Time',
        'Asia/Bangkok' => 'Indochina Time',
        'Asia/Singapore' => 'Singapore Time',
        'Asia/Tokyo' => 'Japan Standard Time',
        'Asia/Dubai' => 'Gulf Standard Time',
        'Australia/Sydney' => 'Australian Eastern Time',
    ];

    /**
     * Get user's locale
     */
    public function getUserLocale(?User $user = null): string
    {
        if (!$user) {
            return config('app.locale', 'en');
        }

        return Cache::remember("user_locale_{$user->id}", 3600, function () use ($user) {
            return $user->locale ?? config('app.locale', 'en');
        });
    }

    /**
     * Set user's locale
     */
    public function setUserLocale(User $user, string $locale): bool
    {
        if (!isset(self::SUPPORTED_LOCALES[$locale])) {
            throw new \InvalidArgumentException("Unsupported locale: {$locale}");
        }

        $user->update(['locale' => $locale]);
        Cache::forget("user_locale_{$user->id}");
        app()->setLocale($locale);

        return true;
    }

    /**
     * Get user's timezone
     */
    public function getUserTimezone(?User $user = null): string
    {
        if (!$user) {
            return config('app.timezone', 'UTC');
        }

        return Cache::remember("user_timezone_{$user->id}", 3600, function () use ($user) {
            return $user->timezone ?? config('app.timezone', 'UTC');
        });
    }

    /**
     * Set user's timezone
     */
    public function setUserTimezone(User $user, string $timezone): bool
    {
        if (!isset(self::SUPPORTED_TIMEZONES[$timezone])) {
            throw new \InvalidArgumentException("Unsupported timezone: {$timezone}");
        }

        $user->update(['timezone' => $timezone]);
        Cache::forget("user_timezone_{$user->id}");

        return true;
    }

    /**
     * Get all supported locales
     */
    public function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    /**
     * Get all supported timezones
     */
    public function getSupportedTimezones(): array
    {
        return self::SUPPORTED_TIMEZONES;
    }

    /**
     * Get all user locale settings
     */
    public function getUserSettings(?User $user = null): array
    {
        if (!$user) {
            return [
                'locale' => config('app.locale', 'en'),
                'timezone' => config('app.timezone', 'UTC'),
            ];
        }

        return [
            'locale' => $this->getUserLocale($user),
            'timezone' => $this->getUserTimezone($user),
            'locale_name' => self::SUPPORTED_LOCALES[$user->locale ?? 'en']['name'] ?? 'English',
            'timezone_name' => self::SUPPORTED_TIMEZONES[$user->timezone ?? 'UTC'] ?? 'UTC',
        ];
    }

    /**
     * Set user locale settings
     */
    public function setUserSettings(User $user, array $settings): bool
    {
        if (isset($settings['locale'])) {
            $this->setUserLocale($user, $settings['locale']);
        }

        if (isset($settings['timezone'])) {
            $this->setUserTimezone($user, $settings['timezone']);
        }

        return true;
    }

    /**
     * Format date berdasarkan user's locale dan timezone
     */
    public function formatDate(?\DateTime $date, ?User $user = null, string $format = 'Y-m-d H:i:s'): string
    {
        if (!$date) {
            return '';
        }

        $timezone = $this->getUserTimezone($user);
        $locale = $this->getUserLocale($user);

        $date->setTimezone(new \DateTimeZone($timezone));

        if ($locale === 'id') {
            // Indonesian locale
            $months = [
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember'
            ];
            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            $month = $months[$date->format('n') - 1];
            $day = $days[$date->format('w')];

            return sprintf('%s, %d %s %s %s', $day, $date->format('d'), $month, $date->format('Y'), $date->format('H:i:s'));
        } elseif ($locale === 'ar') {
            // Arabic locale
            return $this->formatArabicDate($date);
        }

        return $date->format($format);
    }

    /**
     * Format date untuk Arabic locale
     */
    private function formatArabicDate(\DateTime $date): string
    {
        $months = [
            'يناير',
            'فبراير',
            'مارس',
            'أبريل',
            'مايو',
            'يونيو',
            'يوليو',
            'أغسطس',
            'سبتمبر',
            'أكتوبر',
            'نوفمبر',
            'ديسمبر'
        ];
        $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

        $month = $months[$date->format('n') - 1];
        $day = $days[$date->format('w')];

        return sprintf('%s، %d %s %s %s', $day, $date->format('d'), $month, $date->format('Y'), $date->format('H:i:s'));
    }

    /**
     * Get user's locale preferences dengan merge dari default config
     */
    public function getFullSettings(?User $user = null): array
    {
        $locale = $user?->locale ?? config('app.locale', 'en');

        // Get default currency and timezone based on locale
        $currencyService = app(CurrencyService::class);
        $defaultCurrency = $currencyService->getDefaultCurrencyForLocale($locale);
        $defaultTimezone = $currencyService->getDefaultTimezoneForLocale($locale);

        return array_merge(
            [
                'locale' => config('app.locale', 'en'),
                'timezone' => config('app.timezone', 'UTC'),
                'currency' => $defaultCurrency,
            ],
            $user ? [
                'locale' => $user->locale ?? config('app.locale', 'en'),
                'timezone' => $user->timezone ?? $defaultTimezone,
                'currency' => $user->currency ?? $defaultCurrency,
            ] : []
        );
    }

    /**
     * Validate locale code
     */
    public function isValidLocale(string $locale): bool
    {
        return isset(self::SUPPORTED_LOCALES[$locale]);
    }

    /**
     * Validate timezone code
     */
    public function isValidTimezone(string $timezone): bool
    {
        return isset(self::SUPPORTED_TIMEZONES[$timezone]);
    }
}
