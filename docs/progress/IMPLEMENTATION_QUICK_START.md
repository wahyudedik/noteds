# QUICK START - Implementation Checklist

Copy-paste ready code blocks untuk setiap file yang perlu diubah.

---

## FILE 1: app/Services/CurrencyService.php

**Location:** Setelah method `getBaseCurrency()` (line ~20)

**Add this method:**

```php
/**
 * Get default currency for a specific locale
 * Maps language → currency (en→USD, id→IDR, ar→AED)
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
```

**Also update fallback rates (around line 115):**

Find:
```php
$fallbacks = [
    'USD' => ['IDR' => 15000],
    'IDR' => ['USD' => 1 / 15000],
];
```

Replace with:
```php
$fallbacks = [
    'USD' => ['IDR' => 15500, 'AED' => 3.67],
    'IDR' => ['USD' => 1 / 15500, 'AED' => 1 / 4230],
    'AED' => ['USD' => 1 / 3.67, 'IDR' => 4230],
];
```

---

## FILE 2: app/Http/Controllers/LocaleController.php

**Replace the `switchLocale()` method (entire method):**

```php
public function switchLocale(string $locale)
{
    $supportedLocales = ['en', 'id', 'ar'];
    
    if (in_array($locale, $supportedLocales)) {
        App::setLocale($locale);
        Session::put('locale', $locale);
        
        // NEW: Auto-set currency berdasarkan locale
        $currencyService = app(\App\Services\CurrencyService::class);
        $defaultCurrency = $currencyService->getDefaultCurrencyForLocale($locale);
        
        if (auth()->check()) {
            auth()->user()->update(['currency' => $defaultCurrency]);
            \Illuminate\Support\Facades\Cache::forget("user_currency_" . auth()->id());
        }
        
        Session::put('currency', $defaultCurrency);
        
        return redirect()->back()
            ->with('success', __('messages.locale_changed', [], $locale))
            ->with('info', 'Currency updated to ' . $defaultCurrency);
    }
    
    return redirect()->back()->with('error', 'Unsupported locale');
}
```

**Update `setCurrency()` method - replace entire method:**

```php
public function setCurrency(Request $request)
{
    $currency = $request->input('currency');
    $supported = config('currency.supported_currencies', ['IDR', 'USD']);
    
    if (in_array($currency, $supported)) {
        Session::put('currency', $currency);
        
        if (auth()->check()) {
            auth()->user()->update(['currency' => $currency]);
            \Illuminate\Support\Facades\Cache::forget("user_currency_" . auth()->id());
        }
        
        return redirect()->back()
            ->with('success', __('messages.currency_updated'));
    }
    
    return redirect()->back()
        ->with('error', 'Unsupported currency: ' . $currency);
}
```

---

## FILE 3: app/Services/LocaleService.php

**Find and replace the `getFullSettings()` method:**

Find (around line 200):
```php
public function getFullSettings(?User $user = null): array
{
    return array_merge(
        [
            'locale' => config('app.locale', 'en'),
            'timezone' => config('app.timezone', 'UTC'),
            'currency' => 'USD',
        ],
        $user ? [
            'locale' => $user->locale ?? config('app.locale', 'en'),
            'timezone' => $user->timezone ?? config('app.timezone', 'UTC'),
            'currency' => $user->currency ?? 'USD',
        ] : []
    );
}
```

Replace with:
```php
public function getFullSettings(?User $user = null): array
{
    $currencyService = app(\App\Services\CurrencyService::class);
    
    $locale = $user?->locale ?? config('app.locale', 'en');
    $defaultCurrency = $currencyService->getDefaultCurrencyForLocale($locale);
    
    return array_merge(
        [
            'locale' => $locale,
            'timezone' => config('app.timezone', 'UTC'),
            'currency' => $defaultCurrency,
        ],
        $user ? [
            'locale' => $user->locale ?? config('app.locale', 'en'),
            'timezone' => $user->timezone ?? config('app.timezone', 'UTC'),
            'currency' => $user->currency ?? $defaultCurrency,
        ] : []
    );
}
```

---

## FILE 4: app/Helpers/CurrencyHelper.php

**Find and update `$currencies` array (line ~7):**

After the `USD` array, add:

```php
'AED' => [
    'symbol' => 'د.إ',
    'name' => 'UAE Dirham',
    'decimal_places' => 2,
    'thousands_separator' => ',',
    'decimal_separator' => '.',
    'locale' => 'ar_AE',
],
'SAR' => [
    'symbol' => 'ر.س',
    'name' => 'Saudi Riyal',
    'decimal_places' => 2,
    'thousands_separator' => ',',
    'decimal_separator' => '.',
    'locale' => 'ar_SA',
],
```

**Find and replace `getDefaultCurrency()` method (around line 57):**

Find:
```php
public static function getDefaultCurrency(): string
{
    if (auth()->check() && auth()->user()->currency) {
        return auth()->user()->currency;
    }

    if (session()->has('currency')) {
        return session('currency');
    }

    $locale = app()->getLocale();

    return match ($locale) {
        'id' => 'IDR',
        'ar' => 'USD',
        'en' => 'USD',
        default => 'IDR',
    };
}
```

Replace with:
```php
public static function getDefaultCurrency(): string
{
    if (auth()->check() && auth()->user()->currency) {
        return auth()->user()->currency;
    }

    if (session()->has('currency')) {
        return session('currency');
    }

    $currencyService = app(\App\Services\CurrencyService::class);
    return $currencyService->getDefaultCurrencyForLocale(app()->getLocale());
}
```

---

## FILE 5: config/currency.php

**Find and replace `supported_currencies`:**

Find (around line 22):
```php
'supported_currencies' => [
    'IDR',
    'USD',
],
```

Replace with:
```php
'supported_currencies' => [
    'IDR',
    'USD',
    'AED',
    'SAR',
],
```

---

## FILE 6: lang/ar/messages.php

**Find the currency options section (around line 40) and update:**

Find:
```php
'currency_option_idr' => 'Rp IDR',
'currency_option_usd' => '$ USD',
```

Replace with:
```php
'currency_option_idr' => 'روبية إندونيسية IDR',
'currency_option_usd' => 'دولار أمريكي USD',
'currency_option_aed' => 'درهم إماراتي AED',
'currency_option_sar' => 'ريال سعودي SAR',
```

---

## FILE 7: resources/views/dashboard.blade.php

**Find currency selector (around line 111):**

Find:
```blade
<select name="currency" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
    <option value="IDR" {{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'selected' : '' }}>{{ __('messages.currency_option_idr') }}</option>
    <option value="USD" {{ (auth()->user()->currency ?? 'IDR') === 'USD' ? 'selected' : '' }}>{{ __('messages.currency_option_usd') }}</option>
</select>
```

Replace with:
```blade
<select name="currency" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
    <option value="IDR" {{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'selected' : '' }}>{{ __('messages.currency_option_idr') }}</option>
    <option value="USD" {{ (auth()->user()->currency ?? 'IDR') === 'USD' ? 'selected' : '' }}>{{ __('messages.currency_option_usd') }}</option>
    @if(config('app.locale') === 'ar' || in_array('AED', config('currency.supported_currencies', [])))
        <option value="AED" {{ (auth()->user()->currency ?? 'IDR') === 'AED' ? 'selected' : '' }}>{{ __('messages.currency_option_aed') }}</option>
    @endif
    @if(in_array('SAR', config('currency.supported_currencies', [])))
        <option value="SAR" {{ (auth()->user()->currency ?? 'IDR') === 'SAR' ? 'selected' : '' }}>{{ __('messages.currency_option_sar') }}</option>
    @endif
</select>
```

---

## FILE 8: resources/views/seller/analytics/index.blade.php

**Find around line 249 and update:**

Find:
```blade
return '{{ config("app.currency") === "USD" ? "$" : "Rp " }}' + value.toLocaleString();
```

Before it (add at top of blade section):
```blade
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $userCurrency = $currencyService->getUserCurrency(auth()->user());
    $currencySymbol = \App\Helpers\CurrencyHelper::getCurrencyInfo($userCurrency)['symbol'] ?? '$';
@endphp
```

Replace the line with:
```blade
return '{{ $currencySymbol }}' + value.toLocaleString();
```

---

## FILE 9: Create Database Migration

```bash
php artisan make:migration add_currency_validation_to_users_table
```

**Content of migration file:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency')->default('IDR')->after('timezone');
            } else {
                $table->string('currency')->default('IDR')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
```

---

## FILE 10: Create Validation Middleware

**File:** `app/Http/Middleware/ValidateCurrency.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Log;

class ValidateCurrency
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $currencyService = app(CurrencyService::class);
            $user = auth()->user();
            $currency = $user->currency;
            $supportedCurrencies = $currencyService->getSupportedCurrencies();
            
            if (!in_array($currency, $supportedCurrencies)) {
                Log::warning("Invalid currency detected for user {$user->id}: {$currency}");
                
                $locale = $user->locale;
                $defaultCurrency = $currencyService->getDefaultCurrencyForLocale($locale);
                $user->update(['currency' => $defaultCurrency]);
                
                session()->put('currency', $defaultCurrency);
            }
        }
        
        return $next($request);
    }
}
```

**Register in `app/Http/Kernel.php`:**

Find `protected $middleware = [` array and add at the end:
```php
\App\Http\Middleware\ValidateCurrency::class,
```

---

## EXECUTION STEPS (In Order)

```bash
# 1. Make backups
mysqldump -u user -p database > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Edit all 8 PHP files (use copy-paste from above)
# vim app/Services/CurrencyService.php
# vim app/Http/Controllers/LocaleController.php
# ... etc

# 3. Create migration file
php artisan make:migration add_currency_validation_to_users_table

# 4. Create middleware file
# (create app/Http/Middleware/ValidateCurrency.php)

# 5. Register middleware in Kernel.php

# 6. Run migration
php artisan migrate

# 7. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 8. Run tests
php artisan test tests/Unit/CurrencyServiceTest.php
php artisan test tests/Feature/CurrencyLanguageSyncTest.php

# 9. Deploy & monitor
# tail -f storage/logs/laravel.log
```

---

## TESTING QUICK CHECK

```php
// In artisan tinker
php artisan tinker

// Test 1: Check mapping
$cs = app(\App\Services\CurrencyService::class);
$cs->getDefaultCurrencyForLocale('en')  // Should return 'USD'
$cs->getDefaultCurrencyForLocale('id')  // Should return 'IDR'
$cs->getDefaultCurrencyForLocale('ar')  // Should return 'AED'

// Test 2: Check conversion
$cs->convert(100, 'USD', 'IDR')  // Should be ~1,550,000
$cs->convert(1550000, 'IDR', 'USD')  // Should be ~100

// Test 3: Check user currency
$user = App\Models\User::first();
$cs->getUserCurrency($user)  // Should match their currency field

// Exit tinker
exit
```

---

## TROUBLESHOOTING

| Error | Solution |
|-------|----------|
| `Class not found` | Run `composer dump-autoload` |
| `Table currency missing` | Run `php artisan migrate` |
| `Currency not changing` | Clear cache: `config:clear cache:clear` |
| `Old rate still used` | Update rate in `/admin/exchange-rates/` |

---

**Time Estimate:**
- Code changes: 1-2 hours
- Testing: 1-2 hours  
- Total: 2-4 hours of work

**Good luck! 🚀**

