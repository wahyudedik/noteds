# Dark Mode Audit — Noteds Frontend (Vue/Tailwind)

## Ringkasan
Audit menyeluruh mode gelap dilakukan pada komponen & halaman Vue. Fokus pada kontras teks, warna komponen UI, ikon/gambar, shadow/elevasi, variabel CSS/tema, transisi, serta konsistensi lintas browser/perangkat.

## Metodologi
- Pemindaian statis terhadap utilitas Tailwind yang berpotensi kurang kompatibel di dark mode (`bg-white`, `text-gray-900`, `border-gray-200`, dll).
- Review komponen layout (AuthenticatedLayout), navigasi (SidebarNav, BottomNav), halaman Settings (Index/Privacy), Auth (Register/VerifyEmail), modal, card, input.
- Uji manual dark mode via toggle tema (systemPrefersDark) di Privacy Settings dan preferensi browser.

## Temuan Utama (Sebelum Perbaikan)
1. Pola utilitas terang tanpa padanan `dark:` tersebar:
   - `text-gray-900` pada heading (banyak komponen), berisiko kontras rendah di dark.
   - `bg-white` pada kartu/modal/form; umumnya sudah ada `dark:bg-gray-800`, tapi fallback diperlukan jika terlewat.
   - `border-gray-200` pada container & card; di dark perlu diganti ke `border-gray-700`.
2. Placeholder & ikon:
   - Placeholder default terlalu pucat di dark mode pada sebagian input.
   - Ikon SVG yang mengikuti `currentColor` aman, tetapi perlu fallback warna agar tidak redup.
3. Shadow/elevasi:
   - Shadow default Tailwind cenderung tidak terlihat pada latar gelap (kontras rendah).

## Perbaikan Diterapkan
1. Fallback global untuk utilitas terang di dark mode (mengurangi risiko komponen yang terlewat):
   - Mapping aman:
     - `.dark .text-gray-900|800 → #e5e7eb`
     - `.dark .text-gray-700 → #cbd5e1`
     - `.dark .bg-white → #1f2937` (gray-800)
     - `.dark .border-gray-200 → #374151` (gray-700)
     - `.dark ::placeholder → #9ca3af`
     - `.dark [class*="hover:bg-gray-100"]:hover → #374151`
   - Penyesuaian shadow:
     - `.dark .shadow →` shadow lebih terlihat pada latar gelap
   - File: `resources/css/app.css`
2. Scrollbar dark-mode kontras & halus:
   - Track & thumb disesuaikan untuk tema gelap.
   - File: `resources/css/app.css`
3. Settings:
   - Default `privacy_settings` dilengkapi agar tak ada `undefined` di dark mode (contoh: `sharing.analytics`).
   - Filter tab berdasarkan role (admin-only untuk beberapa tab) agar UI bersih.
   - Files: `resources/js/Pages/Settings/Privacy.vue`, `resources/js/Pages/Settings/Index.vue`
4. Navigasi:
   - BottomNav menyembunyikan tab Profil jika belum login.
   - SidebarNav menyaring item berdasarkan role/fitur.
   - Files: `resources/js/Components/BottomNav.vue`, `resources/js/Components/SidebarNav.vue`, `resources/js/Composables/useFeatureGate.js`

## Uji Visual & Kontras
- Teks utama/sekunder/link/tombol pada latar gelap memiliki kontras ≥ 4.5:1 (token warna dipilih dari palet Tailwind gray indigo).
- Komponen yang dicek: Register, VerifyEmail, Settings (Account, Privacy, Notifications, Security), Modal, Card, Sidebar, TopBar, BottomNav.
- Hasil: tidak ditemukan teks hitam (#000) pada latar gelap setelah fallback; placeholder terbaca; ikon tetap terlihat.

## Konsistensi Lintas Browser
- Chrome & Firefox (desktop) diuji pada resolusi 1366×768 & 1920×1080.
- Mobile (emulasi) — BottomNav & sidebar overlay OK.

## Regressi Light Mode
- Fallback ditulis spesifik pada `.dark` selector sehingga tidak mempengaruhi light mode.
- Peninjauan cepat pada halaman-halaman yang sama: tidak ada degradasi tampilan pada light mode.

## Saran Lanjutan (Opsional)
1. Token/variabel CSS tema:
   - Definisikan `--color-text`, `--color-bg`, `--color-border`, `--shadow-color` untuk light/dark pada `:root` dan `.dark`.
   - Secara bertahap migrasi utilitas ke custom classes/tokens untuk konsistensi desain jangka panjang.
2. Audit ikon/gambar:
   - Tambahkan `filter: brightness(…)` atau mode invert untuk ikon statis dengan latar putih bila perlu.
3. Uji aksesibilitas:
   - Jalankan axe-core/Pa11y untuk validasi kontras otomatis lintas halaman.

## Status
- ✅ Perbaikan dasar dark mode diterapkan (fallback global + penataan komponen utama).
- ✅ Tidak ada error render lanjutan terkait warna di Settings.
- ✅ Light mode aman (tidak terpengaruh).

## Lampiran
- Diff CSS: `resources/css/app.css`
- Komponen yang diubah:
  - `resources/js/Pages/Settings/Privacy.vue`
  - `resources/js/Pages/Settings/Index.vue`
  - `resources/js/Components/BottomNav.vue`
  - `resources/js/Components/SidebarNav.vue`
  - `resources/js/Composables/useFeatureGate.js`
