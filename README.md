## Debugging & Form Safety

### Telescope (local/dev)
- Installed via `composer require laravel/telescope --dev` and `php artisan telescope:install && php artisan migrate`.
- Access: `/telescope` (local). Inspect requests, queries, jobs, exceptions.

### Debugbar (local/dev)
- Installed via `composer require barryvdh/laravel-debugbar --dev`.
- Shows view data, route, queries, timeline.

### Reusable Blade Inputs
Use components to avoid typo dan konsisten error display:

```blade
<x-form.input name="email" label="Email" type="email" required />
<x-form.textarea name="bio" label="Bio" rows="5" />
```

### Guard Unknown Fields in Requests
Extend `App\Http\Requests\BaseFormRequest` dan panggil guard pada `prepareForValidation`:

```php
class StoreUserRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->guardUnknownFields('users');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ];
    }
}
```

Guard hanya aktif di environment `local`/`testing`, aman untuk production.

## 💡 **Noteds** — Catatan & Ide Digital yang Menghasilkan Uang

### ✍️ Branding

> **Noteds** — Tempat Menulis, Menjual, dan Menemukan Ide Digital yang Menghasilkan Uang.

### 🎯 Tujuan Platform

Platform di mana pengguna bisa:
- ✅ Menulis dan menyimpan catatan pribadi
- ✅ Menjual template atau ide digital mereka
- ✅ Membeli catatan/ide/template milik orang lain
- ✅ Menghasilkan uang dari setiap catatan yang dibeli orang

### 💰 Monetisasi

- ✅ Komisi 20% dari setiap transaksi (sudah diimplementasikan)
- ⚠️ Paket Premium: Rp25.000/bln (FASE 7 - opsional)
- ⚠️ Iklan catatan unggulan di dashboard (future enhancement)

### 🧱 Struktur Database

| Tabel          | Status | Kolom Utama                                                  |
| -------------- | ------ | ------------------------------------------------------------ |
| `users`        | ✅     | id, name, email, password, role, wallet_balance              |
| `notes`        | ✅     | id, user_id, title, content, price, is_public, status          |
| `wallets`      | ✅     | id, user_id, balance                                         |
| `transactions` | ✅     | id, buyer_id, seller_id, note_id, amount, commission, status |
| `withdraws`    | ⚠️     | id, user_id, amount, status (FASE 5)                         |
| `note_reviews` | ⚠️     | id, note_id, user_id, rating, comment (FASE 6)              |
| `tags`         | ⚠️     | id, name, slug (FASE 2 step 7)                              |
| `note_tag`     | ⚠️     | note_id, tag_id (FASE 2 step 7)                              |

---

## 🧭 TASKLIST PENGEMBANGAN "NOTEDS" (Versi Detail)

---

### 🗓️ **FASE 1 – Setup Project (Minggu 1)**

#### 📌 Tujuan:
Menyiapkan pondasi Laravel & struktur dasar aplikasi Noteds.

**Langkah-langkah detail:**

1. [x] **Install Laravel 11**
   - Jalankan di terminal: `laravel new noteds`
   - Cek folder `noteds` sudah dibuat.
2. [x] **Setup database MySQL & koneksi `.env`**
   - Buat database MySQL, misal `noteds`.
   - Edit `.env`:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=noteds_db
     DB_USERNAME=root
     DB_PASSWORD= // (isi sesuai konfigurasi)
     ```
   - Jalankan `php artisan migrate` untuk tes koneksi.
3. [x] **Setup autentikasi dengan Laravel Breeze**
   - Jalankan:
     ```
     composer require laravel/breeze --dev
     php artisan breeze:install
     npm install && npm run dev
     php artisan migrate
     ```
   - Pastikan halaman login/register berjalan.
4. **Buat layout dasar Blade**
  - [x] Buka atau buat file: `resources/views/layouts/app.blade.php`
  - [x] Tambahkan struktur dasar HTML (doctype, html, head, body)
  - [x] Pasang komponen umum (misal: navbar)
  - [x] Gunakan `@yield('title')` untuk judul halaman
  - [x] Gunakan `@yield('content')` untuk konten utama
  - [x] Tambahkan section opsional (misal: stylesheet, script, atau footer) jika diperlukan
5. **Tambah navbar & dashboard minimal**
  - [x] Edit layout agar ada navbar (Home, Notes, Marketplace, Wallet, Logout).
  - [x] Buat view `resources/views/dashboard.blade.php`.
  - [x] Routing: arahkan `/dashboard` ke halaman dashboard.
6. **Modifikasi tabel `users`**
   - [x] Buka migration users.
   - [x] Tambahkan kolom:
     ```php
     $table->string('role')->default('buyer'); 
     $table->decimal('wallet_balance', 12, 2)->default(0);
     ```
   - [x] Jalankan `php artisan migrate:refresh`.
7. **Integrasi Spatie Permission** (implemented)
   - [x] `composer require spatie/laravel-permission`
   - [x] Publish config & migration:
     ```
     php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
     php artisan migrate
     ```
   - [x] Tambahkan trait `HasRoles` ke model User.
   - [x] Definisikan role: admin, seller, buyer.
8. [x] **Seeder akun admin default**
   - [x] Buat seeder: `php artisan make:seeder AdminSeeder`
   - [x] Di `AdminSeeder`:
     ```php
     User::create([
       'name' => 'Admin',
       'email' => 'admin@noteds.test',
       'password' => bcrypt('password'),
       'role' => 'admin',
     ])->assignRole('admin');
     ```
   - [x] Jalankan: `php artisan db:seed --class=AdminSeeder`

---

### 🗓️ **FASE 2 – Modul Catatan (Minggu 2–3)**

#### 📌 Tujuan:
Pengguna dapat membuat, menyimpan, dan mengelola catatan (pribadi/publik).

**Langkah-langkah detail:**

1. **Migration tabel `notes`**
   - [x] `php artisan make:migration create_notes_table`
   - [x] Dalam migration:
     ```php
     $table->id();
     $table->foreignId('user_id')->constrained()->onDelete('cascade');
     $table->string('title');
     $table->text('content');
     $table->decimal('price', 12, 2)->default(0);
     $table->boolean('is_public')->default(false);
     $table->enum('status', ['active', 'sold', 'inactive'])->default('active');
     $table->timestamps();
     ```
   - [x] Jalankan `php artisan migrate`
2. **Model & Migration Note**
   - [x] Perintah sudah di atas; lanjutkan: `php artisan make:model Note`
   - [x] Konfigurasi relasi `User` di model Note.
3. **Controller Note**
   - [x] `php artisan make:controller NoteController --resource`
   - [x] Implementasi:
     - [x] index: list notes user
     - [x] create/store: form dan simpan catatan
     - [x] show: detail catatan
     - [x] edit/update: ubah catatan
     - [x] destroy: hapus catatan
4. **CRUD Note pada Routing & View**
   - [x] Routing:
     ```
     Route::resource('notes', NoteController::class);
     ```
   - [x] Buat views:
     - [x] `resources/views/notes/index.blade.php`
     - [x] `resources/views/notes/create.blade.php`
     - [x] `resources/views/notes/show.blade.php`
     - [x] `resources/views/notes/edit.blade.php`
   - [x] Validasi input pada setiap form.
5. **Form is_public & price**
   - [x] Pada form create/edit catatan, tambahkan opsi:
     - [x] Publish/Public? (checkbox)
     - [x] Harga catatan (input number)
6. **Catatan Publik di Marketplace**
   - [x] Di model Note, buat scope `publicOnly`.
   - [x] Tampilkan hanya `is_public = true` pada halaman marketplace.
7. **Sistem Tagging/Kategori Catatan**
   - [x] Migration tabel `tags` dan `note_tag` (pivot):
     ```php
     // create_tags_table
     $table->id();
     $table->string('name')->unique();
     $table->string('slug')->unique();
     $table->timestamps();
     
     // create_note_tag_table (pivot)
     $table->foreignId('note_id')->constrained()->onDelete('cascade');
     $table->foreignId('tag_id')->constrained()->onDelete('cascade');
     ```
   - [x] Model Tag dengan relasi many-to-many ke Note
   - [x] Form create/edit note: input tags (select multiple atau autocomplete)
   - [x] Filter marketplace by tag
   - [x] Tampilkan tags di notes show, index, dan marketplace

---

### 🗓️ **FASE 3 – Marketplace Catatan (Minggu 4)**

#### 📌 Tujuan:
Menyediakan halaman publik untuk eksplorasi dan pembelian catatan.

**Langkah-langkah detail:**

1. **Route `/marketplace`**
   - [x] Tambahkan: `Route::get('/marketplace', [MarketplaceController::class, 'index']);`
   - [x] Route show: `/marketplace/{note}`
   - [x] Route purchase: `/marketplace/{note}/purchase`
2. **Controller Marketplace**
   - [x] `php artisan make:controller MarketplaceController`
   - [x] Method index: tampilkan list publik notes
   - [x] Method show: detail catatan publik
   - [x] Method purchase: validasi pembelian (implementasi penuh di FASE 4)
3. **List catatan publik**
   - [x] Filter: search by judul, filter harga.
   - [x] Query example:
     ```php
     Note::where('is_public', true)
         ->where('status', 'active')
         ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
         ->orderBy('created_at', 'desc')
         ->paginate(12);
     ```
   - [x] Buat view `marketplace/index.blade.php`.
   - [x] Filter by tag (setelah tagging system diimplementasikan)
4. **Detail catatan publik**
   - [x] Route: `/marketplace/{note}`
   - [x] Tombol "Beli Catatan" (hanya jika user ≠ owner & belum pernah beli)
   - [x] View: `marketplace/show.blade.php`
5. **Riwayat pembelian**
   - [ ] Migration tabel `transactions` (lihat FASE 4).
   - [x] Simpan data setiap pembelian (method purchase disiapkan, implementasi penuh di FASE 4).

---

### 🗓️ **FASE 4 – Sistem Wallet & Transaksi (Minggu 5)**

#### 📌 Tujuan:
Menyediakan sistem saldo dan transaksi pembelian catatan.

**Langkah-langkah detail:**

1. **Migration tabel `wallets`**
   - [x] `php artisan make:migration create_wallets_table`
   - [x] Field: user_id (unik), balance (default 0)
2. **Migration tabel `transactions`**
   - [x] `php artisan make:migration create_transactions_table`
   - [x] buyer_id, seller_id, note_id, amount, commission, status (enum: pending/success/failed)
3. **Model & Controller Wallet**
   - [x] `php artisan make:model Wallet`
   - [x] `php artisan make:controller WalletController`
   - [x] Fitur top-up, transaksi, dan cek saldo.
4. **Integrasi Top-up (Midtrans/Tripay API)**
   - [x] Pilih provider (contoh: Midtrans)
   - [x] Buat konfigurasi sandbox di `.env`
   - [x] Implementasi webhook/handler pembayaran
5. **Pembelian Catatan**
   - [x] Proses:
     - [x] Cek saldo user cukup
     - [x] Kurangi saldo buyer
     - [x] Tambah saldo seller (80%)
     - [x] Tambah saldo platform (wallet khusus "admin" 20%)
     - [x] Update/membuat record transaction
     - [x] Cegah pembelian ganda (cek di transaksi sudah ada/belum dengan user dan note yang sama)
6. **Halaman `/wallet`**
   - [x] List transaksi user
   - [x] Saldo terkini

---

### 🗓️ **FASE 5 – Modul Withdraw & Admin Panel (Minggu 6)**

#### 📌 Tujuan:
Seller dapat menarik saldo, admin dapat mengatur transaksi & komisi.

**Langkah-langkah detail:**

1. **Migration tabel `withdraws`**
   - [ ] `php artisan make:migration create_withdraws_table`
   - [ ] Field: 
     ```php
     $table->id();
     $table->foreignId('user_id')->constrained()->onDelete('cascade');
     $table->decimal('amount', 12, 2);
     $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
     $table->string('bank_name');
     $table->string('account_number');
     $table->string('account_name');
     $table->text('admin_notes')->nullable();
     $table->foreignId('processed_by')->nullable()->constrained('users');
     $table->timestamp('processed_at')->nullable();
     $table->timestamps();
     ```
2. **Model & Controller Withdraw**
   - [ ] `php artisan make:model Withdraw`
   - [ ] `php artisan make:controller WithdrawController`
   - [ ] Relasi ke User (belongsTo)
3. **Form withdraw**
   - [ ] Buat di page `/wallet/withdraw`
   - [ ] Validasi saldo minimal (Rp50.000)
   - [ ] Input: jumlah, nama bank, nomor rekening, nama pemilik rekening
   - [ ] Validasi saldo cukup sebelum submit
4. **Konfirmasi Admin**
   - [ ] Halaman admin withdraw: list request withdraw dengan filter status
   - [ ] Aksi approve/reject (ubah status) dengan input catatan admin
   - [ ] Jika approve: potong saldo wallet, catat transaksi keluar
   - [ ] Notifikasi email ke user saat withdraw approved/rejected
5. **Halaman Admin**
   - [ ] Controller: `php artisan make:controller Admin/DashboardController`
   - [ ] Route group dengan middleware `role:admin`:
     ```php
     Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function() {
         Route::get('/dashboard', [Admin\DashboardController::class, 'index']);
         Route::resource('users', Admin\UserController::class);
         Route::resource('transactions', Admin\TransactionController::class);
         Route::resource('withdraws', Admin\WithdrawController::class);
     });
     ```
   - [ ] `/admin/dashboard`: statistik (total user, transaksi, saldo platform, dll)
   - [ ] `/admin/users`: kelola user (CRUD, suspend, edit role)
   - [ ] `/admin/transactions`: list semua transaksi dengan filter & export
   - [ ] `/admin/withdraws`: list dan approve/reject withdraw
   - [ ] `/admin/notes`: kelola catatan publik (moderasi jika perlu)
   - [ ] Middleware role-based untuk akses admin (`App\Http\Middleware\EnsureUserIsAdmin`)

---

### 🗓️ **FASE 6 – Rating, Review, & Profil Publik (Minggu 7)**

#### 📌 Tujuan:
Membangun fitur interaksi sosial & reputasi antar pengguna.

**Langkah-langkah detail:**

1. **Migration tabel `note_reviews`**
   - `php artisan make:migration create_note_reviews_table`
   - Field: note_id, user_id, rating (int, 1-5), comment (nullable)
2. **Form Ulasan**
   - Ditampilkan hanya jika user sudah membeli note & belum mereview
   - Validasi rating antara 1 dan 5
3. **Tampilkan rating**
   - Rata-rata rating catatan pada halaman detail note
   - Bisa gunakan Eloquent attribute accessor untuk rata-rata
4. **Profil publik User**
   - [ ] Migration: tambah kolom `username` (unique), `avatar`, `bio` ke tabel users
   - [ ] Route: `/u/{username}` atau `/profile/{user}`
   - [ ] Tampilkan: 
     - [ ] Avatar/bio user
     - [ ] Catatan publik yang dijual dengan pagination
     - [ ] Ringkasan rating (rata-rata rating dari semua catatan)
     - [ ] Total penjualan (jumlah catatan terjual, total pendapatan)
   - [ ] Controller: `php artisan make:controller ProfileController` (public profile, bukan edit)

---

### 🗓️ **FASE 7 – Premium Plan (Opsional, Minggu 8–9)**

#### 📌 Tujuan:
Menambah sumber pendapatan dan benefit user premium.

**Langkah-langkah detail:**

1. **Migration `subscriptions`**
   - `php artisan make:migration create_subscriptions_table`
   - Field: user_id, plan (enum: basic/premium), expired_at (timestamp)
2. **Langganan via QRIS (manual/approve admin)**
   - User upload bukti transfer/QRIS (fitur dummy)
   - Admin approve => aktifkan premium
3. **Fitur premium**
   - Unlimited catatan (tanpa batasan create)
   - Statistik penjualan di dashboard
   - Backup ke cloud (opsional, ex: cronjob upload S3)

---

### 🗓️ **FASE 8 – Finishing & Launch Beta (Minggu 10)**

#### 📌 Tujuan:
Menyiapkan aplikasi untuk rilis publik.

**Langkah-langkah detail:**

1. **Testing end-to-end**
   - Buat user dummy: admin, seller, buyer (pakai seeder/faker)
   - Jalankan skenario transaksi
2. **Penambahan validasi & error handling**
   - Lengkapi request form dengan FormRequest
   - Tampilkan feedback jika gagal sukses/langkah berikut
   - Log error ke Sentry/Stackdriver jika perlu
3. **Landing page `/`**
   - [ ] Konten: deskripsi, fitur utama, call-to-action register/login
   - [ ] Tambah ilustrasi/logo simple
   - [ ] Section: "Cara Kerja", "Fitur", "Testimoni" (opsional)
4. **Pengayaan UI**
   - [ ] Upload logo/favicon
   - [ ] Footer dengan kontak/info
   - [ ] Responsive design untuk mobile
   - [ ] Dark mode toggle (opsional)
5. **Deploy ke Hosting**
   - Pilih target (Laragon, Ploi, Laravel Forge, cPanel)
   - Pastikan `.env` & DB sudah dihosting
   - Cek domain sudah aktif
6. **Custom domain**
   - Konfigurasi DNS/domain agar mengarah ke server Noteds

---

### 🧩 BONUS TASK (Jangka Panjang)

**Pengembangan lebih lanjut:**

- [ ] Integrasi AI: auto-generate summary, suggest tags (pakai ollama)
- [ ] Sistem referral: user unik link, dapat saldo jika referral daftarkan & transaksi
- [ ] REST API publik: endpoint dokumentasi untuk mobile/3rd party
- [ ] Versi mobile: Flutter app, konek ke REST API

---

> **Tips:** Eksekusi setiap fase mingguan dan checklist per task agar workflow terstruktur. Jika butuh _breakdown_ task lebih mikro (per controller, model, route, view, migration), bisa dibuat sub-checklist per file misal sebagai berikut:

#### Contoh Checklist Per File (FASE 2: Modul Catatan)

- [ ] Migration: `create_notes_table.php`
- [ ] Model: `Note.php`
- [ ] Controller: `NoteController.php`
- [ ] Request: `StoreNoteRequest.php`, `UpdateNoteRequest.php`
- [ ] Route: resource route di `web.php`
- [ ] View:
  - [ ] `notes/index.blade.php`
  - [ ] `notes/create.blade.php`
  - [ ] `notes/show.blade.php`
  - [ ] `notes/edit.blade.php`#   n o t e d s  
 