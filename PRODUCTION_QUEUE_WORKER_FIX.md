# 🔧 PRODUCTION QUEUE WORKER FIX - Cache & Telescope Errors

## 🚨 ERROR YANG TERJADI

1. **Database Error:**
   ```
   SQLSTATE[42S02]: Base table or view not found: 1146 Table 'sql_noteds_com.cache' doesn't exist
   ```
   - Cache table belum dibuat

2. **Telescope Error:**
   ```
   Class "Laravel\Telescope\TelescopeApplicationServiceProvider" not found
   ```
   - Telescope provider tidak ter-install dengan benar

---

## ✅ SOLUTION - RUN COMMANDS DI PRODUCTION

**SSH ke production server:**
```bash
ssh root@noteds.com
cd /www/wwwroot/noteds.com
```

### Step 1: Create Cache Table
```bash
php artisan cache:table
php artisan migrate
```

### Step 2: Disable/Fix Telescope Provider

**Option A: Comment out Telescope (safest)**
Edit `config/app.php`:
```php
'providers' => [
    // ... other providers ...
    // Laravel\Telescope\TelescopeServiceProvider::class,  // COMMENT THIS OUT
],
```

**Option B: Remove Telescope completely**
```bash
composer remove laravel/telescope
php artisan optimize:clear
```

### Step 3: Clear All Cache & Compiled Files
```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
```

### Step 4: Stop & Start Queue Worker

**Stop current worker (via supervisor):**
```bash
supervisorctl stop noteds-worker
```

**Verify it's stopped:**
```bash
ps aux | grep queue:work
# Should show nothing or only grep result
```

**Start worker again:**
```bash
supervisorctl start noteds-worker
```

**Verify it's running:**
```bash
ps aux | grep queue:work
# Should show: php /www/wwwroot/noteds.com/artisan queue:work...
```

**Check supervisor status:**
```bash
supervisorctl status noteds-worker
# Should show: noteds-worker                    RUNNING
```

---

## 📝 COMPLETE PRODUCTION FIX CHECKLIST

```bash
#!/bin/bash
# Production Fix - Copy & paste this

cd /www/wwwroot/noteds.com

# 1. Create cache table
echo "Creating cache table..."
php artisan cache:table
php artisan migrate

# 2. Clear caches
echo "Clearing caches..."
php artisan optimize:clear

# 3. Stop worker
echo "Stopping queue worker..."
supervisorctl stop noteds-worker

# 4. Wait 2 seconds
sleep 2

# 5. Start worker
echo "Starting queue worker..."
supervisorctl start noteds-worker

# 6. Verify
echo "Verifying..."
sleep 2
supervisorctl status noteds-worker
ps aux | grep queue:work | grep -v grep

echo "✅ Done!"
```

---

## 🔍 VERIFY QUEUE WORKER IS WORKING

**Check if worker is running:**
```bash
ps aux | grep "queue:work"
```
Should show:
```
www      12345  0.0  0.5 245180 44000 ?  S   10:30   0:02 php /www/wwwroot/noteds.com/artisan queue:work --queue=default --sleep=3 --tries=5
```

**Check supervisor status:**
```bash
supervisorctl status noteds-worker
```
Should show:
```
noteds-worker   RUNNING   pid 12345, uptime 0:05:32
```

**Test with manual job:**
```bash
cd /www/wwwroot/noteds.com
php artisan tinker
> DB::table('jobs')->count();
// Should return number of queued jobs
```

**Monitor logs in real-time:**
```bash
tail -f /var/log/supervisor/noteds-worker.log
# Should show queue processing logs
```

---

## 🔧 IF WORKER STILL NOT WORKING

### Check supervisor logs:
```bash
cat /var/log/supervisor/noteds-worker.log
# Look for error messages
```

### Check Laravel logs:
```bash
tail -f storage/logs/laravel.log | grep "queue\|webhook"
```

### Test queue manually:
```bash
cd /www/wwwroot/noteds.com
php artisan queue:work --queue=default --sleep=3 --tries=5 --verbose
# Run in foreground to see errors
```

### Restart supervisor:
```bash
supervisorctl reread
supervisorctl update
supervisorctl restart noteds-worker
```

---

## 📊 EXPECTED BEHAVIOR AFTER FIX

✅ **Queue Worker Status:**
- Process running: `RUNNING`
- No database errors
- No Telescope errors
- Logs show: "Attempting to process job..."

✅ **Webhook Processing:**
1. User topup di Midtrans
2. Webhook received → 200 OK
3. Job queued in `jobs` table
4. Worker picks up job
5. Wallet updated in 1-5 seconds

---

## 💡 NOTES

- **Cache table** diperlukan untuk rate limiting & cache operations
- **Telescope** adalah Laravel debugging tool - boleh di-disable untuk production
- **Queue worker** harus ALWAYS running (di-manage oleh supervisor)
- **Logs** sangat membantu debugging - check regularly

---

**Last Updated:** December 13, 2025
**Status:** Critical Production Fix
