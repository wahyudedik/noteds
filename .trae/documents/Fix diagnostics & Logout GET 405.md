## Target
- Hilangkan error 405 saat membuka `http://noteds.test/logout`.
- Rapikan item di `#problems_and_diagnostics` (utamanya yang berpotensi runtime bug dan yang bikin IDE merah), tanpa mengubah behavior aplikasi secara tidak perlu.

## Analisa Singkat
- `/logout` di Laravel default-nya **POST-only**, jadi kalau dibuka via URL (GET) akan **405 Method Not Allowed**.
- Mayoritas error di daftar diagnostics berasal dari static analyzer (Intelephense) yang tidak paham helper `auth()`/magic forwarding (mis. paginator `pluck()`), tapi beberapa bisa jadi **runtime bug** atau minimal bikin code kurang jelas.

## Perbaikan Logout
1. Tambahkan route **GET /logout** yang aman (tidak melakukan logout), hanya **redirect** (mis. ke `home` atau `login`) agar tidak 405.
2. Pastikan tombol Logout di sidebar tidak pernah menghasilkan navigasi GET:
   - Ubah implementasi Logout menjadi tombol murni yang memanggil `router.post(route('logout'))` (tidak memiliki `href` yang bisa dibuka sebagai GET).
   - Terapkan di `SidebarNav` (submenu Account + tombol di kartu profil).

## Perbaikan Diagnostics (tanpa komentar)
1. `SchedulingController.php`
   - Ganti penggunaan `\DB::...` menjadi `DB::...` dengan `use Illuminate\Support\Facades\DB;`.
   - Ganti `auth()->id()` menjadi `Auth::id()` dengan `use Illuminate\Support\Facades\Auth;`.
2. `Support/TicketController.php`
   - Ganti `auth()->id()` dengan `Auth::id()` untuk menghilangkan “Undefined method id” dari analyzer.
3. `tests/Feature/ThrottlingTest.php`
   - Ganti `auth()->check()` dengan `Auth::check()`.
4. `tests/Feature/PostTopCachingTest.php`
   - Ubah `pluck()` langsung pada paginator menjadi `$paginator->getCollection()->pluck(...)`.
5. `AppServiceProvider.php`
   - Hindari pemanggilan `->toArray()` langsung pada `$event->notification` (analyzer tidak paham guard `method_exists`). Pakai `call_user_func`/`is_callable`.
   - Untuk login event: pastikan `$event->user` adalah instance `App\Models\User` sebelum dipakai oleh `GamificationService`.
6. `routes/channels.php`
   - Jika `Conversation` sudah benar-benar tidak ada: bungkus registrasi channel conversation dengan `class_exists(\App\Models\Conversation::class)` atau hapus blok channel conversation.
7. `NotificationService.php`
   - Ubah loop `foreach ($admins as $admin)` menjadi `User::query()->...->each(function (User $admin) { ... })` agar tipe `User` jelas tanpa docblock.

## Verifikasi
- Jalankan `php artisan test`.
- Jalankan `npm run build`.
- Uji manual:
  - Klik Logout dari sidebar (harus logout sukses).
  - Akses `http://noteds.test/logout` (harus redirect, tidak 405).

Jika kamu setuju, saya lanjut eksekusi perubahan sesuai langkah di atas.