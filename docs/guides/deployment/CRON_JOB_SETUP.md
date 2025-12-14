# Cron Job Setup Guide for Noteds

## Overview

The application uses Laravel's task scheduling for automated background jobs including Midtrans payment synchronization. This guide explains how to set up cron jobs on your production server.

## What Gets Automated?

### 1. **Midtrans Payment Status Sync** (Every 5 minutes)
- Command: `midtrans:sync-status --all`
- Purpose: Automatically synchronizes pending topup transactions with Midtrans API
- Benefit: Catches missed webhooks and updates payment status automatically
- Logs: Success/failure logged to `storage/logs/laravel.log`

### 2. **Transaction Cleanup** (Daily at 3 AM)
- Command: `transactions:cleanup-pending --days=1 --verify`
- Purpose: Cleans up pending transactions older than 1 day
- Runs in: Asia/Jakarta timezone

### 3. **Escrow Auto-Release** (Daily at 4 AM)
- Command: `escrows:auto-release`
- Purpose: Automatically releases escrows past grace period

### 4. **Other Scheduled Tasks**
- Subscription renewals (5 AM)
- Transaction reporting (every 4 hours)
- Cleanup summary (6 AM)

## Server Setup

### Linux/Ubuntu (SSH Required)

#### Step 1: Access Server via SSH
```bash
ssh root@noteds.com
# or with your SSH key
ssh -i /path/to/key.pem root@noteds.com
```

#### Step 2: Edit Crontab
```bash
crontab -e
```

#### Step 3: Add Laravel Scheduler Entry
```bash
# Laravel scheduler entry - runs every minute to check for scheduled tasks
* * * * * cd /www/wwwroot/noteds.com && php artisan schedule:run >> /dev/null 2>&1
```

**Example crontab (after `crontab -e`):**
```bash
# Laravel Scheduler
* * * * * cd /www/wwwroot/noteds.com && php artisan schedule:run >> /dev/null 2>&1

# Optional: Additional monitoring cron (runs daily backup)
0 2 * * * cd /www/wwwroot/noteds.com && php artisan backup:run >> /dev/null 2>&1
```

#### Step 4: Verify Cron Installation
```bash
# List cron jobs
crontab -l

# Expected output should include the scheduler line
```

#### Step 5: Verify It's Working
```bash
# Check logs for scheduled task executions
tail -f /www/wwwroot/noteds.com/storage/logs/laravel.log | grep -i "schedule\|midtrans\|sync"

# Example output:
# [2025-12-13 14:00:05] local.INFO: ✅ Midtrans payment status sync completed
```

### cPanel/WHM Servers

If your server uses cPanel:

1. Login to cPanel
2. Navigate to **Cron Jobs**
3. Add new cron job:
   - **Command:** `php /home/username/public_html/noteds.com/artisan schedule:run`
   - **Time:** Every 1 minute
4. Save and verify

### Windows Servers (IIS)

Use **Task Scheduler**:

1. Open Task Scheduler
2. Create Basic Task
3. Set trigger: "Every 1 minute"
4. Set action:
   - Program: `php.exe` (full path to PHP)
   - Arguments: `C:\path\to\noteds\artisan schedule:run`
5. Save

## Testing & Verification

### Test Sync Command Manually
```bash
# Sync all pending transactions
php artisan midtrans:sync-status --all

# Expected output:
# 🔄 Found 8 pending transactions. Syncing...
# Processing: topup-1765564392-019b13d6
# 🔄 Syncing transaction: topup-1765564392-019b13d6
# Current DB Status: pending
# Midtrans Status: settlement
# Fraud Status: accept
# ✅ Transaction synced successfully!
```

### Monitor Cron Execution
```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Filter for Midtrans sync only
tail -f storage/logs/laravel.log | grep "Midtrans"

# Search for sync failures
grep "Failed\|Error" storage/logs/laravel.log
```

### Check Last Execution
```bash
# Last 10 sync attempts
tail -100 storage/logs/laravel.log | grep "sync-midtrans"
```

## How It Works

### Cron Execution Flow

```
Every Minute (Cron runs scheduler)
        ↓
Laravel Scheduler checks configured tasks
        ↓
Every 5 minutes: Execute "midtrans:sync-status --all"
        ↓
Query pending topup transactions
        ↓
Check each with Midtrans API
        ↓
If settlement found → Update DB + Credit wallet
        ↓
Log result (success/failure)
        ↓
Users see updated balance automatically
```

### Frontend Auto-Refresh

**Bonus Feature:** The wallet page now auto-refreshes every 10 seconds if you have pending transactions:

- Detects pending transactions on page load
- Polls the page for updates without page reload
- Shows toast notification when balance updates
- Automatically stops when all transactions are processed

## Troubleshooting

### Cron Not Running

**Check 1: Verify cron is installed**
```bash
crontab -l
# Should show your scheduler entry
```

**Check 2: Verify PHP path is correct**
```bash
which php
# Should return path like /usr/bin/php
```

**Check 3: Verify project path exists**
```bash
ls -la /www/wwwroot/noteds.com/
# Should show laravel project files
```

**Check 4: Verify artisan command works**
```bash
cd /www/wwwroot/noteds.com
php artisan midtrans:sync-status --all
# Should run without errors
```

### Cron Runs But Sync Fails

**Check 1: Midtrans config**
```bash
php artisan tinker
> config('services.midtrans.server_key')
# Should return server key, not null
```

**Check 2: Database permissions**
```bash
# Verify database user can update transactions
php artisan migrate:status
```

**Check 3: File permissions**
```bash
# Ensure Laravel can write to log file
chmod -R 755 storage/logs
chmod -R 755 storage/
```

## Performance Impact

- **CPU**: Minimal - each sync takes ~1-2 seconds
- **Memory**: ~50-100MB per execution
- **Database**: Light queries, optimized with indexes
- **API Calls**: ~1 per pending transaction (Midtrans rate limits: 2000/min)

## Best Practices

1. ✅ Always use absolute paths in cron commands
2. ✅ Redirect output to logs (use `>> /dev/null 2>&1`)
3. ✅ Monitor logs regularly for failures
4. ✅ Test manually before relying on automation
5. ✅ Set up email alerts for failed jobs (optional)
6. ✅ Keep timezone consistent (Asia/Jakarta)

## Email Alerts (Optional)

To get email notifications when sync fails:

```bash
# Edit Kernel.php to add notifications
$schedule->command('midtrans:sync-status --all')
    ->everyFiveMinutes()
    ->onFailure(function () {
        Mail::send('emails.sync-failed', [], function ($m) {
            $m->to('admin@noteds.com')->subject('Midtrans Sync Failed');
        });
    });
```

## Disabling Auto-Refresh

If you want to disable automatic refresh, edit `resources/views/wallet/index.blade.php` and comment out the refresh section.

## Summary

| Task | Frequency | Purpose |
|------|-----------|---------|
| Midtrans Sync | Every 5 min | Update payment status |
| Transaction Cleanup | Daily 3 AM | Remove old pending |
| Escrow Release | Daily 4 AM | Auto-release escrows |
| Subscriptions Renewal | Daily 5 AM | Renew expired subs |
| Reporting | Every 4 hours | Monitor suspicious transactions |

---

**Last Updated:** December 13, 2025  
**Documentation Version:** 1.0
