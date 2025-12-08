# Next Steps - Task 8: End-to-End Testing & Deployment

## Quick Reference Commands

### 1. Run Database Seeder (Setup Permissions)
```bash
cd d:\PROJECT\LARAVEL\noteds
php artisan db:seed --class=AffiliatePermissionSeeder
```

### 2. Test Affiliate Link Creation
```bash
# Access admin dashboard → Create affiliate account
# Or use Tinker:
php artisan tinker
> $user = User::where('role', 'seller')->first();
> App\Services\AffiliateService::generateAffiliateLink($user, 'Test Link', 'Desc', null);
```

### 3. Test Job Scheduling (Manual Trigger)
```bash
# Run scheduler once (useful for testing)
php artisan schedule:run

# Or manually execute the job
php artisan tinker
> $job = new App\Jobs\ProcessMonthlyAffiliatePayoutJob();
> dispatch($job);

# Monitor queue
php artisan queue:work  # Run in separate terminal
```

### 4. Test Event Broadcasting
```bash
# Check event log in storage/logs/laravel.log
tail -f storage/logs/laravel.log | grep -i "affiliate"

# Or use Telescope (if enabled)
# Visit: http://localhost:8000/telescope
```

### 5. Test Email Notifications
```bash
# View queued emails in log driver
# Or connect to Redis/Database queue driver and inspect

# For local testing, use:
MAIL_DRIVER=log  # .env file
# Emails will be logged to storage/logs/laravel.log

# For full SMTP testing:
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
# (Configure your mailtrap credentials)
```

### 6. Check Database State
```bash
php artisan tinker
> App\Models\AffiliateLink::count()           # Total links
> App\Models\AffiliateConversion::count()     # Total conversions
> App\Models\AffiliateCommission::count()     # Total commissions
> App\Models\AffiliatePayout::count()         # Total payouts
> App\Models\Permission::where('name', 'like', 'affiliate%')->get()  # Check permissions
```

---

## Manual Test Workflow

### Step 1: Create Test Users
```bash
php artisan tinker
> $seller = User::factory()->create(['role' => 'seller']);
> $buyer = User::factory()->create(['role' => 'buyer']);
```

### Step 2: Create Affiliate Link
```bash
# Through UI: Visit /affiliate/index and create link
# Or via API: POST /api/affiliate-links

# Via Tinker:
> $service = new App\Services\AffiliateService();
> $link = $service->generateAffiliateLink($seller, 'Test Link', 'Description', null);
> echo $link->full_url;  // Should show clickable link
```

### Step 3: Simulate Click & Conversion
```bash
> // Track click
> $service->trackClick($link->code);

> // Simulate purchase conversion
> $transaction = Transaction::factory()->create(['amount' => 100000]);
> $conversion = $service->trackConversion($buyer, 'purchase', $transaction, null);

> // Check created commission
> $commission = $conversion->commissions()->where('tier', 1)->first();
> echo "Commission: " . $commission->commission_amount;  // Should show calculated amount
```

### Step 4: Test Payout Request
```bash
> // Create payout request
> $payout = $service->createPayoutRequest($seller, 50000, 'wallet');

> // Check broadcast event was fired
> // Check in logs: "AffiliatePayoutRequested event broadcast"

> // Verify mail was queued
> Job::assertPushed(SendQueuedMailable::class);
```

### Step 5: Test Admin Dashboard
```bash
# 1. Log in as admin
# 2. Go to /admin/dashboard
# 3. Verify Affiliate Analytics section shows:
#    - Total Affiliates: 1+
#    - Active Links: 1+
#    - Total Conversions: 1+
#    - Total Commissions: 50000+
#    - Pending Payouts: 50000+
#    - Completed Payouts: 0 (until job runs)

# 4. Verify Top Affiliates table shows seller
# 5. Verify Pending Payouts table shows payout request
```

### Step 6: Test Scheduled Job
```bash
# Method 1: Trigger scheduler
php artisan schedule:run

# Method 2: Manually dispatch job
php artisan tinker
> $job = new App\Jobs\ProcessMonthlyAffiliatePayoutJob();
> $job->handle();

# Method 3: Check if job is registered
php artisan schedule:list | grep affiliate
```

### Step 7: Verify Payout Processing
```bash
php artisan tinker
> $payout = AffiliatePayout::where('status', 'pending')->first();
> echo "Before: " . $payout->status;  // Should be 'pending'

# (Run job)

> $payout->refresh();
> echo "After: " . $payout->status;   // Should be 'completed'

> // Check affiliate wallet was updated
> $seller->wallet->refresh();
> echo "Seller balance: " . $seller->wallet->balance;
```

---

## Testing Checklist

### Database & Permissions
- [ ] AffiliatePermissionSeeder runs without errors
- [ ] 4 permissions created in database
- [ ] Seller and buyer roles have affiliate permissions
- [ ] Admin role can view all features

### Affiliate Link Management
- [ ] Can create new affiliate link
- [ ] Link has unique code
- [ ] Can edit link name/description
- [ ] Can toggle active/inactive
- [ ] Can delete link
- [ ] Full URL accessible and redirects correctly

### Conversion & Commission Tracking
- [ ] Click counter increments
- [ ] Conversion created on purchase
- [ ] Commission calculated correctly for tier 1
- [ ] Commission status is 'pending'
- [ ] Multi-tier commissions work (if applicable)

### Payout System
- [ ] Can request payout
- [ ] Payout created with 'pending' status
- [ ] Amount matches pending balance
- [ ] Can specify payout method
- [ ] Cannot request more than available balance

### Notifications
- [ ] AffiliateConversionCompleted event fires
- [ ] AffiliateConversionMail queued to affiliate
- [ ] AffiliatePayoutRequested event fires
- [ ] AffiliatePayoutRequestMail queued to all admins
- [ ] Emails appear in log (MAIL_DRIVER=log)

### Admin Dashboard
- [ ] Affiliate Analytics section visible
- [ ] All 6 metric cards display correctly
- [ ] Top affiliates table shows data
- [ ] Pending payouts table shows data
- [ ] Charts/numbers update after new conversion

### Scheduled Jobs
- [ ] Job is listed: `php artisan schedule:list`
- [ ] Job runs at correct time (monthly, day/time config)
- [ ] Pending payouts marked as 'completed'
- [ ] Affiliate wallet balance updated
- [ ] No errors in logs

### Sidebar Navigation
- [ ] Admin can see "Affiliate Settings" link in sidebar
- [ ] Link is properly highlighted when on settings page
- [ ] Icon displays correctly
- [ ] Works for all languages (EN/ID/AR)

---

## Common Issues & Troubleshooting

### Issue: Permissions not working
**Solution:**
```bash
# Clear permission cache
php artisan cache:clear

# Re-run seeder
php artisan db:seed --class=AffiliatePermissionSeeder
```

### Issue: Emails not sending
**Solution:**
```bash
# Check mail driver
php artisan tinker
> echo config('mail.driver');  // Should be 'log' or 'smtp'

# Check queue
php artisan queue:work  # Start queue worker
php artisan queue:failed  # Check failed jobs
```

### Issue: Job not running on schedule
**Solution:**
```bash
# Verify job exists
php artisan schedule:list | grep affiliate

# Test job manually
php artisan tinker
> Artisan::call('schedule:run');

# Check cron setup (production)
# Add to crontab: * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Issue: Commission not calculated
**Solution:**
```bash
# Check commission tier rates
php artisan tinker
> $service = new App\Services\AffiliateService();
> echo $service->getCommissionRate($user, 1);  // Should return percentage

# Verify transaction amount
> Transaction::find($transactionId)->amount;
```

### Issue: Dashboard analytics showing 0
**Solution:**
```bash
# Clear dashboard cache
Redis::flushdb();  // If using Redis

# Verify data exists
php artisan tinker
> AffiliateLink::count()
> AffiliateConversion::count()
```

---

## Performance Monitoring

### Check Queue Performance
```bash
# View job count in queue
php artisan queue:size

# Monitor specific queue
php artisan queue:work --queue=default

# Failed jobs
php artisan queue:failed
```

### Check Database Queries
```bash
# Enable query logging in storage/logs/laravel.log
// In .env:
APP_DEBUG=true

// Monitor in tinker:
DB::enableQueryLog();
// ... run code ...
dd(DB::getQueryLog());
```

### Monitor Scheduled Jobs
```bash
# Check schedule log
tail -f storage/logs/laravel.log | grep -i schedule

# Get next scheduled run times
php artisan schedule:list

# Run schedule with detailed output
php artisan schedule:run -v
```

---

## Deployment Checklist

### Before Production
- [ ] All tests pass
- [ ] Code reviewed
- [ ] Migrations tested in staging
- [ ] Scheduler configured in crontab
- [ ] Email credentials configured
- [ ] Queue worker configured (supervisor/systemd)
- [ ] Database backups configured
- [ ] Logs monitored and alerting set up

### Deployment Steps
```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install

# 3. Run migrations
php artisan migrate

# 4. Seed permissions (if first time)
php artisan db:seed --class=AffiliatePermissionSeeder

# 5. Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# 6. Restart queue workers
# (Handled by supervisor/systemd config)

# 7. Verify scheduler
php artisan schedule:list | grep affiliate
```

### Post-Deployment
- [ ] Monitor logs for errors
- [ ] Test affiliate link creation
- [ ] Test conversion tracking
- [ ] Verify admin dashboard displays data
- [ ] Monitor job execution
- [ ] Check email delivery

---

## Documentation Links

- Affiliate Feature Docs: `AFFILIATE_IMPLEMENTATION_SUMMARY.md`
- Completion Summary: `AFFILIATE_COMPLETION_SUMMARY.md`
- Feature Requirements: `FITUR.md`

---

**Status**: Ready for Task 8 execution  
**Estimated Testing Time**: 1-2 hours  
**Deployment Risk**: Low (feature isolated, no existing changes)
