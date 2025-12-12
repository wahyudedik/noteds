<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use App\Services\LocaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    protected CurrencyService $currencyService;
    protected LocaleService $localeService;

    public function __construct(CurrencyService $currencyService, LocaleService $localeService)
    {
        $this->currencyService = $currencyService;
        $this->localeService = $localeService;
    }

    /**
     * Switch application locale
     * Also auto-syncs currency and timezone with locale selection
     */
    public function switchLocale(string $locale)
    {
        $supportedLocales = ['en', 'id', 'ar'];

        if (!in_array($locale, $supportedLocales)) {
            return redirect()->back()->with('error', 'Unsupported locale');
        }

        // Set locale globally
        App::setLocale($locale);
        Session::put('locale', $locale);

        if (auth()->check()) {
            $user = auth()->user();

            // Auto-sync currency based on locale
            $defaultCurrency = $this->currencyService->getDefaultCurrencyForLocale($locale);
            if ($user->currency !== $defaultCurrency) {
                $user->update(['currency' => $defaultCurrency]);
                Session::put('currency', $defaultCurrency);
                // Clear currency-related cache entries
                Cache::forget('user_locale_' . $user->id);
                Cache::forget('user_currency_' . $user->id);
            }

            // Auto-sync timezone based on locale
            $defaultTimezone = $this->currencyService->getDefaultTimezoneForLocale($locale);
            if ($user->timezone !== $defaultTimezone) {
                $user->update(['timezone' => $defaultTimezone]);
                Session::put('timezone', $defaultTimezone);
            }
        } else {
            // For guests, just update session
            Session::put('currency', $this->currencyService->getDefaultCurrencyForLocale($locale));
            Session::put('timezone', $this->currencyService->getDefaultTimezoneForLocale($locale));
        }

        return redirect()->back()->with('success', __('messages.locale_changed', [], $locale));
    }

    /**
     * Set user currency preference
     */
    public function setCurrency(Request $request)
    {
        $currency = $request->input('currency');
        $supported = ['IDR', 'USD', 'AED', 'SAR'];

        if (!in_array($currency, $supported)) {
            return redirect()->back()->with('error', 'Unsupported currency');
        }

        Session::put('currency', $currency);

        if (auth()->check()) {
            auth()->user()->update(['currency' => $currency]);
            // Clear user currency cache
            Cache::forget('user_currency_' . auth()->id());
        }

        return redirect()->back()->with('success', __('messages.currency_updated'));
    }

    /**
     * Set user timezone preference
     */
    public function setTimezone(Request $request)
    {
        $timezone = $request->input('timezone');

        // Validate timezone
        if (!in_array($timezone, timezone_identifiers_list())) {
            return redirect()->back()->with('error', 'Invalid timezone');
        }

        Session::put('timezone', $timezone);

        if (auth()->check()) {
            auth()->user()->update(['timezone' => $timezone]);
        }

        return redirect()->back()->with('success', __('messages.timezone_updated'));
    }
}
