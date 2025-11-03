# 🔧 Cara Menghapus Cache untuk Fix SSL Certificate Error

## Problem
Error `NET::ERR_CERT_COMMON_NAME_INVALID` masih muncul karena browser masih "mengingat" sertifikat untuk `backend.test` yang sudah tidak digunakan lagi.

## Solusi Lengkap

### 1. Clear HSTS Cache (PENTING - Lakukan Ini Dulu!)

**Chrome/Edge:**
1. Buka: `chrome://net-internals/#hsts`
2. Scroll ke bagian **"Delete domain security policies"**
3. Masukkan: `noteds.test` → Klik **"Delete"**
4. **Masukkan: `backend.test` → Klik "Delete"** ⚠️ INI PENTING!
5. Pastikan kedua domain sudah terhapus

**Firefox:**
1. Buka: `about:config`
2. Cari: `security.tls.insecure_fallback_hosts`
3. Hapus entri `noteds.test` dan `backend.test` jika ada
4. Clear browser cache

### 2. Clear Browser Cache Lengkap

**Chrome/Edge:**
1. Tekan: `Ctrl + Shift + Delete`
2. Atau buka: `chrome://settings/clearBrowserData`
3. Pilih:
   - ✅ Cached images and files
   - ✅ Cookies and other site data
   - ✅ Hosted app data
4. Time range: **"All time"**
5. Klik **"Clear data"**
6. Restart browser

**Firefox:**
1. Tekan: `Ctrl + Shift + Delete`
2. Pilih: **"Everything"**
3. Pilih semua opsi
4. Clear data
5. Restart browser

### 3. Clear DNS Cache (Windows)

Buka PowerShell atau CMD sebagai Administrator:
```powershell
ipconfig /flushdns
```

### 4. Disable HTTPS di Herd

1. Buka Laravel Herd
2. Pilih site `noteds.test`
3. Settings → **Disable HTTPS** / pilih **HTTP Only**
4. Restart site

### 5. Akses via HTTP (Bukan HTTPS)

**PENTING:** Setelah clear cache, akses langsung via:
```
http://noteds.test
```

**Jangan** akses via `https://noteds.test` untuk development.

### 6. Verifikasi

Setelah melakukan semua langkah di atas:
1. Restart browser
2. Buka: `http://noteds.test` (bukan https)
3. Error SSL certificate seharusnya tidak muncul lagi
4. Console tidak akan ada error "backend.test"

## Vite ERR_EMPTY_RESPONSE (Assets Not Loading)

Jika console menampilkan error `ERR_EMPTY_RESPONSE` untuk `app.css`, `app.js`, atau `client`:

1. **Check Vite Server:**
   ```bash
   # Windows PowerShell
   netstat -ano | findstr :5173
   ```
   - Jika ada output, server sudah berjalan
   - Jika tidak ada, jalankan: `npm run dev`

2. **Pastikan Akses via HTTP:**
   - Akses: `http://noteds.test` (bukan https://)
   - Vite dev server menggunakan HTTP, tidak bisa diakses dari HTTPS page

3. **Restart Vite Server:**
   ```bash
   # Stop (Ctrl+C) lalu restart
   npm run dev
   ```

4. **Use Production Build:**
   ```bash
   npm run build
   ```
   - Assets akan di-build ke `public/build/`
   - Tidak perlu Vite dev server

## Jika Masih Error

Jika masih muncul error setelah semua langkah di atas:

1. **Incognito/Private Window:**
   - Buka browser dalam mode incognito
   - Akses `http://noteds.test`
   - Jika bekerja di incognito, berarti cache masih ada di normal mode

2. **Clear Storage Manually:**
   - Buka DevTools (F12)
   - Tab "Application" → "Storage"
   - Klik "Clear site data"
   - Reload page

3. **Reset Browser (Extreme):**
   - Chrome: Settings → Reset and clean up → Restore settings to their original defaults
   - Firefox: Settings → Privacy & Security → Clear Data

## Prevent Future Issues

Untuk mencegah masalah ini di masa depan:
- ✅ Gunakan HTTP untuk development (`http://noteds.test`)
- ✅ Disable HTTPS di Herd untuk development
- ✅ Jangan pernah akses `https://noteds.test` jika tidak menggunakan HTTPS dengan benar
- ✅ Clear HSTS cache jika mengganti domain atau SSL configuration

---

**Last Updated:** 2025-11-03
**Status:** ✅ Complete Guide

