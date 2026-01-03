# Setup Queue & Schedule di aaPanel

Panduan lengkap untuk mengkonfigurasi Laravel Queue dan Scheduled Tasks di aaPanel.

## Prerequisites

- Server dengan aaPanel terinstall
- Laravel application sudah terdeploy
- PHP CLI sudah terinstall
- Supervisor atau systemd tersedia
- Database sudah dikonfigurasi

## 1. Setup Laravel Queue Worker

### 1.1 Install Supervisor (jika belum ada)

```bash
# Login ke server via SSH
ssh root@your-server-ip

# Install supervisor
yum install supervisor -y  # CentOS/RHEL
# atau
apt-get install supervisor -y  # Ubuntu/Debian

# Start dan enable supervisor
systemctl start supervisord
systemctl enable supervisord
```

### 1.2 Konfigurasi Supervisor untuk Queue Worker

1. **Buat file konfigurasi supervisor**:

```bash
nano /etc/supervisord.d/laravel-worker.ini
```

2. **Isi dengan konfigurasi berikut** (sesuaikan path dan user):

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/your-domain.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www
numprocs=2
redirect_stderr=true
stdout_logfile=/www/wwwroot/your-domain.com/storage/logs/worker.log
stopwaitsecs=3600
```

**Penjelasan konfigurasi:**
- `command`: Path ke artisan dan command queue:work
- `user`: User yang menjalankan worker (biasanya `www` di aaPanel)
- `numprocs`: Jumlah worker process (sesuaikan dengan kebutuhan)
- `stdout_logfile`: Path untuk log file
- `--sleep=3`: Delay 3 detik setelah queue kosong
- `--tries=3`: Retry maksimal 3 kali jika gagal
- `--max-time=3600`: Worker restart setiap 1 jam

3. **Reload supervisor**:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start laravel-worker:*
```

4. **Cek status worker**:

```bash
supervisorctl status
```

### 1.3 Setup via aaPanel (Alternatif)

Jika menggunakan aaPanel, bisa setup via interface:

1. Login ke aaPanel
2. Buka **Supervisor Manager** (jika ada plugin)
3. Atau buka **Cron** → **Add Cron Job**
4. Tambahkan cron job untuk queue worker:

```
* * * * * cd /www/wwwroot/your-domain.com && php artisan queue:work --once
```

**Note**: Metode ini kurang optimal, lebih baik gunakan Supervisor.

## 2. Setup Laravel Scheduler (Cron)

### 2.1 Setup Cron Job di aaPanel

1. **Login ke aaPanel**
2. Navigasi ke **Cron** atau **Scheduled Tasks**
3. Klik **Add Cron Job**

### 2.2 Konfigurasi Cron Job

**Task Name**: Laravel Scheduler

**Task Type**: Shell Script

**Period**: N minute(s) → **1 minute**

**Script Content**:

```bash
cd /www/wwwroot/your-domain.com && php artisan schedule:run >> /dev/null 2>&1
```

**Penjelasan:**
- `cd /www/wwwroot/your-domain.com`: Masuk ke direktori project
- `php artisan schedule:run`: Menjalankan Laravel scheduler
- `>> /dev/null 2>&1`: Redirect output (opsional, bisa diganti dengan log file)

### 2.3 Setup via SSH (Alternatif)

Jika lebih suka setup manual via SSH:

```bash
# Edit crontab
crontab -e

# Tambahkan baris berikut (sesuaikan path)
* * * * * cd /www/wwwroot/your-domain.com && php artisan schedule:run >> /dev/null 2>&1
```

### 2.4 Verifikasi Scheduled Commands

Scheduled commands yang sudah dikonfigurasi di `routes/console.php`:

```php
// MediaStack articles fetch (3x per hari)
Schedule::command('mediastack:fetch')->dailyAt('08:00');
Schedule::command('mediastack:fetch')->dailyAt('14:00');
Schedule::command('mediastack:fetch')->dailyAt('20:00');

// Clipper System
Schedule::command('clipper:track-views')->everySixHours();
Schedule::command('clipper:validate-pending-clips')->hourly();
Schedule::command('clipper:auto-transfer-rewards')->everyFifteenMinutes();
Schedule::command('clipper:complete-expired-campaigns')->daily();

// Explorer Articles
Schedule::command('articles:sync --source=rss')->dailyAt('02:00');
Schedule::command('articles:sync --source=reddit')->everySixHours();
```

## 3. Konfigurasi Queue Driver

### 3.1 Update .env File

Edit file `.env` di root project:

```env
QUEUE_CONNECTION=database
# atau
QUEUE_CONNECTION=redis  # Jika menggunakan Redis
```

### 3.2 Setup Database Queue Table

Jika menggunakan `database` sebagai queue driver:

```bash
cd /www/wwwroot/your-domain.com
php artisan queue:table
php artisan migrate
```

### 3.3 Setup Redis (Opsional, Recommended)

Jika menggunakan Redis untuk queue:

1. **Install Redis** (jika belum ada):

```bash
# Via aaPanel: Software Store → Redis
# atau via SSH:
yum install redis -y  # CentOS
apt-get install redis-server -y  # Ubuntu
```

2. **Start Redis**:

```bash
systemctl start redis
systemctl enable redis
```

3. **Update .env**:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 4. Monitoring & Logs

### 4.1 Monitor Queue Status

```bash
# Cek queue status
php artisan queue:monitor

# Cek failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### 4.2 Monitor Supervisor

```bash
# Status semua workers
supervisorctl status

# Restart worker
supervisorctl restart laravel-worker:*

# Stop worker
supervisorctl stop laravel-worker:*

# View logs
tail -f /www/wwwroot/your-domain.com/storage/logs/worker.log
```

### 4.3 Monitor Scheduler

```bash
# Test scheduler (dry run)
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run

# View scheduler logs
tail -f storage/logs/scheduler.log
```

## 5. Troubleshooting

### 5.1 Queue Worker Tidak Berjalan

**Problem**: Queue jobs tidak diproses

**Solution**:
```bash
# Cek supervisor status
supervisorctl status

# Cek log
tail -f /www/wwwroot/your-domain.com/storage/logs/worker.log

# Restart supervisor
systemctl restart supervisord
supervisorctl reread
supervisorctl update
```

### 5.2 Scheduler Tidak Berjalan

**Problem**: Scheduled commands tidak dieksekusi

**Solution**:
```bash
# Verifikasi cron job
crontab -l

# Test scheduler manual
cd /www/wwwroot/your-domain.com
php artisan schedule:run

# Cek permission
ls -la /www/wwwroot/your-domain.com/storage/logs
```

### 5.3 Permission Issues

**Problem**: Permission denied errors

**Solution**:
```bash
# Set ownership
chown -R www:www /www/wwwroot/your-domain.com

# Set permissions
chmod -R 755 /www/wwwroot/your-domain.com
chmod -R 775 /www/wwwroot/your-domain.com/storage
chmod -R 775 /www/wwwroot/your-domain.com/bootstrap/cache
```

### 5.4 Memory Issues

**Problem**: Worker menggunakan terlalu banyak memory

**Solution**:
- Kurangi `numprocs` di supervisor config
- Tambahkan `--max-jobs=1000` untuk restart worker setelah N jobs
- Monitor memory usage: `htop` atau `free -m`

## 6. Best Practices

### 6.1 Queue Configuration

- Gunakan **Redis** untuk production (lebih cepat dari database)
- Set `numprocs` sesuai dengan CPU cores (biasanya 2-4)
- Monitor queue size dan adjust worker count
- Set timeout yang reasonable untuk long-running jobs

### 6.2 Scheduler Configuration

- Pastikan cron job berjalan setiap menit
- Log semua scheduled commands untuk debugging
- Gunakan `withoutOverlapping()` untuk commands yang tidak boleh overlap
- Set `runInBackground()` untuk commands yang lama

### 6.3 Monitoring

- Setup monitoring untuk queue size
- Alert jika queue size terlalu besar
- Monitor failed jobs dan retry rate
- Log semua queue operations

## 7. Production Checklist

- [ ] Supervisor terinstall dan running
- [ ] Queue worker running dengan multiple processes
- [ ] Cron job untuk scheduler sudah setup
- [ ] Queue driver dikonfigurasi (Redis recommended)
- [ ] Log files writable
- [ ] Monitoring setup
- [ ] Failed jobs handling
- [ ] Backup strategy untuk queue data
- [ ] Alert system untuk queue failures

## 8. Script Helper

Buat script untuk memudahkan management:

**File: `/www/wwwroot/your-domain.com/queue-manager.sh`**

```bash
#!/bin/bash

case "$1" in
    start)
        supervisorctl start laravel-worker:*
        echo "Queue workers started"
        ;;
    stop)
        supervisorctl stop laravel-worker:*
        echo "Queue workers stopped"
        ;;
    restart)
        supervisorctl restart laravel-worker:*
        echo "Queue workers restarted"
        ;;
    status)
        supervisorctl status
        ;;
    logs)
        tail -f /www/wwwroot/your-domain.com/storage/logs/worker.log
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|status|logs}"
        exit 1
        ;;
esac
```

**Set executable**:
```bash
chmod +x /www/wwwroot/your-domain.com/queue-manager.sh
```

**Usage**:
```bash
./queue-manager.sh start
./queue-manager.sh status
./queue-manager.sh logs
```

## 9. Additional Resources

- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Laravel Scheduler Documentation](https://laravel.com/docs/scheduling)
- [Supervisor Documentation](http://supervisord.org/)
- [aaPanel Documentation](https://doc.aapanel.com/)

---

**Last Updated**: 2025-01-XX
**Maintained by**: Development Team

