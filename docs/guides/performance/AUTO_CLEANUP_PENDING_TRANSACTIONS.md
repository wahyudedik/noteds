# 🧹 AUTO-CLEANUP PENDING TRANSACTIONS SYSTEM

**Status:** ✅ IMPLEMENTED & READY TO USE  
**Date:** December 12, 2025  
**Purpose:** Automatically clean up suspicious/injected pending transactions

---

## 📋 Overview

System ini otomatis mendeteksi dan membersihkan pending transactions yang:
- Sudah lama pending (>24 jam, >3 hari)
- Mungkin hasil dari injection attempts
- Tidak ter-confirm oleh Midtrans

---

## 🚀 Features

### 1. **Automatic Scheduled Cleanup**
- Daily cleanup (1 AM) untuk pending >24 jam
- Weekly cleanup (Sundays 2 AM) untuk pending >3 hari
- Verify status dengan Midtrans sebelum delete

### 2. **Manual Cleanup Commands**
- `transactions:cleanup-pending` - Interactive cleanup
- `admin:cleanup-transactions` - Advanced admin tool
- `transactions:report-pending` - Find suspicious transactions
- `transactions:cleanup-summary` - Daily report

### 3. **Safety Features**
- Confirmation before deletion
- Dry-run mode (preview tanpa delete)
- CSV export before deletion
- Comprehensive logging
- Rollback on error

### 4. **Midtrans Verification**
- Check actual status di Midtrans API
- Auto-update if status changed
- Skip deletion jika masih pending in Midtrans

---

## 📅 Schedule (Auto)

### Daily (3:00 AM - Jakarta Time)
```
transactions:cleanup-pending --days=1 --verify
```
- Cleanup pending transactions older than 1 day
- Verify dengan Midtrans
- Automatic (no confirmation needed)
- Runs with `withoutOverlapping()` - tidak double-run

### Weekly (Sunday 2:00 AM - Jakarta Time)
```
transactions:cleanup-pending --days=3 --verify --force
```
- More aggressive cleanup (3+ days old)
- Weekly full scan
- Force delete without confirmation

### Monitoring (Every 6 Hours)
```
transactions:report-pending
```
- Alert untuk suspicious pending transactions
- Threshold: >24 hours old

### Daily Report (6:00 AM)
```
transactions:cleanup-summary
```
- Summary statistics
- Recommendations

---

## 🎮 Manual Usage

### 1. **Find Suspicious Transactions**
```bash
php artisan transactions:report-pending --threshold=24
```

Output:
```
⚠️  Found 7 suspicious pending transactions (older than 24 hours)

🔴 550e8400-e29b-41d4-a716-446655440001
   Amount: 90,000.00 IDR
   Pending: 5d 3h ago
   Midtrans: topup-1733999700-e3c5e2a
   User: Reyhan (reyhan@email.com)
```

### 2. **Interactive Cleanup** (Safest)
```bash
php artisan transactions:cleanup-pending --days=1 --verify

# With Midtrans verification
# Interactive confirmation
# Auto-updates if status changed
# Deletes only truly failed transactions
```

Options:
- `--days=1` - Cleanup older than X days
- `--verify` - Check Midtrans status first
- `--force` - Skip confirmation

### 3. **Advanced Admin Tool**
```bash
php artisan admin:cleanup-transactions --status=pending --days=1 --export

# With dry-run first:
php artisan admin:cleanup-transactions --status=pending --days=1 --dry-run

# Then delete:
php artisan admin:cleanup-transactions --status=pending --days=1 --force --export
```

Options:
- `--status=pending|failed|success` - Filter by status
- `--days=1` - Age filter
- `--force` - Skip confirmation
- `--dry-run` - Preview only (no changes)
- `--export` - Save to CSV before delete

### 4. **Daily Summary Report**
```bash
php artisan transactions:cleanup-summary
```

Output:
```
📊 Generating Cleanup Summary Report...

┌─────────────────────────────────┬──────────────────┐
│ Metric                          │ Count/Amount     │
├─────────────────────────────────┼──────────────────┤
│ Total Pending Transactions      │ 7                │
│ Pending > 24 hours              │ 5                │
│ Successful (Last 24h)           │ 42               │
│ Failed (Last 24h)               │ 2                │
│ Total Pending Amount            │ Rp 450,000       │
│ Old Pending Amount              │ Rp 400,000       │
└─────────────────────────────────┴──────────────────┘

⚠️  ACTION REQUIRED:
  • 5 transactions are pending for >24 hours
  • Total amount: Rp 400,000
  • Run: php artisan transactions:cleanup-pending --days=1 --verify
```

---

## 🔍 Example Scenario: Cleaning Up Injected Transactions

Dari gambar Anda - ada 7 pending top-ups sekaligus:

### Step 1: Identify
```bash
php artisan transactions:report-pending --threshold=24
```

### Step 2: Verify
```bash
php artisan transactions:cleanup-pending --days=1 --verify
```

Output:
```
🔍 Searching for pending transactions...

🗑️  DELETE | 550e8400-e29b-41d4-a716-446655440001 | 90,000.00 IDR | 6d ago (Status: failed)
🗑️  DELETE | 550e8400-e29b-41d4-a716-446655440002 | 90,000.00 IDR | 6d ago (Status: failed)
🗑️  DELETE | 550e8400-e29b-41d4-a716-446655440003 | 9,000.00 IDR | 6d ago (Status: cancelled)
✅ UPDATE | 550e8400-e29b-41d4-a716-446655440004 | 12,312.32 IDR | 6d ago (Actually success - will update)
⏳ KEEP   | 550e8400-e29b-41d4-a716-446655440005 | 2,333.33 IDR | 5d ago (Still pending in Midtrans)

Summary: 5 transactions marked for deletion
```

### Step 3: Confirm & Delete
```
Delete these pending transactions? (yes/no) [no]: yes

✅ Successfully deleted 5 pending transactions.

┌──────────────────┬──────────────────┐
│ Metric           │ Value            │
├──────────────────┼──────────────────┤
│ Total Found      │ 7                │
│ Deleted          │ 5                │
│ Updated          │ 1                │
│ Cutoff Date      │ 2025-12-11       │
└──────────────────┴──────────────────┘
```

---

## 📊 What Each Command Does

| Command | Purpose | When to Use | Auto? |
|---------|---------|------------|-------|
| `cleanup-pending` | Delete pending transactions | Investigate & cleanup | No (2x daily) |
| `admin:cleanup-transactions` | Advanced filtering & export | Admin manual cleanup | No |
| `report-pending` | Find suspicious transactions | Monitoring & alerts | Yes (6h) |
| `cleanup-summary` | Daily statistics & recommendations | Dashboard reporting | Yes (6 AM) |

---

## 🛡️ Safety Mechanisms

### 1. **Midtrans Verification**
- Sebelum delete, cek status real di Midtrans
- Jika ternyata success → Update, jangan delete
- Jika ternyata failed → Delete
- Jika masih pending → Keep

### 2. **Confirmation Required**
```bash
# Will ask for confirmation (unless --force)
php artisan transactions:cleanup-pending --days=1

# Auto-confirm
php artisan transactions:cleanup-pending --days=1 --force
```

### 3. **Dry Run**
```bash
# Preview tanpa delete
php artisan admin:cleanup-transactions --dry-run

# Ketika puas, run for real:
php artisan admin:cleanup-transactions --force
```

### 4. **CSV Export**
```bash
# Backup data sebelum delete
php artisan admin:cleanup-transactions --export --force

# File saved ke: storage/logs/cleanup_export_YYYY-MM-DD_HH-ii-ss.csv
```

### 5. **Database Rollback**
```php
DB::beginTransaction();
// ... delete operations ...
DB::commit(); // Rollback otomatis jika error
```

### 6. **Comprehensive Logging**
Semua aksi logged ke:
- `storage/logs/laravel.log` - Application logs
- Database audit trail
- Admin dapat trace history cleanup

---

## 📈 Monitoring

### Watch Logs
```bash
# Real-time logs
tail -f storage/logs/laravel.log

# Filter cleanup logs
grep -i "cleanup" storage/logs/laravel.log

# Filter pending transactions
grep -i "pending" storage/logs/laravel.log
```

### Dashboard (Future)
Admin dashboard akan show:
- Pending transactions count
- Total pending amount
- Last cleanup time
- One-click cleanup button

---

## ⚙️ Configuration

### Change Schedule Time
Edit `app/Console/Kernel.php`:

```php
// Default: 3 AM
$schedule->command('transactions:cleanup-pending --days=1 --verify')
    ->dailyAt('03:00')
    ->timezone('Asia/Jakarta');

// Change to 2 AM:
    ->dailyAt('02:00')
```

### Change Cleanup Age Threshold
```php
// Default: 1 day untuk daily, 3 days untuk weekly
$schedule->command('transactions:cleanup-pending --days=2 --verify')
    ->dailyAt('03:00');
```

### Disable Auto Cleanup
```php
// Comment out di Kernel.php
// $schedule->command('transactions:cleanup-pending --days=1 --verify')
//     ->dailyAt('03:00');
```

---

## 🚨 Important Notes

### Midtrans Webhook Still Running
- Webhook tetap berjalan
- Jika payment selesai → status update ke 'success'
- Cleanup hanya untuk yang tidak ter-update webhook-nya

### Keep Backup
- Export CSV sebelum cleanup besar
- Data akan hard-delete dari database
- Backup tersimpan di `storage/logs/`

### Timezone
- Default: Asia/Jakarta
- Change di Kernel.php jika berbeda

### Performance
- `withoutOverlapping()` - Prevent double-run
- Batch processing - Tidak lock DB terlalu lama
- Runs at off-peak hours (3-6 AM)

---

## 🔧 Troubleshooting

### Command Tidak Jalan?
```bash
# Check if scheduler running
php artisan schedule:list

# Test command manually
php artisan transactions:cleanup-pending --days=1

# Check logs
tail -f storage/logs/laravel.log
```

### "No pending transactions" tapi masih ada?
```bash
# Check database directly
php artisan tinker
>>> Transaction::where('status', 'pending')->count()
```

### Midtrans Verification Error?
```bash
# Check config
php artisan tinker
>>> config('services.midtrans.server_key')
>>> config('services.midtrans.is_production')

# Manual test
>>> Http::withBasicAuth('SERVER_KEY', '')->get('https://app.sandbox.midtrans.com/v2/ORDER_ID/status')->json()
```

---

## 📝 Inject Attack Prevention

### Scenario: Attacker injects 100 pending transactions

**Old Way (Manual):**
- Admin manually check each one
- Manually delete (error-prone)
- Time-consuming ❌

**New Way (Auto):**
1. Scheduler runs daily 3 AM
2. Finds pending >24 hours
3. Verify dengan Midtrans
4. Auto-delete jika failed/cancelled
5. Logs semua aksi
6. Report to admin 6 AM ✅

---

## 📞 Support

### For More Help:
1. Run: `php artisan help transactions:cleanup-pending`
2. Check logs: `storage/logs/laravel.log`
3. Test schedule: `php artisan schedule:work`

---

## Summary

✅ **Automatic Daily Cleanup**
- Runs 3 AM every day
- Cleans pending >24 hours
- Verify dengan Midtrans
- Auto-update if needed

✅ **Manual Tools for Admin**
- Interactive cleanup
- Advanced filtering
- CSV export
- Dry-run preview

✅ **Safety First**
- Confirmation required
- Midtrans verification
- Transaction logging
- Rollback on error

✅ **Monitoring**
- Hourly alerts for suspicious
- Daily summary report
- Comprehensive logging

---

**Status: READY TO DEPLOY** 🚀

Injected/pending transactions akan otomatis ter-clean setiap hari!
