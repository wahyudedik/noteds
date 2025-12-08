# Share Analytics - Implementation Summary

Implementasi complete untuk 5 requirement dari Share Analytics. Berikut adalah ringkasan lengkapnya:

---

## 1. ✅ PERMISSION - Share Analytics Hanya untuk Seller

**File Modified:** `app/Http/Controllers/ShareAnalyticsController.php`

**Changes:**
- Added middleware untuk restrict akses hanya ke seller role
- Throws 403 Forbidden jika non-seller mencoba akses
- Updated to use `$request->user()` pattern

**Code:**
```php
public function __construct(private NoteShareService $noteShareService)
{
    // Only sellers can access share analytics
    $this->middleware('auth');
    $this->middleware(function ($request, $next) {
        $user = $request->user();
        if (!$user || $user->role !== 'seller') {
            abort(403, 'Only sellers can access share analytics.');
        }
        return $next($request);
    });
}
```

---

## 2. ✅ ADMIN SETTINGS - Share Configuration UI

**Files Created:**
- `app/Http/Controllers/Admin/ShareSettingsController.php` - Controller untuk manage share settings
- `resources/views/admin/share-settings/index.blade.php` - UI form untuk admin

**Route:**
- `GET /admin/settings/share` - Display settings page
- `POST /admin/settings/share` - Update settings

**Configurable Settings:**
```
- share_commission_percent (0-100%)
  → Komisi dari harga catatan yang dibeli via share link
  
- share_monthly_payout_day (1-31)
  → Tanggal dalam sebulan untuk mentransfer komisi
  
- share_max_shares_per_user_per_link (1-1000)
  → Batasan share per user per link (fraud prevention)
  
- share_commission_payment_mode (monthly/immediate)
  → Apakah komisi pending atau langsung ke wallet
```

**Admin Dashboard Link:**
- Added Quick Link di admin dashboard untuk akses settings

---

## 3. ✅ MONTHLY COMMISSION ACCUMULATION

**Files Created:**
- `app/Models/NoteShareCommission.php` - Model untuk track pending commissions
- `app/Jobs/ProcessMonthlyShareCommissionJob.php` - Job untuk batch transfer
- `database/migrations/2025_12_08_000002_create_note_share_commissions_table.php`

**Database Table:**
```
note_share_commissions
├── id (uuid primary)
├── share_referral_id (foreign)
├── seller_id (foreign)
├── transaction_id (foreign)
├── commission_amount (decimal)
├── commission_percent (decimal)
├── status (pending/paid)
├── month (Y-m format)
├── paid_at (timestamp)
└── created_at, updated_at
```

**How It Works:**
1. Ketika buyer purchase via share link → komisi dicatat di `note_share_commissions` table
2. Status di-set sebagai "pending"
3. Setiap hari ke-`share_monthly_payout_day`, job `ProcessMonthlyShareCommissionJob` berjalan:
   - Mengambil semua pending commissions dari bulan sebelumnya
   - Group by seller
   - Deduct dari admin wallet
   - Transfer ke seller wallet
   - Update status menjadi "paid"

**Scheduling:**
```php
// routes/console.php
Schedule::job(new \App\Jobs\ProcessMonthlyShareCommissionJob())
    ->monthlyOn($payoutDay, '11:00')
    ->timezone('Asia/Jakarta')
    ->description('Transfer accumulated share commissions');
```

---

## 4. ✅ FRAUD PREVENTION - One Share Per User Per Link

**Files Created:**
- `app/Models/NoteShareUserTracking.php` - Model untuk track user shares
- `database/migrations/2025_12_08_000001_create_note_share_user_tracking_table.php`

**Database Table:**
```
note_share_user_tracking
├── id (uuid primary)
├── share_referral_id (foreign unique)
├── user_id (nullable, unique with referral_id)
├── share_count (integer)
└── created_at, updated_at
```

**Implementation in NoteShareService:**
```php
private function trackAndValidateShareCount(NoteShareReferral $shareReferral, User $sharer): void
{
    $maxSharesPerLink = Setting::getSetting('share_max_shares_per_user_per_link', 'marketplace', 1);
    
    // Get or create tracking record
    $tracking = NoteShareUserTracking::firstOrCreate(
        [
            'share_referral_id' => $shareReferral->id,
            'user_id' => $sharer->id,
        ],
        ['share_count' => 0]
    );
    
    // Check if user has exceeded share limit
    if ($tracking->share_count >= $maxSharesPerLink) {
        throw new \Exception(
            "You can only share this link {$maxSharesPerLink} time(s). Create a new share link if you want to share again."
        );
    }
    
    // Increment share count
    $tracking->increment('share_count');
}
```

**How It Works:**
1. User mencoba share catatan → `generateShareUrl()` dipanggil
2. Validation check: apakah user sudah share link ini sebelumnya?
3. Jika sudah mencapai limit → throw exception
4. Jika belum → increment share count & generate link
5. Setting `share_max_shares_per_user_per_link = 1` → user hanya bisa share 1x per link
6. User bisa buat link baru jika ingin share lagi

**Benefit:**
- Mencegah spam/fraud
- Membatasi 1 user tidak bisa manipulate same link 100x
- Jika different link = bisa share berkali-kali

---

## 5. ✅ PAYMENT LOGIC - Pending vs Immediate

**Files Modified:**
- `app/Services/NoteShareService.php` - Updated `processShareCommission()` method

**Logic:**
```php
// Get payment mode setting
$paymentMode = Setting::getSetting('share_commission_payment_mode', 'marketplace', 'monthly');

if ($paymentMode === 'immediate') {
    // Immediate payment mode - transfer to wallet immediately
    $this->transferCommissionToWallet($shareReferral->sharer, $commissionAmount);
} else {
    // Monthly payment mode - create pending commission record
    $month = now()->format('Y-m');
    NoteShareCommission::create([
        'share_referral_id' => $shareReferral->id,
        'seller_id' => $shareReferral->sharer_id,
        'transaction_id' => $transaction->id,
        'commission_amount' => $commissionAmount,
        'commission_percent' => $commissionPercent,
        'status' => 'pending',
        'month' => $month,
    ]);
}
```

**Two Modes:**

### Mode: Monthly (Default)
- Komisi disimpan sebagai "pending" di database
- Tidak langsung masuk wallet
- Dikumpulkan satu bulan penuh
- Akhir bulan (hari yang dikonfigurasi) → transfer via job

### Mode: Immediate
- Komisi langsung ditransfer ke wallet seller
- Tidak menunggu bulan berakhir
- Seller bisa langsung withdraw

---

## Database Migrations

Jalankan migrations:
```bash
php artisan migrate
```

Files:
- `2025_12_08_000001_create_note_share_user_tracking_table.php`
- `2025_12_08_000002_create_note_share_commissions_table.php`

---

## Admin Configuration

### Access URL
`https://yourdomain.com/admin/settings/share`

### Settings to Configure:

| Setting | Default | Range | Description |
|---------|---------|-------|-------------|
| Share Commission % | 5% | 0-100 | Komisi dari price catatan |
| Monthly Payout Day | 1 | 1-31 | Tanggal transfer (1=tgl 1) |
| Max Shares Per Link | 1 | 1-1000 | Share limit per user per link |
| Payment Mode | monthly | monthly/immediate | Tipe pembayaran |

---

## Testing Checklist

- [ ] Non-seller tidak bisa akses `/share/analytics` (forbidden)
- [ ] Seller bisa akses `/share/analytics` (200 OK)
- [ ] Admin bisa akses `/admin/settings/share`
- [ ] Settings dapat disave dan di-read kembali
- [ ] User dapat share catatan 1x (setting default)
- [ ] Attempt share 2x same link → exception
- [ ] Buat link baru → bisa share lagi
- [ ] If mode=monthly: komisi masuk `note_share_commissions` as pending
- [ ] If mode=immediate: komisi langsung di wallet
- [ ] Job `ProcessMonthlyShareCommissionJob` berjalan di hari yg dikonfigurasi
- [ ] Komisi ditransfer dari admin wallet → seller wallet
- [ ] Status komisi berubah jadi "paid" setelah transfer

---

## Summary

✅ **Complete Implementation:**
1. Permission restriction untuk Share Analytics (seller only)
2. Admin settings UI untuk configure komisi, payout day, share limit
3. Monthly commission accumulation system dengan pending status
4. Fraud prevention: one share per user per link
5. Flexible payment mode: monthly (default) atau immediate

**Key Files Modified/Created:**
- Controllers: 1 new + 1 modified
- Models: 2 new
- Views: 1 new
- Migrations: 2 new
- Jobs: 1 new
- Services: 1 modified
- Routes: 3 new

**Admin Dashboard Links:**
- `/admin/settings/share` - Share analytics settings

**No breaking changes** - backward compatible dengan existing system.
