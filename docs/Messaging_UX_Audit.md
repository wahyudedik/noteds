# Audit UX Halaman Messaging/Conversations

## Heuristic Evaluation (Nielsen)
- Visibility of system status: indikator ketik (typing) dan unread ada; status koneksi/panggilan kurang jelas.
- Match with real-world: istilah “Messages”, “New” sesuai; tombol aksi panggilan bercampur dengan konten chat.
- User control & freedom: belum ada undo untuk penghapusan pesan, keluar/mute ada.
- Consistency & standards: badge unread konsisten; posisi waktu pesan kadang kurang menonjol.
- Error prevention: input pesan belum ada validasi panjang/attachment guidance.
- Recognition over recall: daftar percakapan menampilkan last preview; tidak ada pencarian/filter.
- Flexibility & efficiency: keyboard shortcut tidak terlihat; mobile layout kurang optimal.
- Aesthetic & minimalist: layout bersih; panel panggilan menambah beban visual pada thread.
- Help & documentation: tidak tersedia onboarding/tooltips konteks.

## Pain Points User Journey
- Onboarding: pengguna baru kebingungan dengan panel “Video Call” selalu tampil.
- Navigasi: di mobile, kolom 1/3 sidebar menyulitkan; harus fokus single-pane.
- Penemuan percakapan: tidak ada pencarian/filter/sort, sulit menemukan percakapan lama.
- Loading states: saat memuat batch lama, tidak ada skeleton atau indikator yang informatif.
- Attachment: tidak ada affordance jelas untuk drag-drop/preview sebelum kirim.
- Aksesibilitas: tidak ada peran ARIA/role untuk list dan items, fokus keyboard belum diatur.

## Accessibility (WCAG 2.1)
- Perceivable: kontrast teks vs background cukup; badge biru di latar putih aman, dark mode tersedia.
- Operable: navigasi keyboard belum jelas; focus ring standar belum dipastikan.
- Understandable: label teks jelas; tidak ada deskripsi untuk tombol panggilan.
- Robust: struktur semantik belum memanfaatkan landmark/roles (list, listitem).

## Information Architecture & Visual Hierarchy
- Struktur tiga kolom di desktop baik, namun mobile-first perlu single-pane dengan bottom bar.
- Prioritas visual: nama percakapan, unread count, waktu terakhir, preview; panel call sebaiknya tersendiri/tersembunyi.
- Penanda pemisah waktu/hari di thread akan meningkatkan pemahaman kronologi.

## Rekomendasi Prioritas Tinggi
- Responsive single-pane di mobile; toggle sidebar dan bottom navigation actions.
- Pencarian & filter percakapan; pin/favorite untuk percakapan penting.
- Skeleton loading untuk list dan pesan; indikator “loading older messages”.
- Pemisah tanggal di timeline; unread marker.
- Aksesibilitas: roles (list/listitem), aria-label pada tombol, fokus keyboard teratur.
- Panel panggilan dipindah ke tab/accordion, lazy load saat diperlukan.
- Attachment UX: tombol jelas, dropzone, preview sebelum kirim.

## Wireframe & Mockup (Desain Alternatif)
- Halaman prototipe interaktif tersedia: Messaging/UXPrototype (lihat route /messaging/prototype).
- Fitur: responsive toggle sidebar, bottom bar mobile, skeleton states, contoh aria roles.

## Usability Testing
- Rencana: 5 pengguna dengan skenario menemukan percakapan lama, mengirim pesan + attachment, memulai panggilan.
- Metrik: waktu tugas, error rate, SUS score, feedback kualitatif.
- Performance: target TTI < 2s di percakapan aktif; batch load < 300ms per 20 pesan.
