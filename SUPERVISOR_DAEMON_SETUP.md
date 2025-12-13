# Supervisor Configuration untuk Queue Worker

## Panel Supervisor di Hosting

Ketika membuat daemon baru di panel supervisor, gunakan konfigurasi berikut:

### ✅ SETTINGAN YANG BENAR:

| Field | Value | Keterangan |
|-------|-------|-----------|
| **Name** | `noteds-worker` | Nama untuk identifikasi |
| **Run User** | `www` | User yang menjalankan (sesuaikan dengan hosting) |
| **Processes** | `1` | Jumlah worker process (1 sudah cukup untuk queue) |
| **Startup priority** | `undefined` atau `999` | Priority saat sistem boot |
| **Start command** | `php /www/wwwroot/noteds.com/artisan queue:work --queue=default --sleep=3 --tries=5` | **CRITICAL** |
| **Process directory** | `/www/wwwroot/noteds.com/` | Root directory aplikasi |
| **Remark** | `Laravel Queue Worker for Midtrans Webhook Processing` | Catatan/deskripsi |

---

## 📝 PENJELASAN START COMMAND

```bash
php /www/wwwroot/noteds.com/artisan queue:work --queue=default --sleep=3 --tries=5
```

**Breakdown:**
- `php` - PHP executable
- `/www/wwwroot/noteds.com/artisan` - Path ke artisan console
- `queue:work` - Perintah untuk start queue worker
- `--queue=default` - Queue name yang di-process
- `--sleep=3` - Sleep 3 detik jika tidak ada job (resource saving)
- `--tries=5` - Retry job maksimal 5 kali jika gagal

---

## 🔧 ALTERNATIVE CONFIGURATIONS

### Option 1: Dengan Timeout (Recommended)
```bash
php /www/wwwroot/noteds.com/artisan queue:work --queue=default --sleep=3 --tries=5 --timeout=30
```
- `--timeout=30`: Job di-kill jika lebih dari 30 detik

### Option 2: Multiple Workers (untuk production dengan banyak requests)
Jika aplikasi sibuk, gunakan 2-3 processes:
- Change **Processes** ke `2` atau `3`
- Supervisor akan spawn 2-3 worker secara otomatis

### Option 3: Dengan Logging
```bash
php /www/wwwroot/noteds.com/artisan queue:work --queue=default --sleep=3 --tries=5 --verbose
```
- `--verbose`: Output detail logs (bisa cek di supervisor)

---

## ✅ STEP-BY-STEP DI PANEL SUPERVISOR

1. **Klik "Create Daemon"** atau **"Add New Daemon"**

2. **Isi Form:**
   - Name: `noteds-worker`
   - Run User: `www`
   - Processes: `1`
   - Startup priority: `undefined`
   - Start command: `php /www/wwwroot/noteds.com/artisan queue:work --queue=default --sleep=3 --tries=5`
   - Process directory: `/www/wwwroot/noteds.com/`
   - Remark: `Laravel Queue Worker - Midtrans Webhook`

3. **Klik "Confirm"**

4. **Verify:**
   - Lihat status → harus **"running"** atau **"online"**
   - Jika belum running, klik tombol "Start"

5. **Test:**
   ```bash
   # SSH ke server
   ps aux | grep "queue:work"
   # Harus muncul process baru
   ```

---

## 🔍 TROUBLESHOOTING

### Queue Worker Tidak Start
```bash
# SSH ke server dan jalankan manual
cd /www/wwwroot/noteds.com
php artisan queue:work --queue=default --sleep=3 --tries=5

# Lihat error apa yang keluar
```

### Queue Worker Keep Restarting
- Check supervisor logs: `/var/log/supervisor/`
- Periksa file permissions
- Pastikan database connection OK

### Jobs Not Processing
```bash
# Check jobs table
php artisan tinker
> DB::table('jobs')->count();

# Check failed jobs
php artisan queue:failed
```

### Worker Hung/Stuck
Supervisor akan auto-restart jika process down. Tapi bisa juga set:
- Add `--timeout=30` to start command
- Increase supervisor `startsecs` jika crash saat startup

---

## 📊 STATUS MONITORING

Setelah dikonfigurasi, Anda bisa:

1. **Di Panel Hosting:**
   - Lihat status: Running/Stopped
   - Restart daemon jika perlu
   - View logs

2. **Via SSH:**
   ```bash
   # Check process
   ps aux | grep "queue:work"
   
   # Check supervisor status
   supervisorctl status noteds-worker
   
   # View supervisor logs
   tail -f /var/log/supervisor/noteds-worker.log
   ```

3. **Check Queue Jobs:**
   ```bash
   cd /www/wwwroot/noteds.com
   
   # Count pending jobs
   php artisan tinker
   > DB::table('jobs')->count();
   
   # List failed jobs
   php artisan queue:failed
   ```

---

## 🎯 EXPECTED BEHAVIOR AFTER SETUP

✅ **Webhook Flow:**
1. Midtrans sends webhook
2. App returns 200 OK instantly
3. Job queued in `jobs` table
4. Queue worker picks up job
5. **Wallet balance updated within 1-5 seconds**

✅ **Supervisor Behavior:**
- Daemon runs continuously (even after server reboot)
- Auto-restarts if process crashes
- Logs available in supervisor panel

---

## 💡 TIPS

- **Single Worker** adalah standard untuk kebanyakan aplikasi
- Gunakan **multiple processes** hanya jika perlu (banyak concurrent webhooks)
- **Logs** sangat membantu debugging - cek secara berkala
- **Database queue** persistence - jobs disimpan di DB jika worker down
- **Cron sync** jalan setiap 5 menit sebagai backup

---

**Last Updated:** December 13, 2025
**For:** Noteds Application
**Queue System:** Laravel Database Queue
