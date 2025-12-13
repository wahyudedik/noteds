# 🔧 PRODUCTION WEBHOOK REAL-TIME FIX

## 🎯 Masalah
- Payment sudah **settlement** di Midtrans
- Tapi di aplikasi masih **pending**
- Webhook tidak update real-time

## ✅ Solusi Implementasi

### 1. **Async Webhook Processing** 
Webhook sekarang:
- ✅ Return **200 OK immediately** ke Midtrans (tidak timeout)
- ✅ Queue job untuk processing (tidak block response)
- ✅ Process di background worker dengan retry logic

### 2. **Database Queue Fallback**
Jika queue worker down:
- ✅ Job disimpan di `jobs` table
- ✅ Bisa diproses kapan saja saat worker restart
- ✅ Cron job sync setiap 5 menit sebagai safety net

### 3. **Robust Error Handling**
- ✅ Return 200 OK bahkan jika ada error (prevent excessive retries)
- ✅ Log semua error untuk debugging
- ✅ Retry otomatis (5 kali dengan backoff 5 detik)

---

## 🚀 DEPLOYMENT CHECKLIST

### Step 1: Pull Latest Changes
```bash
cd /www/wwwroot/noteds.com
git pull origin main
```

### Step 2: Clear Cache
```bash
php artisan optimize:clear
php artisan cache:clear
```

### Step 3: Verify Configuration
```bash
# Check .env has these set correctly:
# MIDTRANS_SERVER_KEY=xxx
# MIDTRANS_CLIENT_KEY=xxx
# MIDTRANS_IS_PRODUCTION=true
# QUEUE_CONNECTION=database

# List env settings
env | grep MIDTRANS
env | grep QUEUE
```

### Step 4: Create Jobs Table (if not exists)
```bash
# This creates the jobs table for queue
php artisan queue:failed-table
php artisan queue:table
php artisan migrate
```

### Step 5: Start Queue Worker
This is **CRITICAL** for webhook processing:

```bash
# Option A: Run in foreground (for testing)
php artisan queue:work --queue=default --sleep=3 --tries=5

# Option B: Run as background service (permanent)
# Add to supervisord/systemd or use nohup:
nohup php artisan queue:work --queue=default --sleep=3 --tries=5 > /var/log/noteds-queue.log 2>&1 &

# Option C: Use Supervisor configuration
# Create /etc/supervisor/conf.d/noteds-queue.conf:
[program:noteds-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/noteds.com/artisan queue:work --queue=default --sleep=3 --tries=5
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/noteds-queue.log

# Then run:
supervisorctl reread
supervisorctl update
supervisorctl start noteds-queue:*
```

### Step 6: Run Cron Job Sync (Optional but Recommended)
```bash
# This runs the 5-minute sync schedule
php artisan schedule:run

# Or let cron handle it automatically:
# Add to crontab:
* * * * * cd /www/wwwroot/noteds.com && php artisan schedule:run >> /dev/null 2>&1
```

### Step 7: Test Webhook Processing
```bash
# Monitor logs in real-time
tail -f /var/log/noteds-queue.log
tail -f storage/logs/laravel.log | grep webhook

# Or check job queue status:
php artisan queue:work --verbose

# List failed jobs:
php artisan queue:failed
```

---

## 📊 How It Works

### Before (❌ Timeout Issue)
```
Midtrans Webhook
    ↓
POST /wallet/webhook
    ↓
Process in handler (takes 10+ seconds)
    ↓
Timeout / Response delay
    ↓
Midtrans thinks it failed, retries
```

### After (✅ Async Processing)
```
Midtrans Webhook
    ↓
POST /wallet/webhook
    ↓
Queue job + Return 200 OK immediately (< 100ms)
    ↓
Queue Worker Process:
  - Validate transaction
  - Update database
  - Credit wallet
  - Send notification
    ↓
Success / Retry if fails
```

---

## 🔍 Troubleshooting

### Queue Worker Not Running
```bash
# Check process
ps aux | grep "queue:work"

# If not running, start it:
php artisan queue:work --queue=default --sleep=3 --tries=5

# Check failed jobs
php artisan queue:failed
php artisan queue:retry all  # Retry all failed jobs
```

### Webhook Still Not Real-Time
```bash
# Check logs
tail -f storage/logs/laravel.log | grep "Processing Midtrans webhook"

# Manually test webhook
# Send a test webhook with valid signature to:
curl -X POST https://noteds.com/wallet/webhook \
  -H "Content-Type: application/json" \
  -d '{...webhook payload...}'
```

### Jobs Pile Up
```bash
# If jobs keep piling up, check why:
php artisan queue:work --verbose
# Look for errors in the output

# Or clear them:
php artisan queue:clear
```

### Database Connection Issues
```bash
# Verify connection
php artisan tinker
> \Illuminate\Support\Facades\DB::connection()->getPdo();
```

---

## 📋 Implementation Details

### Files Changed:
1. **`app/Http/Controllers/WalletController.php`**
   - Modified `webhook()` method to queue job
   - Returns 200 OK immediately
   - Removed blocking processing

2. **`app/Jobs/ProcessMidtransWebhook.php`** (NEW)
   - Handles actual webhook processing
   - Async with retry logic (5 tries)
   - Better error handling

3. **`.env` Configuration** (must be set on production)
   - `QUEUE_CONNECTION=database` (already set)
   - `MIDTRANS_SERVER_KEY=xxx` (must be set)
   - `MIDTRANS_IS_PRODUCTION=true` (for production Midtrans)

### Job Features:
- ✅ Auto-retry: 5 times with 5-second backoff
- ✅ Timeout protection: 30 seconds per job
- ✅ Failure logging: Detailed error logs
- ✅ Database persistence: Jobs saved in DB queue table
- ✅ Duplicate prevention: Checks if already processed

---

## 🎯 Expected Result After Deployment

### Real-Time Updates:
1. User topup di Midtrans
2. Midtrans sends webhook
3. **Your app returns 200 OK immediately** ✅
4. **Queue job processes in background** ✅
5. **Wallet balance updates in 1-5 seconds** ✅
6. **User sees notification** ✅

### Fallback (if queue fails):
1. Cron job runs every 5 minutes
2. Syncs all pending transactions from Midtrans API
3. Updates any missed payments ✅

---

## 📞 Support

If webhook still not real-time after deployment:

1. ✅ Verify queue worker is running: `ps aux | grep queue:work`
2. ✅ Check job queue: `php artisan queue:work --verbose`
3. ✅ Monitor logs: `tail -f storage/logs/laravel.log`
4. ✅ Test webhook manually: Send curl request to endpoint
5. ✅ Check database: Verify `jobs` table has queued items

---

**Last Updated:** December 13, 2025
**Version:** 2.0 (Async Queue Implementation)
