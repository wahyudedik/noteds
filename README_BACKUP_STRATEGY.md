# Backup Strategy for Clipper System

## Critical Data to Backup

### High Priority (Daily Backup)
- `ledger_entries` - All financial transactions (immutable, critical)
- `audit_logs` - All admin actions
- `campaigns` - Campaign data
- `clips` - Clip submissions
- `clip_view_tracking` - View tracking history

### Medium Priority (Weekly Backup)
- `brand_registrations` - Brand registration data
- `clipper_profiles` - Clipper profiles
- `notifications` - Notification history
- `top_ups` - Top up history
- `withdrawals` - Withdrawal requests

### Low Priority (Monthly Backup) 
- Analytics aggregated data
- Cache data (can be regenerated)

## Backup Methods

### Database Backup

1. **Automated Daily Backup**:
```bash
# Add to crontab or Laravel scheduler
0 2 * * * mysqldump -u user -p database > /backups/daily/db_$(date +\%Y\%m\%d).sql
```

2. **Laravel Backup Package** (Recommended):
```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

3. **Configuration** (`config/backup.php`):
```php
'backup' => [
    'name' => env('APP_NAME', 'noteds'),
    'source' => [
        'databases' => [
            'mysql',
        ],
        'files' => [
            base_path('.env'),
            storage_path('app'),
        ],
    ],
    'destination' => [
        'disks' => ['s3'], // or 'local'
    ],
],
```

### Backup Retention

- **Daily backups**: Keep 30 days
- **Weekly backups**: Keep 12 weeks
- **Monthly backups**: Keep 12 months

## Recovery Plan

### Data Corruption
1. Stop application
2. Restore from latest backup
3. Replay transactions from audit log if needed
4. Verify data integrity
5. Resume operations

### Disaster Recovery
1. Restore database from backup
2. Restore application code
3. Restore uploaded files
4. Verify all services
5. Test critical flows
6. Resume operations

## Automated Backup Script

Create `database/backups/backup_ledger.sh`:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/ledger"
mkdir -p $BACKUP_DIR

# Backup ledger entries
mysqldump -u user -p database ledger_entries > $BACKUP_DIR/ledger_$DATE.sql

# Backup audit logs
mysqldump -u user -p database audit_logs > $BACKUP_DIR/audit_$DATE.sql

# Compress
gzip $BACKUP_DIR/ledger_$DATE.sql
gzip $BACKUP_DIR/audit_$DATE.sql

# Upload to S3 (optional)
aws s3 cp $BACKUP_DIR/ledger_$DATE.sql.gz s3://backups/ledger/
aws s3 cp $BACKUP_DIR/audit_$DATE.sql.gz s3://backups/audit/

# Cleanup old backups (keep last 30 days)
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
```

## Laravel Scheduled Backup

Add to `app/Console/Kernel.php` or `routes/console.php`:
```php
Schedule::command('backup:run --only-db')->daily()->at('02:00');
Schedule::command('backup:clean')->daily()->at('03:00');
```

