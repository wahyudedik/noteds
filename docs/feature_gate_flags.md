# Feature Gate Flags & Komponen Terkait

## Flags yang tersedia
- `admin` — akses penuh area admin
- `clipper` — fitur Clipper (submit clips, dashboard)
- `brand` — fitur brand approvals
- `marketplace.seller` — aksi penjual (buat produk, penarikan saldo)
- `marketplace.buyer` — aksi pembeli (belanja marketplace)
- `stocks` — fitur stocks dasar

Definisi flags berada di `resources/js/Composables/useFeatureGate.js`.

## Pemetaan Komponen
- Navigasi
  - `Components/SidebarNav.vue` — disaring berdasarkan flags
  - `Components/BottomNav.vue` — menyembunyikan Profile jika belum login
- Seller Actions
  - `Components/CreateProductModal.vue` — hanya tampil jika `marketplace.seller`
  - `Components/Marketplace/WithdrawalForm.vue` — hanya tampil jika `marketplace.seller`
- Clipper
  - `Pages/Clipper/Clips/Create.vue` — form gated via role clipper (existing)
- Admin Widgets
  - `Components/Admin/QuickActionsPanel.vue` — gated `admin`
  - `Components/Admin/RecentActivitiesWidget.vue` — gated `admin`
  - `Components/Admin/PendingItemsSummary.vue` — gated `admin`

## Cara Pakai
Impor dan gunakan:
```js
import { useFeatureGate } from '@/Composables/useFeatureGate'
const { can } = useFeatureGate()
const allowed = can('marketplace.seller')
```
Render bersyarat:
```vue
<div v-if="allowed">...</div>
```

## Pemeliharaan
- Tambahkan flag baru di `useFeatureGate.js`
- Gunakan flag di komponen yang relevan
- Uji perilaku untuk role yang berbeda (admin/user/clipper/brand)
