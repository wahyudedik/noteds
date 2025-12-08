<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use App\Services\LocaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function __construct(
        private CurrencyService $currencyService,
        private LocaleService $localeService,
    ) {}

    /**
     * Get user's locale and currency settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'locale' => $this->localeService->getUserSettings($user),
            'currency' => [
                'code' => $user->currency ?? 'USD',
                'symbol' => $this->currencyService->getCurrencySymbol($user->currency ?? 'USD'),
            ],
            'all_locales' => $this->localeService->getSupportedLocales(),
            'all_timezones' => $this->localeService->getSupportedTimezones(),
            'all_currencies' => $this->currencyService->getSupportedCurrencies(),
        ]);
    }

    /**
     * Update user's locale and currency settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'locale' => 'nullable|string|in:en,id,ar',
            'timezone' => 'nullable|string|in:' . implode(',', array_keys($this->localeService->getSupportedTimezones())),
            'currency' => 'nullable|string|in:' . implode(',', array_keys($this->currencyService->getSupportedCurrencies())),
        ]);

        if (isset($validated['locale'])) {
            $this->localeService->setUserLocale($user, $validated['locale']);
        }

        if (isset($validated['timezone'])) {
            $this->localeService->setUserTimezone($user, $validated['timezone']);
        }

        if (isset($validated['currency'])) {
            $this->currencyService->setUserCurrency($user, $validated['currency']);
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'locale' => $this->localeService->getUserSettings($user),
            'currency' => [
                'code' => $user->currency ?? 'USD',
                'symbol' => $this->currencyService->getCurrencySymbol($user->currency ?? 'USD'),
            ],
        ]);
    }
}
