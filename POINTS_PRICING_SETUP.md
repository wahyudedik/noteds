# Points Pricing System - Setup & Deployment Guide

---

## Quick Start (5 Minutes)

### Step 1: Verify Installation
```bash
cd d:\PROJECT\LARAVEL\noteds
php artisan migrate --step  # If not already run
php artisan tinker
> Schema::hasTable('points_pricing_config')
> exit()
```

Expected output: `true`

### Step 2: Create First Admin User
If you don't have an admin user yet:

```bash
php artisan tinker
> $user = User::create(['email' => 'admin@noteds.test', 'name' => 'Admin', 'password' => bcrypt('password')])
> $user->assignRole('admin')
> exit()
```

### Step 3: Access Admin Panel
1. Login to `http://noteds.test/admin`
2. Look for pink "Points Pricing" card in Quick Links
3. Click "Add New Pricing Option"
4. Create your first pricing configuration

---

## Installation Verification Checklist

- [ ] Database table `points_pricing_config` exists
- [ ] Migration file: `database/migrations/2025_12_07_035519_create_points_pricing_config_table.php`
- [ ] Model file: `app/Models/PointsPricingConfig.php`
- [ ] Controller file: `app/Http/Controllers/Admin/PointsPricingController.php`
- [ ] View files in: `resources/views/admin/points-pricing/`
  - [ ] `index.blade.php`
  - [ ] `create.blade.php`
  - [ ] `edit.blade.php`
  - [ ] `monitoring.blade.php`
- [ ] Routes added to: `routes/web.php` (lines 700-704)
- [ ] Dashboard updated: `resources/views/admin/dashboard.blade.php`
- [ ] Test file: `tests/Feature/PointsPricingTest.php`
- [ ] Documentation files:
  - [ ] `POINTS_PRICING_FEATURE.md`
  - [ ] `POINTS_PRICING_API.md`
  - [ ] This file

---

## File Structure

```
app/
├── Http/Controllers/Admin/
│   └── PointsPricingController.php      ← Main controller
├── Models/
│   └── PointsPricingConfig.php          ← Main model
database/
├── migrations/
│   └── 2025_12_07_035519_*.php          ← Database schema
resources/views/
└── admin/points-pricing/
    ├── index.blade.php                  ← List view
    ├── create.blade.php                 ← Create form
    ├── edit.blade.php                   ← Edit form
    └── monitoring.blade.php             ← Monitoring dashboard
routes/
└── web.php                              ← Routes (lines 700-704)
tests/Feature/
└── PointsPricingTest.php                ← Test cases
```

---

## Configuration Examples

### Minimal Setup (For Testing)
```php
// Create via admin panel or artisan

PointsPricingConfig::create([
    'name' => 'Test Discount',
    'type' => 'discount',
    'points_required' => 100,
    'discount_percent' => 5,
    'is_active' => true,
    // All limits nullable for unlimited
]);
```

### Conservative Setup (Recommended for Production)
```php
// Discount offer
PointsPricingConfig::create([
    'name' => '5% Discount',
    'type' => 'discount',
    'points_required' => 500,
    'discount_percent' => 5,
    'daily_limit' => 50,      // Max 50 redemptions/day
    'user_limit' => 1,        // Each user max 1/day
    'is_active' => true,
    'description' => 'Get 5% off on purchases',
]);

// Premium feature
PointsPricingConfig::create([
    'name' => 'Premium 7 Days',
    'type' => 'premium_feature',
    'points_required' => 1000,
    'premium_days' => 7,
    'daily_limit' => 20,
    'user_limit' => 1,
    'is_active' => true,
]);
```

### Promotional Setup (Time Limited)
```php
PointsPricingConfig::create([
    'name' => 'Holiday Bonus: 20% Off',
    'type' => 'discount',
    'points_required' => 200,
    'discount_percent' => 20,
    'daily_limit' => 100,
    'user_limit' => 3,
    'expires_at' => Carbon::parse('2025-12-31'),
    'is_active' => true,
    'description' => 'Limited time holiday offer - expires Dec 31',
]);
```

---

## Initial Data Setup

### Via Admin Panel (Recommended)

1. **Navigate:** `http://noteds.test/admin/points-pricing`
2. **Click:** "Add New Pricing Option"
3. **Fill Form:**
   - Name: "5% Discount"
   - Type: "Discount"
   - Points Required: 500
   - Discount Percent: 5
   - Daily Limit: 50
   - User Limit: 1
   - Active: ✓ Checked
4. **Save:** System creates configuration
5. **Repeat:** For additional offers

### Via Artisan Tinker

```bash
php artisan tinker
```

```php
use App\Models\PointsPricingConfig;

// Create discount
PointsPricingConfig::create([
    'name' => '5% Discount',
    'type' => 'discount',
    'points_required' => 500,
    'discount_percent' => 5,
    'daily_limit' => 50,
    'user_limit' => 1,
    'is_active' => true,
]);

// Create premium feature
PointsPricingConfig::create([
    'name' => 'Premium 30 Days',
    'type' => 'premium_feature',
    'points_required' => 1000,
    'premium_days' => 30,
    'daily_limit' => 10,
    'user_limit' => 1,
    'is_active' => true,
]);

exit()
```

### Via Database Seeder

Create `database/seeders/PointsPricingSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\PointsPricingConfig;
use Illuminate\Database\Seeder;

class PointsPricingSeeder extends Seeder
{
    public function run(): void
    {
        PointsPricingConfig::create([
            'name' => '5% Discount',
            'type' => 'discount',
            'points_required' => 500,
            'discount_percent' => 5,
            'daily_limit' => 50,
            'user_limit' => 1,
            'is_active' => true,
        ]);

        PointsPricingConfig::create([
            'name' => 'Premium 7 Days',
            'type' => 'premium_feature',
            'points_required' => 1000,
            'premium_days' => 7,
            'daily_limit' => 20,
            'user_limit' => 1,
            'is_active' => true,
        ]);
    }
}
```

Run seeder:
```bash
php artisan db:seed --class=PointsPricingSeeder
```

---

## Testing

### Run All Tests
```bash
php artisan test tests/Feature/PointsPricingTest.php
```

### Run Specific Test
```bash
php artisan test tests/Feature/PointsPricingTest.php --filter=test_admin_can_create_pricing_configuration
```

### Run with Coverage
```bash
php artisan test tests/Feature/PointsPricingTest.php --coverage
```

### Test Checklist
- [ ] List pricing configurations
- [ ] Create new configuration
- [ ] Edit existing configuration
- [ ] Delete configuration
- [ ] Toggle active status
- [ ] View monitoring dashboard
- [ ] Filter by date range
- [ ] Export CSV report
- [ ] Verify daily limits work
- [ ] Verify user limits work
- [ ] Form validation works

---

## Deployment to Production

### Pre-Deployment Checklist

- [ ] Run tests: `php artisan test`
- [ ] Check code style: `php artisan pint --check`
- [ ] Run static analysis: `phpstan analyse app`
- [ ] Verify migrations: `php artisan migrate --dry-run`
- [ ] Clear caches: `php artisan cache:clear`
- [ ] Backup database: [Manual backup]

### Deployment Steps

1. **Pull Latest Code**
```bash
git pull origin main
```

2. **Install/Update Dependencies**
```bash
composer install --no-dev --optimize-autoloader
```

3. **Run Migrations**
```bash
php artisan migrate --force
```

4. **Clear Caches**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

5. **Optimize for Production**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

6. **Verify Deployment**
```bash
php artisan tinker
> Schema::hasTable('points_pricing_config')
> PointsPricingConfig::count()
> exit()
```

---

## Configuration Settings

### Environment Variables (if needed)

Add to `.env`:
```
POINTS_PRICING_ENABLE=true
POINTS_PRICING_DAILY_LIMIT=100
POINTS_PRICING_USER_LIMIT=5
POINTS_PRICING_MAX_EXPIRATION_DAYS=90
```

Use in controller:
```php
$dailyLimit = config('points-pricing.daily_limit', 100);
```

### Database Connection

Ensure your database connection is working:

```bash
php artisan tinker
> DB::connection()->getPdo()
```

---

## Monitoring & Maintenance

### Daily Tasks
1. **Check Dashboard:** `http://noteds.test/admin/points-monitoring`
2. **Review Statistics:** Today's count, points used
3. **Monitor Anomalies:** Any unusual patterns?

### Weekly Tasks
1. **Export Report:** `http://noteds.test/admin/points-redemption/export`
2. **Analyze Trends:** Week-over-week comparison
3. **Adjust Limits:** If needed based on data

### Monthly Tasks
1. **Review Offers:** Still appropriate?
2. **Disable Old Promotions:** Clean up expired offers
3. **Plan New Offers:** Based on performance

### Backup Important Reports
```bash
# Export and archive monthly reports
php artisan tinker
> PointRedemption::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
>   ->select('user_id', 'points', 'value', 'status', 'created_at')
>   ->export()
```

---

## Performance Optimization

### Database Optimization
```bash
# Analyze tables
php artisan tinker
> DB::statement('ANALYZE TABLE points_pricing_config')
> DB::statement('ANALYZE TABLE point_redemptions')
```

### Caching Strategy
```php
// Cache active pricing options (5 minutes)
$options = Cache::remember('points_pricing_active', 300, function () {
    return PointsPricingConfig::getActiveOptions();
});
```

### Query Optimization
The model already includes:
- Proper indexes on: type, is_active, points_required
- Eager loading where needed
- Efficient query scopes

---

## Troubleshooting

### Issue: Table Not Found
```
SQLSTATE[42S02]: Table 'noteds.points_pricing_config' doesn't exist
```

**Solution:**
```bash
php artisan migrate
# OR check if migration ran
php artisan migrate:status
```

### Issue: Unauthorized Access
```
Session has expired
```

**Solution:**
1. Login again
2. Verify admin role: `$user->hasRole('admin')`
3. Check middleware in routes/web.php

### Issue: CSV Export Not Working
```
Failed to download report
```

**Solution:**
1. Check file permissions: `ls -la storage/logs/`
2. Verify disk configuration in config/filesystems.php
3. Check available disk space

### Issue: Forms Not Saving
```
Validation errors appear
```

**Solution:**
1. Check form data matches validation rules
2. Verify CSRF token in form
3. Check Laravel logs for detailed error

### Issue: Limits Not Enforcing
```
Users can redeem more than limit
```

**Solution:**
1. Verify `daily_limit` and `user_limit` fields are not NULL
2. Check that validation is being called before redemption
3. Verify `isDailyLimitReached()` method is implemented

---

## Security Considerations

### Access Control
✅ Admin role required  
✅ CSRF protection enabled  
✅ Input validation on all forms  
✅ SQL injection prevention via Eloquent  

### Data Protection
✅ UUIDs instead of sequential IDs  
✅ Timestamps for audit trail  
✅ No sensitive data in exports  
✅ Proper authorization checks  

### Best Practices
- [ ] Use HTTPS in production
- [ ] Enable two-factor authentication for admin users
- [ ] Regular database backups
- [ ] Monitor access logs
- [ ] Keep Laravel updated

---

## Rollback Procedure

If you need to rollback the feature:

```bash
# Rollback migration
php artisan migrate:rollback --step=1

# Remove files
rm app/Models/PointsPricingConfig.php
rm app/Http/Controllers/Admin/PointsPricingController.php
rm -rf resources/views/admin/points-pricing/
rm tests/Feature/PointsPricingTest.php

# Revert routes/web.php (remove lines 700-704)
# Revert resources/views/admin/dashboard.blade.php (remove Points Pricing card)
```

---

## Support & Contact

For issues or questions:

1. **Check Documentation:**
   - `POINTS_PRICING_FEATURE.md` - Full feature documentation
   - `POINTS_PRICING_API.md` - API reference
   - This file - Setup guide

2. **Review Test Cases:**
   - `tests/Feature/PointsPricingTest.php` - Working examples

3. **Check Logs:**
   - `storage/logs/laravel.log` - Detailed error messages

4. **Contact Development Team:**
   - Email: [team email]
   - Slack: #noteds-support

---

## Next Steps

1. **Immediate (Today):**
   - [ ] Create initial pricing configurations
   - [ ] Test in development environment
   - [ ] Verify admin can access dashboard

2. **This Week:**
   - [ ] Deploy to staging
   - [ ] Run full test suite
   - [ ] Verify all features work
   - [ ] Get stakeholder approval

3. **Next Week:**
   - [ ] Deploy to production
   - [ ] Monitor closely first 24 hours
   - [ ] Gather user feedback
   - [ ] Adjust configurations if needed

4. **Ongoing:**
   - [ ] Daily monitoring of redemptions
   - [ ] Weekly analysis of patterns
   - [ ] Monthly reviews and adjustments
   - [ ] Quarterly comprehensive audits

---

**Last Updated:** December 7, 2025  
**Version:** 1.0  
**Status:** Production Ready ✅
