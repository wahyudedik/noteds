# 🛠️ Implementation Guide - Bug Fixes

## Quick Start

Ikuti langkah-langkah di bawah untuk memperbaiki semua bug.

---

## STEP 1: Fix Critical Bugs di MarketplaceController.php

### Lokasi: `app/Http/Controllers/MarketplaceController.php`

#### Fix 1A: Purchase Notification (Line 1360-1367)

**BEFORE:**
```php
if (isset($notificationData['purchase']) && is_array($notificationData['purchase'])) {
    $buyerForNotification = User::find($notificationData['purchase']['buyer_id'] ?? null);
    if ($buyerForNotification) {
        $this->notificationService->notifyPurchase(
            $buyerForNotification,
            $note,
            $notificationData['purchase']['amount'],
            $notificationData['purchase']['transaction_id'],
            $notificationData['purchase']['breakdown'] ?? []
        );
    }
}
```

**AFTER:**
```php
if (isset($notificationData['purchase']) && is_array($notificationData['purchase'])) {
    $buyerForNotification = User::find($notificationData['purchase']['buyer_id'] ?? null);
    if ($buyerForNotification) {
        $this->notificationService->notifyPurchase(
            $buyerForNotification,
            $note,
            $notificationData['purchase']['amount'] ?? 0,
            $notificationData['purchase']['transaction_id'] ?? null,
            $notificationData['purchase']['breakdown'] ?? []
        );
    }
}
```

**Changes:**
- Line 1363: Tambah `?? 0` untuk `amount`
- Line 1364: Tambah `?? null` untuk `transaction_id`

---

#### Fix 1B: Sale Notification (Line 1371-1380)

**BEFORE:**
```php
if (isset($notificationData['sale']) && is_array($notificationData['sale'])) {
    $sellerForNotification = User::find($notificationData['sale']['seller_id'] ?? null);
    if ($sellerForNotification) {
        $this->notificationService->notifySale(
            $sellerForNotification,
            $note,
            $notificationData['sale']['amount'],
            $notificationData['sale']['buyer_name'],
            $notificationData['sale']['breakdown'] ?? []
        );
    }
}
```

**AFTER:**
```php
if (isset($notificationData['sale']) && is_array($notificationData['sale'])) {
    $sellerForNotification = User::find($notificationData['sale']['seller_id'] ?? null);
    if ($sellerForNotification) {
        $this->notificationService->notifySale(
            $sellerForNotification,
            $note,
            $notificationData['sale']['amount'] ?? 0,
            $notificationData['sale']['buyer_name'] ?? 'Unknown',
            $notificationData['sale']['breakdown'] ?? []
        );
    }
}
```

**Changes:**
- Line 1376: Tambah `?? 0` untuk `amount`
- Line 1377: Tambah `?? 'Unknown'` untuk `buyer_name`

---

## STEP 2: Fix Rector Configuration

### Lokasi: `rector.php`

**BEFORE:**
```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        SetList::CODE_QUALITY,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
    ]);

    $rectorConfig->rule(ReadOnlyPropertyRector::class);
};
```

**AFTER:**
```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPhpVersion(\Rector\ValueObject\PhpVersion::PHP_82)
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->withRules([
        \Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class,
    ])
    ->withSets([
        \Rector\Set\ValueObject\SetList::CODE_QUALITY,
        \Rector\Set\ValueObject\SetList::TYPE_DECLARATION,
        \Rector\Set\ValueObject\SetList::EARLY_RETURN,
    ]);
```

**Changes:**
- Update ke API baru RectorConfig dengan method chaining
- Gunakan `RectorConfig::configure()` sebagai entrypoint
- Update `withPhpVersion()` untuk PHP 82
- Gunakan `withRules()` dan `withSets()` methods

---

## STEP 3: Fix Tailwind CSS Classes

### Lokasi: `resources/views/marketplace/show.blade.php` (Line 400-408)

**Opsi Terpilih:** CSS Variables (Recommended - Paling Clean)

#### Step 3A: Update Badge Model

Tambahkan color mapping ke `app/Models/Badge.php`:

```php
// app/Models/Badge.php

class Badge extends Model
{
    // ... existing code ...
    
    // Mapping warna badge ke CSS color
    protected $colorMap = [
        'gold' => '#b45309',    // amber-600
        'green' => '#16a34a',   // green-600
        'blue' => '#2563eb',    // blue-600
        'purple' => '#9333ea',  // purple-600
        'yellow' => '#eab308',  // yellow-500
        'orange' => '#ea580c',  // orange-600
        'default' => '#4b5563', // slate-600
    ];
    
    public function getColorHexAttribute()
    {
        return $this->colorMap[$this->color] ?? $this->colorMap['default'];
    }
}
```

#### Step 3B: Update Blade View

**BEFORE:**
```blade
<span
    class="inline-flex items-center text-xs font-medium 
    @if ($badge->color === 'gold') text-amber-600
    @elseif($badge->color === 'green') text-green-600
    @elseif($badge->color === 'blue') text-blue-600
    @elseif($badge->color === 'purple') text-purple-600
    @elseif($badge->color === 'yellow') text-yellow-500
    @elseif($badge->color === 'orange') text-orange-600
    @else text-slate-600 @endif"
    title="{{ $badge->name }}">
```

**AFTER:**
```blade
<span
    class="inline-flex items-center text-xs font-medium"
    style="color: {{ $badge->color_hex }};"
    title="{{ $badge->name }}">
```

---

## STEP 4: Verification & Testing

### Test 1: PHP Syntax Check
```bash
php -l app/Http/Controllers/MarketplaceController.php
php -l rector.php
```

### Test 2: Build Frontend
```bash
npm run build
```

Expected output: Build sukses tanpa warnings

### Test 3: Check Errors dalam VS Code
- Buka file yang diperbaiki
- Pastikan tidak ada red squiggly lines
- Check Problems panel (Ctrl+Shift+M)

### Test 4: Database Seeders (Optional)
```bash
php artisan tinker
```

Kemudian test notification:
```php
$note = Note::first();
$buyer = User::find(2);
event(new App\Events\NoteContentUpdated($note));
```

---

## Summary of Changes

| File | Changes | Type | Impact |
|------|---------|------|--------|
| MarketplaceController.php | +2 null coalescing operators | Bug Fix | High |
| rector.php | Config refactor to new API | Bug Fix | Medium |
| Badge.php (NEW) | Add color hex mapping | Enhancement | Low |
| marketplace/show.blade.php | Simplify conditional classes | Refactor | Medium |

---

## Rollback Plan (if needed)

Jika ada issue setelah implementasi:

1. **Revert MarketplaceController.php:**
   ```bash
   git checkout app/Http/Controllers/MarketplaceController.php
   ```

2. **Revert rector.php:**
   ```bash
   git checkout rector.php
   ```

3. **Revert blade views:**
   ```bash
   git checkout resources/views/marketplace/show.blade.php
   ```

---

## Timeline

- **Start:** Sekarang
- **Duration:** ~30 menit
- **Testing:** +10 menit
- **Total:** ~40 menit

---

**Ready to implement? Let's go! ✨**
