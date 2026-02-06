# Feature Gate Flags & Komponen Terkait

## Flags yang tersedia
- `admin` — akses penuh area admin
- `privacy.dashboard` — halaman/fitur dashboard privasi (admin)
- `activity.log` — halaman/fitur audit aktivitas (admin)

Definisi flags berada di `resources/js/Composables/useFeatureGate.js`.

## Pemetaan Komponen
- Navigasi
  - `Components/SidebarNav.vue` — disaring berdasarkan flags
  - `Components/BottomNav.vue` — menyembunyikan Profile jika belum login
- Admin Widgets
  - `Components/Admin/RecentActivitiesWidget.vue` — gated `admin`

## Cara Pakai
Impor dan gunakan:
```js
import { useFeatureGate } from '@/Composables/useFeatureGate'
const { can } = useFeatureGate()
const allowed = can('admin')
```
Render bersyarat:
```vue
<div v-if="allowed">...</div>
```

## Pemeliharaan
- Tambahkan flag baru di `useFeatureGate.js`
- Gunakan flag di komponen yang relevan
- Uji perilaku untuk role yang berbeda (admin/user)
