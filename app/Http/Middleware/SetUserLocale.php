<?php

namespace App\Http\Middleware;

use App\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;

class SetUserLocale
{
    public function __construct(
        private LocaleService $localeService,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            // Set locale dari user preferences
            $locale = $this->localeService->getUserLocale($user);
            app()->setLocale($locale);

            // Set timezone untuk date operations
            $timezone = $this->localeService->getUserTimezone($user);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}
