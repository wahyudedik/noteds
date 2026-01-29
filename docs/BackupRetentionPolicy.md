# Backup Retention Policy and Cleanup

## Configuration
- Environment: `BACKUP_RETENTION_DAYS` (default: 60)
- Optional config key: `backup.retention_days`

## Cleanup Migration
- File: `database/migrations/2026_01_30_003000_cleanup_backup_tables.php`
- Behavior:
  - Enumerates tables with suffix `_backup`
  - Reads creation time via `INFORMATION_SCHEMA.TABLES` (MySQL)
  - Fallback: `MIN(created_at)` if column exists
  - Drops tables older than retention, logs action via Laravel logger
- Rollback:
  - Recreates empty `{table}_backup` tables (using original schema if available)

## Usage
- Set retention as needed (`30`, `60`, `90` days)
- Run: `php artisan migrate --force`
- Logs: see `storage/logs/laravel.log`

## Notes
- SQLite does not expose table creation time; cleanup uses data fallback or skips exact age inference.
