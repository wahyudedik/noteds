---
name: Update Clipper Pages to ClipperLayout
overview: Update semua halaman clipper yang masih menggunakan AuthenticatedLayout untuk menggunakan ClipperLayout component agar konsisten dengan navigation tabs dan UX yang lebih baik.
todos:
  - id: update_topups_index
    content: Update TopUps/Index.vue untuk use ClipperLayout
    status: completed
  - id: update_topups_payment
    content: Update TopUps/Payment.vue untuk use ClipperLayout
    status: completed
  - id: update_topups_create
    content: Update TopUps/Create.vue untuk use ClipperLayout
    status: completed
  - id: update_clips_edit
    content: Update Clips/Edit.vue untuk use ClipperLayout
    status: completed
  - id: update_clips_create
    content: Update Clips/Create.vue untuk use ClipperLayout
    status: completed
  - id: update_clips_available
    content: Update Clips/AvailableCampaigns.vue untuk use ClipperLayout
    status: completed
  - id: update_profile_show
    content: Update Profile/Show.vue untuk use ClipperLayout
    status: completed
  - id: update_profile_create
    content: Update Profile/Create.vue untuk use ClipperLayout
    status: completed
  - id: update_wallet_clipper
    content: Update Wallet/Clipper.vue untuk use ClipperLayout
    status: completed
  - id: update_wallet_creator
    content: Update Wallet/Creator.vue untuk use ClipperLayout
    status: completed
  - id: update_campaigns_analytics
    content: Update Campaigns/Analytics.vue untuk use ClipperLayout
    status: completed
---

# Update Clipper Pages to ClipperLayout

## Overview

Update 11 halaman clipper yang masih menggunakan `AuthenticatedLayout` untuk menggunakan `ClipperLayout` component. Ini akan memberikan konsistensi navigation tabs dan UX yang lebih baik di semua halaman clipper.

## Files to Update

### 1. TopUps Pages (3 files)

#### `resources/js/Pages/Clipper/TopUps/Index.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

#### `resources/js/Pages/Clipper/TopUps/Payment.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

#### `resources/js/Pages/Clipper/TopUps/Create.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

### 2. Clips Pages (3 files)

#### `resources/js/Pages/Clipper/Clips/Edit.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

#### `resources/js/Pages/Clipper/Clips/Create.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

#### `resources/js/Pages/Clipper/Clips/AvailableCampaigns.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

### 3. Profile Pages (2 files)

#### `resources/js/Pages/Clipper/Profile/Show.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

#### `resources/js/Pages/Clipper/Profile/Create.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

### 4. Wallet Pages (2 files)

#### `resources/js/Pages/Clipper/Wallet/Clipper.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

#### `resources/js/Pages/Clipper/Wallet/Creator.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

### 5. Campaigns Pages (1 file)

#### `resources/js/Pages/Clipper/Campaigns/Analytics.vue`

- Replace import: `AuthenticatedLayout` → `ClipperLayout`
- Replace component: `<AuthenticatedLayout>` → `<ClipperLayout>`
- Keep header slot structure

## Implementation Pattern

Untuk setiap file, lakukan perubahan berikut:

1. **Update import statement:**
   ```javascript
            // Before
            import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
            
            // After
            import ClipperLayout from '@/Layouts/ClipperLayout.vue';
   ```




2. **Update component usage:**
   ```vue
            <!-- Before -->
            <AuthenticatedLayout>
                <template #header>
                    <!-- header content -->
                </template>
                <!-- page content -->
            </AuthenticatedLayout>
            
            <!-- After -->
            <ClipperLayout>
                <template #header>
                    <!-- header content (unchanged) -->
                </template>
                <!-- page content (unchanged) -->
            </ClipperLayout>
   ```




## Notes

- Semua halaman sudah memiliki header slot yang kompatibel dengan ClipperLayout
- ClipperLayout sudah memiliki navigation tabs yang sesuai dengan role (brand/clipper)
- Tidak ada perubahan pada konten atau struktur halaman, hanya layout wrapper
- ClipperLayout sudah handle routing untuk tabs (termasuk Analytics route)

## Testing

Setelah update, verifikasi:

- Navigation tabs muncul dengan benar di semua halaman