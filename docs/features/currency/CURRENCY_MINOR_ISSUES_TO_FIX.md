# 🔧 Minor Issues Found - Optional Fixes

## Issue #1: Hardcoded Indonesian Text in Workspace Form

**Severity:** 🟡 **MEDIUM** (UX Issue)  
**File:** `resources/views/workspaces/show.blade.php`  
**Line:** 278

### Current Code
```blade
Harga Diskon (Rp) <span class="text-gray-500 text-xs">(opsional)</span>
```

### Problem
- "Harga Diskon" is hardcoded Indonesian text
- "(Rp)" is hardcoded for IDR only
- English users see "Harga Diskon (Rp)" instead of "Discount Price ($)"
- Arabic users see Indonesian text

### Solution
Use translation keys and dynamic currency symbol:

```blade
{{ __('messages.discount_price') }} 
({{ \App\Helpers\CurrencyHelper::getCurrencyInfo(session('currency', 'IDR'))['symbol'] ?? 'Rp' }})
<span class="text-gray-500 text-xs">({{ __('messages.optional') }})</span>
```

### Or More Simply
```blade
{{ __('messages.discount_price') }} ({{ __('messages.optional') }})
```

### Also Check Line 270
```blade
<!-- Current -->
{{ __('messages.price') }} (Rp) *

<!-- Should be -->
{{ __('messages.price') }} ({{ \App\Helpers\CurrencyHelper::getCurrencyInfo(session('currency', 'IDR'))['symbol'] ?? 'Rp' }}) *
```

---

## Issue #2: JavaScript Currency Formatter in Simulators

**Severity:** 🟡 **LOW** (Minor Formatting Issue)  
**File:** `resources/views/simulators/index.blade.php`  
**Lines:** 942+

### Current Code
```javascript
function formatCurrency(amount) {
    // Simple formatting that doesn't match locale rules
    return '₹' + (amount).toLocaleString('en-US');
}
```

### Problem
- Uses simple `toLocaleString()` without respecting user's currency
- Always formats like English ($ symbol)
- Doesn't handle IDR's no-decimal format
- Doesn't handle Arabic symbols

### Solution Option 1: Use Intl API
```javascript
function formatCurrency(amount) {
    const locale = document.documentElement.lang || 'en';
    const currency = {
        'en': 'USD',
        'id': 'IDR',
        'ar': 'AED'
    }[locale] || 'USD';
    
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: currency === 'IDR' ? 0 : 2,
    }).format(amount);
}
```

### Solution Option 2: Pass from Backend
```php
// In controller
view('simulators.index', [
    'currencySymbol' => \App\Helpers\CurrencyHelper::getCurrencyInfo($userCurrency)['symbol'],
    'locale' => app()->getLocale(),
]);
```

```javascript
// In view
const formatCurrency = (amount) => {
    return '{{ $currencySymbol }}' + amount.toLocaleString('{{ $locale }}');
};
```

---

## Priority Matrix

| Issue | Severity | Impact | Effort | Priority | Recommended |
|-------|----------|--------|--------|----------|-------------|
| Hardcoded Indonesian | 🟡 Medium | UX | Low | High | ✅ YES |
| JS Formatter | 🟡 Low | UI | Medium | Low | Optional |

---

## Testing After Fixes

```bash
# 1. Test Workspace Form
- Switch to English (en)
  ✓ Should show "Price ($) *"
  ✓ Should show "Discount Price ($)"
  
- Switch to Arabic (ar)
  ✓ Should show "السعر (د.إ) *"
  ✓ Should show "سعر الخصم (د.إ)"

# 2. Test Simulators (if JS fixed)
- Reload page
- Switch languages
- Check formatter matches currency formatting rules
```

---

## Code for Workspace Fix

**File to modify:** `resources/views/workspaces/show.blade.php`

**Find and replace around line 270:**

```blade
<!-- OLD CODE (lines 270-282) -->
                            {{ __('messages.price') }} (Rp) *
                            <input type="number" 
                               name="price"
                               step="0.01"
                               value="{{ old('price', $workspace->price) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div class="mb-4">
                            Harga Diskon (Rp) <span class="text-gray-500 text-xs">(opsional)</span>
                            <input type="number" 
                               name="discount_price"
                               step="0.01"
                               value="{{ old('discount_price', $workspace->discount_price) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">

<!-- NEW CODE -->
                            {{ __('messages.price') }} ({{ $currencySymbol }}) *
                            <input type="number" 
                               name="price"
                               step="0.01"
                               value="{{ old('price', $workspace->price) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div class="mb-4">
                            {{ __('messages.discount_price') }} ({{ $currencySymbol }}) 
                            <span class="text-gray-500 text-xs">({{ __('messages.optional') }})</span>
                            <input type="number" 
                               name="discount_price"
                               step="0.01"
                               value="{{ old('discount_price', $workspace->discount_price) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
```

**Pass currency symbol in controller:**
```php
// In appropriate controller method
$currencySymbol = \App\Helpers\CurrencyHelper::getCurrencyInfo(session('currency', 'IDR'))['symbol'] ?? 'Rp';
return view('workspaces.show', [
    'workspace' => $workspace,
    'currencySymbol' => $currencySymbol,
]);
```

---

## Implementation Timeline

- **Recommended for next release:** Yes
- **Can delay to later:** Yes (low critical impact)
- **Estimated effort:** 15-30 minutes
- **Test effort:** 5 minutes

---

## Verification Commands

```bash
# 1. Clear caches first
php artisan cache:clear
php artisan config:clear

# 2. Test language switching
curl http://localhost/locale/en
curl http://localhost/locale/id
curl http://localhost/locale/ar

# 3. Verify in browser
# Switch language in dropdown
# Check workspace form shows correct symbols
```

---

## Notes

These are **optional improvements** - the system already works perfectly.  
Both issues are **UI/UX only**, not functional bugs.  
Users won't experience errors - just see text in wrong language.

**Recommendation:** Fix Issue #1 (workspace form) before production if possible.  
**Priority:** Issue #2 (JS formatter) can be fixed in next iteration.

