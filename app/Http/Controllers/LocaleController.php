<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Switch application locale
     */
    public function switchLocale(string $locale)
    {
        $supportedLocales = ['en', 'id', 'ar'];
        
        if (in_array($locale, $supportedLocales)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            
            return redirect()->back()->with('success', __('messages.locale_changed', [], $locale));
        }
        
        return redirect()->back()->with('error', 'Unsupported locale');
    }

    /**
     * Set user currency preference
     */
    public function setCurrency(Request $request)
    {
        $currency = $request->input('currency');
        $supported = ['IDR', 'USD']; // Only IDR and USD
        
        if (in_array($currency, $supported)) {
            Session::put('currency', $currency);
            
            // If user is authenticated, save to database
            if (auth()->check()) {
                auth()->user()->update(['currency' => $currency]);
            }
            
            return redirect()->back()->with('success', __('Currency updated successfully'));
        }
        
        return redirect()->back()->with('error', 'Unsupported currency');
    }

    /**
     * Set user timezone preference
     */
    public function setTimezone(Request $request)
    {
        $timezone = $request->input('timezone');
        
        // Validate timezone
        if (in_array($timezone, timezone_identifiers_list())) {
            Session::put('timezone', $timezone);
            
            // If user is authenticated, save to database
            if (auth()->check()) {
                auth()->user()->update(['timezone' => $timezone]);
            }
            
            return redirect()->back()->with('success', __('Timezone updated successfully'));
        }
        
        return redirect()->back()->with('error', 'Invalid timezone');
    }
}
