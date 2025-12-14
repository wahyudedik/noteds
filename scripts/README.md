# Utility Scripts Reference

Koleksi script utility untuk maintenance dan diagnostics Noteds project.

## Quick Start

```bash
# Run PHP script
php scripts/script-name.php

# Run batch script
scripts\script-name.bat

# Run shell script
bash scripts/script-name.sh
```

## Available Scripts

### Exchange Rate Management

#### `update_exchange_rates.php`
**Purpose:** Update exchange rates from provider  
**Usage:** `php scripts/update_exchange_rates.php`  
**Frequency:** Daily (via scheduler)  
**Details:** Updates rates for all configured currencies

#### `verify_exchange_rates.php`
**Purpose:** Verify exchange rate data integrity  
**Usage:** `php scripts/verify_exchange_rates.php`  
**Frequency:** Manual / Post-update  
**Checks:**
- Rate values are numeric
- Currencies exist in database
- Rates are within acceptable range

### Wallet & Balance Verification

#### `check-wallet.php`
**Purpose:** Check wallet status and balance  
**Usage:** `php scripts/check-wallet.php`  
**Displays:**
- User wallet balances
- Pending transactions
- Currency totals
- Wallet health status

#### `verify_wallet_routes.php`
**Purpose:** Verify wallet API routes are working  
**Usage:** `php scripts/verify_wallet_routes.php`  
**Checks:**
- Route registration
- Controller binding
- Permission assignments
- Response status

### User & Role Management

#### `check_user_roles.php`
**Purpose:** Check user role assignments  
**Usage:** `php scripts/check_user_roles.php`  
**Shows:**
- User role assignments
- Permission inheritance
- Access level breakdown
- Admin status verification

### Webhook & Integration Testing

#### `webhook-diagnostics.php`
**Purpose:** Diagnose webhook delivery issues  
**Usage:** `php scripts/webhook-diagnostics.php`  
**Checks:**
- Webhook endpoint status
- Delivery logs
- Failed deliveries
- Retry counts
- Response codes

#### `test-webhook.php`
**Purpose:** Send test webhook payload  
**Usage:** `php scripts/test-webhook.php`  
**Tests:**
- Webhook endpoint connectivity
- Payload structure
- Response handling
- Error messages

### Audit & Verification Scripts

#### `audit_currency_conversion.php`
**Purpose:** Audit currency conversion calculations  
**Usage:** `php scripts/audit_currency_conversion.php`  
**Audits:**
- Conversion rate accuracy
- Decimal precision
- Rounding errors
- Historical conversions

### Integration Testing

#### `test-currency-integration.bat` (Windows)
**Purpose:** Run currency integration tests  
**Usage:** `scripts\test-currency-integration.bat`  
**Runs:**
- Currency system tests
- Exchange rate tests
- Wallet conversion tests
- Transaction validation

#### `test-currency-integration.sh` (Linux/Mac)
**Purpose:** Run currency integration tests (Unix)  
**Usage:** `bash scripts/test-currency-integration.sh`  
**Same as batch version for Unix systems**

### Code Quality & Maintenance

#### `cleanup.bat`
**Purpose:** Organize project structure  
**Usage:** `scripts\cleanup.bat`  
**Functions:**
- Organize documentation files
- Create folder structure
- Categorize audit reports
- Consolidate guides

## Common Workflows

### Daily Maintenance

```bash
# Update exchange rates
php scripts/update_exchange_rates.php

# Verify exchange rates
php scripts/verify_exchange_rates.php

# Check wallet status
php scripts/check-wallet.php
```

### Troubleshooting

```bash
# Check webhook issues
php scripts/webhook-diagnostics.php

# Verify wallet routes
php scripts/verify_wallet_routes.php

# Check user roles
php scripts/check_user_roles.php

# Audit conversions
php scripts/audit_currency_conversion.php
```

### Pre-Deployment

```bash
# Run all integration tests
scripts\test-currency-integration.bat  # Windows

bash scripts/test-currency-integration.sh  # Linux/Mac
```

## Script Dependencies

| Script | Requires | Optional |
|--------|----------|----------|
| update_exchange_rates.php | Laravel | Rate API key |
| verify_exchange_rates.php | Laravel, Database | - |
| check-wallet.php | Laravel, Database | User ID |
| verify_wallet_routes.php | Laravel, Routes | - |
| check_user_roles.php | Laravel, Spatie Permissions | User ID |
| webhook-diagnostics.php | Laravel, Webhook logs | - |
| test-webhook.php | cURL, HTTP | Test endpoint |
| audit_currency_conversion.php | Laravel, Database | Date range |

## Running Scripts Programmatically

### In Laravel Tinker

```php
php artisan tinker

// Update exchange rates
include 'scripts/update_exchange_rates.php';

// Check wallet
include 'scripts/check-wallet.php';

// Verify routes
include 'scripts/verify_wallet_routes.php';
```

### In Scheduled Tasks

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        shell_exec(base_path('scripts/update_exchange_rates.php'));
    })->daily();
}
```

## Output & Logging

### Script Output

Scripts output results to:
- **Console/stdout** - Direct execution
- **Laravel logs** - Via Log::info()
- **Database logs** - Transaction logs
- **Webhook logs** - Delivery logs

### Viewing Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View webhook logs
php artisan tinker
App\Models\WebhookLog::latest()->get();

# View error logs
grep -i error storage/logs/laravel.log
```

## Adding New Scripts

### Template

```php
<?php

/**
 * Script Name: Example Script
 * Purpose: What this script does
 * Usage: php scripts/example.php [options]
 * Created: YYYY-MM-DD
 * Last Updated: YYYY-MM-DD
 */

// Check if running from CLI
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

// Load Laravel
require __DIR__ . '/../bootstrap/app.php';

// Your script code here
echo "Script executed successfully.\n";
?>
```

### Best Practices

1. ✅ Add header comments
2. ✅ Check CLI execution
3. ✅ Load Laravel bootstrap
4. ✅ Add error handling
5. ✅ Log important actions
6. ✅ Return exit codes (0 success, 1 failure)
7. ✅ Document in this README

## Troubleshooting

### Script Not Found

```bash
# Make sure you're in project root
cd /path/to/noteds

# Check script exists
ls -la scripts/script-name.php
```

### Permission Denied

```bash
# On Linux/Mac, make executable
chmod +x scripts/script-name.sh

# Run with PHP directly
php scripts/script-name.php
```

### Script Hangs

```bash
# Check database connection
php artisan tinker
DB::connection()->getPdo();

# Check for long-running queries
php scripts/verify_exchange_rates.php --verbose
```

### Laravel Not Found

```bash
# Make sure bootstrap path is correct
php -r "require 'bootstrap/app.php'; echo 'OK';"

# Check autoloader
composer dump-autoload
```

## Maintenance Schedule

| Task | Frequency | Script |
|------|-----------|--------|
| Update exchange rates | Daily | update_exchange_rates.php |
| Verify exchange rates | Weekly | verify_exchange_rates.php |
| Check wallet health | Weekly | check-wallet.php |
| Webhook diagnostics | Monthly | webhook-diagnostics.php |
| Currency audit | Monthly | audit_currency_conversion.php |
| Full integration test | Pre-deployment | test-currency-integration.bat |

---

**Scripts Directory:** `scripts/`  
**Total Scripts:** 10+  
**Last Updated:** December 13, 2025  
**Maintained By:** Development Team
