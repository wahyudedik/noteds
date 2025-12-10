# Production Fix: Missing `note_share_user_trackings` Table

## Error Summary
**Location:** Production at `noteds.com`  
**Date:** December 10, 2025  
**Error Type:** `Illuminate\Database\QueryException`

### Error Message
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'sql_noteds_com.note_share_user_trackings' doesn't exist
```

### Error Stack
- **File:** `app/Services/NoteShareService.php:88`
- **Function:** `trackAndValidateShareCount()`
- **Route:** `GET /marketplace/{note}` → `MarketplaceController@show`
- **Method:** Attempting to query non-existent table

---

## Root Cause

### The Problem
There was a **table naming mismatch** between:
1. **Migration file:** Creates table named `note_share_user_tracking` (singular)
   - File: `database/migrations/2025_12_08_000001_create_note_share_user_tracking_table.php`
   - SQL: `Schema::create('note_share_user_tracking', ...)`

2. **Eloquent Model:** Defaults to `note_share_user_trackings` (plural)
   - File: `app/Models/NoteShareUserTracking.php`
   - Laravel auto-pluralizes: `NoteShareUserTracking` → `note_share_user_trackings`
   - Model didn't specify `protected $table = 'note_share_user_tracking'`

### When It Occurs
When viewing a marketplace note (`/marketplace/{id}`):
1. MarketplaceController calls NoteShareService::recordShare()
2. NoteShareService tries to track the share using NoteShareUserTracking model
3. Model queries table `note_share_user_trackings` (doesn't exist)
4. Database throws QueryException for non-existent table

---

## Solution Applied

### Code Change
**File:** `app/Models/NoteShareUserTracking.php`

**Before:**
```php
class NoteShareUserTracking extends Model
{
    use HasUuids;

    protected $fillable = [
        'share_referral_id',
        'user_id',
        'share_count',
    ];
```

**After:**
```php
class NoteShareUserTracking extends Model
{
    use HasUuids;

    protected $table = 'note_share_user_tracking';

    protected $fillable = [
        'share_referral_id',
        'user_id',
        'share_count',
    ];
```

### Commit
- **Hash:** `6bd1e20`
- **Message:** "Fix: Set correct table name in NoteShareUserTracking model to match migration"
- **Branch:** `main`

---

## Deployment Steps for Production

### Step 1: Pull Latest Code
```bash
cd /path/to/noteds
git pull origin main
```
This will include the model fix from commit `6bd1e20`.

### Step 2: Run Pending Migrations
```bash
php artisan migrate --force
```

This will execute the migration that creates the `note_share_user_tracking` table:
- **Migration:** `2025_12_08_000001_create_note_share_user_tracking_table.php`
- **Creates:**
  - Table: `note_share_user_tracking`
  - Columns: `id`, `share_referral_id`, `user_id`, `share_count`, `created_at`, `updated_at`
  - Foreign Key: `share_referral_id` → `note_share_referrals.id` (ON DELETE CASCADE)
  - Unique Index: `(share_referral_id, user_id)`

### Step 3: Verify Table Exists
```bash
# MySQL - Check table
mysql -h your_db_host -u your_user -p your_db_name -e "SHOW TABLES LIKE 'note_share_user_tracking';"

# Output should be:
# | Tables_in_your_db_name |
# | note_share_user_tracking |
```

### Step 4: Clear Application Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Step 5: Monitor Logs
```bash
tail -f storage/logs/laravel.log
```

---

## Verification Checklist

After deployment, verify the fix:

- [ ] Code deployed from commit `6bd1e20`
- [ ] Migration `2025_12_08_000001_create_note_share_user_tracking_table.php` has run
- [ ] Table `note_share_user_tracking` exists in production database
- [ ] Application cache cleared
- [ ] Can view marketplace notes without "table not found" error
- [ ] Share tracking functionality works (user can share notes)
- [ ] No new errors in application logs

---

## Testing the Fix

### Manual Test
1. Open a marketplace note: `https://noteds.com/marketplace/{note-id}`
2. Expected result: Page loads without error
3. Previous error: `Internal Server Error - Table 'note_share_user_trackings' doesn't exist`

### Automated Test
```bash
php artisan tinker
>>> $table = DB::table('note_share_user_tracking')->first();
>>> $table // Should return null or a record, not error
```

---

## Impact Assessment

### Affected Features
- **Marketplace Note Viewing:** Cannot view notes (main feature impacted)
- **Share Tracking:** Cannot track user shares of notes
- **Share Analytics:** Share statistics cannot be recorded

### Severity
- **Critical:** Blocks marketplace functionality for all users trying to view notes

### Users Affected
- All users accessing `/marketplace/{note-id}` route
- All traffic to marketplace feature broken

---

## Related Files

### Modified
- `app/Models/NoteShareUserTracking.php` - Added `protected $table = 'note_share_user_tracking'`

### Not Modified (Already Correct)
- `database/migrations/2025_12_08_000001_create_note_share_user_tracking_table.php`
- `app/Services/NoteShareService.php`
- `app/Http/Controllers/MarketplaceController.php`

### Related Migrations
- `2025_12_08_000002_create_note_share_commissions_table.php`
- `2025_11_18_130527_create_note_share_referrals_table.php`
- `2025_11_18_130602_create_note_share_purchases_table.php`

---

## Prevention

### For Future Development
1. **Always explicitly set table names** if they don't follow Laravel's pluralization convention
2. **Run migrations after pulling code** to ensure database schema stays in sync
3. **Test database migrations** on staging before production deployment
4. **Use schema:dump** to backup schema after migrations
5. **Monitor migration status** with `php artisan migrate:status`

### Schema Validation
```bash
# Check if all models match their tables
php artisan model:show NoteShareUserTracking
```

---

## Additional Notes

- The migration file was created on **December 8, 2025** but had not been run in production
- The model was created without explicitly setting the table name, relying on Laravel's default pluralization
- This is a common issue when migrations are created but not immediately deployed
- The fix is minimal (1 line) and has zero side effects

---

## Questions?

If you encounter any issues during deployment:
1. Check migration status: `php artisan migrate:status`
2. Verify table exists: `SHOW TABLES LIKE 'note_share_user_tracking'`
3. Check model mapping: `php artisan tinker` → `NoteShareUserTracking::query()->getQuery()->from`
4. Review recent logs: `tail -100 storage/logs/laravel.log`
