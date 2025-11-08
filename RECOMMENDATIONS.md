# Noteds Improvement Recommendations

## Fitur Manajemen User: Nonaktifkan/Aktifkan & Suspend Akun

### 1. User Management di Dashboard Admin (Nonaktifkan/Suspend Akun)
- [x] **Fitur baru:** Tambahkan kemampuan admin untuk menonaktifkan/mengaktifkan akun user melalui dashboard (User Management).  
- [x] **Tindakan Admin:** Admin bisa melakukan suspend akun bila user memiliki banyak pelanggaran, atau mengaktifkannya kembali jika diperlukan.
- [x] **Database:**
  - Tambahkan field misal `is_active` (boolean, default: true) atau `suspended_at` (datetime nullable) di tabel `users`.
  - Seeder: Update AdminSeeder jika ingin admin tidak bisa dinonaktifkan/suspend.
- [x] **UI Admin:**
  - Tambah tombol "Nonaktifkan"/"Aktifkan" dan/atau "Suspend"/"Release" pada tabel user di dashboard admin.
  - Tampilkan status akun (aktif, nonaktif, suspended).
- [x] **Proses Backend:**
  - Tambah route dan controller action untuk menonaktifkan/mengaktifkan/suspend user (hanya untuk admin).
- [x] **Validasi:**
  - Nonaktif/suspend user tidak bisa login atau mengakses semua fitur, tampilkan pesan "Akun Anda dinonaktifkan/suspend" (EN/ID).
- [ ] **Log & Audit:**
  - Catat setiap aksi ban/suspend/unban pada log/audit trail.
- [x] **Notifikasi:**
  - Notifikasi email/konten jika akun dinonaktifkan/suspend/diaktifkan ulang.

---

## Rekomendasi Pajak & Rate Harga

### 1. Pengaturan Pajak Dinamis
- [x] **Status**: Implemented (November 8, 2025) — jalankan `php artisan migrate` dan `php artisan db:seed --class=TaxRuleSeeder` untuk mengaktifkan default rule Indonesia.
- [x] **Migrasi & Model**
  - [x] Tabel baru `tax_rules` menyimpan `country_code`, `note_category`, `tax_percent`, dan flag `is_inclusive`.
  - [x] Model helper `TaxRule` & `TaxService` untuk lookup berdasarkan negara/kategori.
- [x] **Seeder & Admin Panel**
  - [x] `SettingSeeder` menambahkan default `tax_percent` serta flag inclusive.
  - [x] Form admin (Settings > Marketplace) untuk mengatur pajak per negara dengan validasi.
- [x] **Backend Checkout**
  - [x] `MarketplaceController` menghitung struktur pajak dan menyimpan `tax_amount` + metadata di `transactions`.
- [x] Perbarui notifikasi/email agar menampilkan harga + pajak.
- [x] **Frontend**
  - [x] Marketplace detail menampilkan breakdown harga dasar, pajak, total + badge inclusive.

### 2. Harga Minimum / Kisaran
- [x] **Konfigurasi**
  - [x] Setting `min_price_default`, `min_price_categories`, dan `recommended_price_multiplier` tersedia di admin.
- [x] **Validasi Backend**
  - [x] `StoreNoteRequest` & `UpdateNoteRequest` memblokir harga di bawah batas kategori dengan pesan khusus.
- [~] **UI Seller**
  - [x] Form harga menampilkan panel panduan minimum & rekomendasi.
- [x] Hitung dan perbarui rekomendasi dinamis berdasarkan kategori/tag yang dipilih saat mengetik.

### 3. Komisi Fleksibel (Tiered Commission)
- **Data Struktur**
  - Tambah tabel `commission_tiers` dengan field: `name`, `volume_threshold`, `platform_fee_percent`, `creator_commission_percent`.
  - Seeder mengisi tier Default, Premium, Enterprise.
- **Logika Penentuan**
  - Buat service `CommissionService` yang menentukan tier seller berdasarkan total penjualan 30 hari terakhir.
  - Update `MarketplaceController` untuk mengambil fee dari service ini.
- **Admin UI & Reporting**
  - Panel admin untuk menambah/ubah tier.
  - Tambahkan laporan total penjualan / tier untuk memantau migrasi seller antar tier.

## Pengujian Alur Duplikasi Catatan

### Langkah Manual
1. **Seller A membuat konten**  
   - Login seller, buat note konten X → pastikan success (`content_hash` tersimpan).  
   - Referensi kode: `136:147:app/Http/Controllers/NoteController.php`.
2. **Buyer membeli konten**  
   - Login buyer, beli note tersebut melalui marketplace.
3. **Buyer mencoba menggandakan**  
   - Buyer sama membuat note baru dengan konten X → sistem harus menolak dengan pesan EN/ID.  
   - Referensi kode: `609:655:app/Http/Controllers/NoteController.php`, `1536:1540:lang/en/messages.php`.
4. **Modifikasi dan simpan**  
   - Ubah konten signifikan, simpan ulang → harus lolos (hash berubah).

### Automasi (Disarankan)
- Buat feature test Pest/PHPUnit:
  - **Test 1**: Seller membuat note → assert 201 & `content_hash` tersimpan.
  - **Test 2**: Buyer membeli → assert transaksi success.
  - **Test 3**: Buyer menyalin konten → assert response 302 dengan error duplicate.
  - **Test 4**: Konten beda → assert update sukses.
- Gunakan database transaksi in-memory atau DB sqlite saat pengujian.

## Rekomendasi Strategis Tambahan

### Global Pricing & Currency
- Integrasi API kurs (Fixer.io, exchangerate.host) untuk update kurs otomatis.
- Simpan pilihan mata uang di profil user & convert harga saat render marketplace.
- Tambah filter marketplace berdasarkan mata uang.

### Compliance & Legal
- Generator konten legal per negara: modul menyusun template (GDPR, CCPA, PDPA).
- Simpan template di `cms_pages` dan pilih otomatis berdasarkan lokasi/geolokasi.
- Audit log perubahan kebijakan agar mudah telusur.

### Seller Reputation
- Tambahkan rating seller (1–5), badge verifikasi (KYC) dan indikator original content.
- Perkuat halaman profil seller: histori penjualan, rating, badge.
- Gunakan reputasi untuk mempengaruhi ranking marketplace.

### Tax Reporting & Invoice
- Generate invoice PDF setelah transaksi (gunakan `barryvdh/laravel-dompdf` atau Snappy).
- Sertakan breakdown pajak, komisi, total diterima seller/buyer.
- Sediakan dashboard laporan pajak per periode untuk seller & admin.

### Anti-fraud Lanjutan
- Simpan fingerprint file (hash lampiran) & jalankan similarity check (cosine similarity / w-shingling) agar perubahan minor tetap terdeteksi.
- Implementasi sistem flag otomatis & manual review di admin panel.
- AI-assisted scanning untuk konten populer guna mencegah plagiarisme massal.

Silakan hubungi bila ingin implementasi modul pajak, pengujian otomatis, atau fitur lanjutan lainnya.

