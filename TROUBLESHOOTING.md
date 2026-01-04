# Troubleshooting Guide

## Error 503 pada Assets (JavaScript/CSS files)

Jika Anda mendapatkan error 503 pada file-file assets seperti:
```
GET https://noteds.com/build/assets/Index-d9sGL4Wb.js net::ERR_ABORTED 503 (Service Unavailable)
```

Ini biasanya disebabkan oleh **Nginx Rate Limiting** yang memblokir terlalu banyak request secara bersamaan.

### Penyebab

Vite memecah kode menjadi banyak file kecil (chunks), dan browser mencoba memuat semua file tersebut secara bersamaan. Jika Nginx memiliki rate limiting yang terlalu ketat, ini akan memicu error 503.

### Solusi 1: Update Vite Configuration (Sudah dilakukan)

File `vite.config.js` sudah di-update untuk mengurangi jumlah chunk files dengan menambahkan:
```javascript
build: {
    rollupOptions: {
        output: {
            manualChunks: undefined, // Disable manual chunking
        },
    },
    chunkSizeWarningLimit: 1600,
}
```

**Langkah selanjutnya:**
1. Rebuild assets:
   ```bash
   npm run build
   ```
2. Deploy ulang ke production

### Solusi 2: Cek dan Atur Nginx Rate Limiting

Jika menggunakan aaPanel atau panel serupa:

#### A. Temukan File Konfigurasi Nginx

**Melalui Terminal:**
```bash
# File konfigurasi biasanya di:
/www/server/panel/vhost/nginx/noteds.com.conf
```

**Melalui aaPanel:**
1. Buka aaPanel
2. Klik menu **Website**
3. Klik nama domain **noteds.com**
4. Klik tab **Config**

#### B. Cek Rate Limiting

Cari baris yang mengandung `limit_req` atau `limit_conn`, biasanya seperti:
```nginx
limit_req zone=one burst=5 nodelay;
# atau
limit_conn addr 10;
```

#### C. Solusi Rate Limiting

**Opsi 1: Naikkan Burst Value (Recommended)**
```nginx
# Ubah dari:
limit_req zone=one burst=5 nodelay;

# Menjadi:
limit_req zone=one burst=100 nodelay;
```

**Opsi 2: Nonaktifkan Sementara untuk Testing**
```nginx
# Tambahkan # di depan untuk comment out:
# limit_req zone=one burst=5 nodelay;
```

**Opsi 3: Kecualikan Folder Build dari Rate Limiting**
Tambahkan di dalam block `server { ... }`:
```nginx
location /build/ {
    limit_req off;
    # atau
    # limit_req zone=one burst=1000 nodelay;
}
```

Setelah mengubah konfigurasi, reload Nginx:
```bash
nginx -t  # Test konfigurasi
nginx -s reload  # Reload Nginx
```

### Solusi 3: Cek Error Logs

Untuk memastikan apakah benar Nginx yang memblokir:

```bash
# Cek error log
tail -f /www/wwwlogs/noteds.com.error.log
```

Kemudian refresh website. Jika muncul tulisan seperti:
- "limiting requests"
- "limiting connections"
- "503 Service Unavailable"

Maka benar Nginx yang menyebabkan masalah.

### Solusi 4: Cek Permissions

Pastikan folder `public/build` memiliki permission yang benar:

```bash
# Set permissions
chmod -R 755 public/build
chown -R www:www public/build  # Ganti www dengan user web server Anda
```

### Checklist Troubleshooting

- [ ] Vite config sudah di-update (`vite.config.js`)
- [ ] Assets sudah di-rebuild (`npm run build`)
- [ ] File konfigurasi Nginx sudah dicek (`/www/server/panel/vhost/nginx/noteds.com.conf`)
- [ ] Rate limiting sudah diatur (naikkan burst atau disable)
- [ ] Nginx sudah di-reload (`nginx -s reload`)
- [ ] Error logs sudah dicek (`/www/wwwlogs/noteds.com.error.log`)
- [ ] Permissions folder build sudah benar (`chmod -R 755 public/build`)

### Setelah Perbaikan

1. Rebuild assets:
   ```bash
   npm run build
   ```

2. Deploy ke production:
   ```bash
   ./deploy.sh
   ```

3. Clear browser cache dan test lagi

### Catatan Tambahan

- Jika menggunakan **Cloudflare**, pastikan **Rocket Loader** dinonaktifkan
- Pastikan `APP_URL` di `.env` sudah benar (https://noteds.com)
- Pastikan folder `public/build` ada dan berisi file-file assets

