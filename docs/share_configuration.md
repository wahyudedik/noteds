# Konfigurasi Tombol Share & Tipe Konten

## Penempatan Tombol Share
- File konfigurasi: `resources/js/config/sharePlacement.ts`
- Opsi:
  - explorer.position: `sidebar` atau `grid_top`
  - groups.position: `below_title`
  - posts.position: `below_actions`
- Aktif/nonaktif per halaman dengan `enabled: boolean`
- Contoh:
```ts
export const sharePlacement = {
  explorer: { enabled: true, position: 'sidebar' },
  groups: { enabled: true, position: 'below_title' },
  posts: { enabled: true, position: 'below_actions' },
};
```

## Tipe Konten & Endpoint Tracking
- Komponen: `SocialShareButtons.vue`
- Prop `shareType` menentukan tipe: `posts`, `groups`, `products`, `stories`, `external`
- Tracking:
  - `products`: mencoba route marketplace `marketplace.products.share`, fallback ke generik `share.track`
  - `posts`, `groups`, `stories`: generik `share.track` (polymorphic)
  - `external`: generik `share.track` (disimpan sebagai `shareable_type='external'`)

## Menambah Tipe Baru
1. Tambahkan model di backend (mis. `App\Models\Story`)
2. Perluas `ShareAnalyticsController::resolveModel` untuk tipe baru
3. Gunakan `SocialShareButtons` dengan `shareType` dan `shareId` tipe baru
4. (Opsional) Tambah route khusus jika diperlukan, dan atur fallback ke generik

## Keamanan & Praktik Baik
- Semua link share dibuka dengan `noopener,noreferrer`
- URL share ditambahkan UTM otomatis untuk analitik
- Copy Link menggunakan Clipboard API dengan fallback
