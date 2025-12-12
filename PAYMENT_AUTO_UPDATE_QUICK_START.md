# Payment Auto-Update Implementation - Quick Start

## ✅ What's Done

You now have **TWO-LAYER automatic payment status update**:

### Layer 1: Wallet Page Auto-Refresh (Frontend)
- **When**: As soon as you topup and visit wallet page
- **How**: Page checks for pending transactions every 10 seconds
- **What happens**: Balance updates automatically without page reload
- **Status**: ✅ Live immediately - no setup needed

### Layer 2: Backend Cron Job (Server-side)
- **When**: Every 5 minutes automatically
- **How**: Server syncs all pending topups with Midtrans API
- **What happens**: DB updated → wallet credited → notification sent
- **Status**: Requires server cron setup (see below)

---

## 🚀 Quick Setup (3 Steps)

### Step 1: SSH into Server
```bash
ssh root@noteds.com
```

### Step 2: Open Crontab
```bash
crontab -e
```

### Step 3: Add This Line
```bash
* * * * * cd /www/wwwroot/noteds.com && php artisan schedule:run >> /dev/null 2>&1
```

That's it! Save and exit.

---

## ✨ How It Works Now

### Scenario: User Topups Rp 10.000

**Before (Manual):**
1. User topups → status: pending
2. Payment confirmed in Midtrans
3. App still shows "Pending" 😞
4. User runs: `php artisan midtrans:sync-status --all`
5. Balance finally updates ✅

**After (Automatic):**
1. User topups → status: pending
2. User sees wallet page
3. Page auto-refreshes every 10 seconds (no page reload needed)
4. Payment confirmed in Midtrans
5. Cron job (every 5 min) syncs status
6. Within ~15 seconds: Balance updates automatically ✅
7. User sees toast: "Wallet balance updated!"

---

## 🔍 Verify It's Working

### Test 1: Manual Sync (Immediate)
```bash
php artisan midtrans:sync-status --all
```
Should see success messages.

### Test 2: Check Cron is Installed
```bash
crontab -l
```
Should show your scheduler entry.

### Test 3: Monitor Live Logs
```bash
tail -f storage/logs/laravel.log | grep -i "midtrans\|sync"
```
Should see sync logs every 5 minutes.

---

## 📋 Schedule Overview

| Task | When | What It Does |
|------|------|-------------|
| **Midtrans Sync** | Every 5 min | Updates pending topups → credits wallet |
| **Transaction Cleanup** | Daily 3 AM | Removes old pending (>1 day) |
| **Escrow Release** | Daily 4 AM | Auto-releases escrows |
| **Subscriptions** | Daily 5 AM | Renews expired subscriptions |
| **Reports** | Every 4 hours | Checks for suspicious transactions |

---

## 📱 Frontend Features

### Automatic Refresh on Wallet Page
✅ Detects pending transactions  
✅ Polls every 10 seconds  
✅ Updates balance without reload  
✅ Shows "Wallet balance updated!" toast  
✅ Stops when no pending transactions  

### How to Test
1. Open wallet page in 2 browser tabs
2. Click "Topup" in first tab
3. Complete Midtrans payment (use test card)
4. Switch to second tab
5. Should see auto-refresh working in 10 seconds ✅

---

## ⚠️ Troubleshooting

### Cron Not Running?

**Check 1**: Verify PHP path
```bash
which php
# Copy this path, you might need it
```

**Check 2**: Test manually
```bash
cd /www/wwwroot/noteds.com && php artisan midtrans:sync-status --all
```

**Check 3**: Check logs
```bash
tail -20 storage/logs/laravel.log
```

### Page Not Auto-Refreshing?

**Check 1**: Open browser console (F12)
- Should see: "Pending transactions detected. Auto-refresh enabled"

**Check 2**: Make sure you have pending transactions
- Status shows "Pending" in yellow

**Check 3**: Check network tab
- Should see GET requests every 10 seconds

---

## 🔐 Security

✅ Webhook signature verification (SHA512)  
✅ CSRF protection maintained  
✅ Database locking prevents race conditions  
✅ Idempotent operations (safe to run multiple times)  

---

## 📊 What Gets Logged

Every sync attempt logs to `storage/logs/laravel.log`:

```
[2025-12-13 14:05:00] local.INFO: ✅ Midtrans payment status sync completed
[2025-12-13 14:05:00] local.INFO: Sync Midtrans Payment Status - Success {"transaction_id":123,"order_id":"topup-1765564392-019b13d6","midtrans_status":"settlement","amount":10000}
```

You can view with:
```bash
tail -f storage/logs/laravel.log
```

---

## 🎯 Next Steps

1. **Add cron entry** (instructions above) - takes 2 minutes
2. **Test manually**: `php artisan midtrans:sync-status --all`
3. **Check logs**: `tail -f storage/logs/laravel.log`
4. **Verify**: Do test topup and watch it auto-update

---

## 📚 Full Documentation

For detailed setup on different server types (cPanel, Windows, etc.), see:
→ `CRON_JOB_SETUP.md`

---

**Status**: ✅ Ready to Deploy  
**Date**: December 13, 2025  
**Version**: 1.0
