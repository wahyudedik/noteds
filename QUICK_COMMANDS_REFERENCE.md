# Quick Commands Reference

## Database Management

### Run Migrations
```bash
php artisan migrate
# or with force flag for production
php artisan migrate --force
```

### Seed Test Users
```bash
php artisan db:seed --class=TestMultiCurrencyUsersSeeder
```

### Check Migrations Status
```bash
php artisan migrate:status
```

### Rollback Migrations (if needed)
```bash
php artisan migrate:rollback
# Rollback last 5 migrations
php artisan migrate:rollback --step=5
```

---

## Testing

### View Test Plan
```bash
cat MULTI_CURRENCY_TEST_PLAN.md
```

### Test Login Credentials
```
USD User:
  Email: test.usd@example.com
  Password: password

SAR User:
  Email: test.sar@example.com
  Password: password

IDR User:
  Email: test.idr@example.com
  Password: password
```

### Database Verification Queries

#### Check USD User Transactions
```sql
SELECT user_id, type, amount, currency, original_amount, original_currency, exchange_rate 
FROM transactions 
WHERE user_id IN (SELECT id FROM users WHERE email = 'test.usd@example.com') 
ORDER BY created_at DESC;
```

#### Check SAR User Transactions
```sql
SELECT user_id, type, amount, currency, original_amount, original_currency, exchange_rate 
FROM transactions 
WHERE user_id IN (SELECT id FROM users WHERE email = 'test.sar@example.com') 
ORDER BY created_at DESC;
```

#### Check All Withdrawals
```sql
SELECT user_id, amount, currency, original_amount, original_currency, exchange_rate 
FROM withdraws 
ORDER BY created_at DESC;
```

#### Check All Affiliate Payouts
```sql
SELECT affiliate_id, amount, currency, original_amount, original_currency, exchange_rate 
FROM affiliate_payouts 
ORDER BY created_at DESC;
```

#### Verify User Wallet Currencies
```sql
SELECT id, email, name, wallet_balance, 
       (SELECT currency FROM wallets WHERE user_id = users.id) as wallet_currency 
FROM users 
WHERE email IN ('test.usd@example.com', 'test.sar@example.com', 'test.idr@example.com');
```

---

## Application Testing

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Reset Application
```bash
php artisan migrate:rollback --step=2  # Undo currency migrations
php artisan db:seed --class=TestMultiCurrencyUsersSeeder  # Recreate test users
```

### View Artisan Commands
```bash
php artisan list
```

---

## Development

### Start Development Server
```bash
php artisan serve
```

### Build Assets
```bash
npm run dev
# or
npm run build
```

---

## Verification Checklist

After deployment, run these commands:

```bash
# 1. Check migrations
php artisan migrate:status

# 2. Seed test users
php artisan db:seed --class=TestMultiCurrencyUsersSeeder

# 3. Clear cache
php artisan cache:clear

# 4. Check database (MySQL)
mysql -u <user> -p <database> < verify_currencies.sql
```

---

## Rollback Plan

If issues found:

```bash
# Step 1: Rollback migrations
php artisan migrate:rollback --step=2

# Step 2: Rollback code (git)
git reset --hard HEAD~<number of commits>

# Step 3: Re-seed if needed
php artisan db:seed --class=TestMultiCurrencyUsersSeeder
```

---

## Expected Results

### USD User (test.usd@example.com)
- Starting balance: $300 USD (5M IDR)
- After Premium: $298.50 USD
- After AI Search: ~$298.38 USD
- After Withdrawal ($10): ~$288.38 USD

### SAR User (test.sar@example.com)
- Starting balance: 1,125 SAR (5M IDR)
- After Premium: 1,119.37 SAR
- After AI Video: 1,113.74 SAR
- After Withdrawal (50 SAR): 1,063.74 SAR

### IDR User (test.idr@example.com)
- Starting balance: 5,000,000 IDR
- After Premium: 4,975,000 IDR
- After withdrawal (100,000 IDR): 4,875,000 IDR

---

## File Locations

### Key Documentation
- `MULTI_CURRENCY_TEST_PLAN.md` - Testing procedures
- `SESSION_2_COMPLETE_SUMMARY.md` - Session details
- `SESSION_2_CHANGES_LOG.md` - Changes documentation
- `NEXT_STEPS_COMPLETE.md` - This progress summary

### Code Changes
- `app/Http/Controllers/WithdrawController.php` - Withdrawal logic
- `app/Services/AiUsageService.php` - AI pricing
- `app/Services/AffiliateService.php` - Affiliate payouts
- `app/Models/Withdraw.php` - Withdraw model
- `app/Models/AffiliatePayout.php` - Payout model

### Migrations
- `database/migrations/2025_12_12_160000_add_currency_columns_to_affiliate_payouts.php`
- `database/migrations/2025_12_12_160001_add_currency_columns_to_withdraws.php`

### Seeders
- `database/seeders/TestMultiCurrencyUsersSeeder.php`

---

## Quick Status Check

```bash
# Check if migrations ran
php artisan migrate:status

# Expected output:
# 2025_12_12_160000_add_currency_columns_to_affiliate_payouts ......... Ran
# 2025_12_12_160001_add_currency_columns_to_withdraws ................ Ran

# Check if test users exist
php artisan tinker
User::where('email', 'like', 'test.%@example.com')->count();
# Expected: 3
```

---

## Deployment Checklist

- [ ] Pull latest code
- [ ] Run migrations: `php artisan migrate`
- [ ] Seed test users: `php artisan db:seed --class=TestMultiCurrencyUsersSeeder`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Verify database with SQL queries above
- [ ] Test 1-2 features manually
- [ ] Monitor transaction logs
- [ ] Check wallet balances by currency
- [ ] Verify exchange rates logged

---

## Support

**Issues Found?**
1. Check `MULTI_CURRENCY_TEST_PLAN.md` for expected behavior
2. Run SQL verification queries
3. Check application logs: `storage/logs/`
4. Review code changes in `SESSION_2_CHANGES_LOG.md`

**Need to Rollback?**
1. `php artisan migrate:rollback --step=2`
2. `git reset --hard` to previous commit
3. Test again

---

**Last Updated**: December 12, 2025
**Status**: Ready for Testing/Deployment ✅
