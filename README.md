# 💡 **Noteds** — Catatan & Ide Digital yang Menghasilkan Uang

> **Marketplace platform untuk catatan digital dengan sistem komisi dan monetisasi yang adil.**

### ✍️ Branding

**Noteds** — Tempat Menulis, Menjual, dan Menemukan Ide Digital yang Menghasilkan Uang.

---

## 🌟 Platform Overview

### 🎯 Tujuan Platform

Platform di mana pengguna bisa:
- ✅ Menulis dan menyimpan catatan pribadi
- ✅ Menjual template atau ide digital mereka 
- ✅ Membeli catatan/ide/template milik orang lain 
- ✅ Menghasilkan uang dari setiap catatan yang dibeli orang 

### 💰 Monetisasi

- ✅ **Komisi 20%** dari setiap transaksi berbayar
- ✅ **Free notes:** 0% commission (mendorong knowledge sharing)
- ✅ **Paid notes:** Platform fee (default: 20%, configurable) + Creator commission (optional, untuk original creator)
- ⚠️ **Scarcity Mode (Default):** Setiap user hanya bisa beli note 1x, tapi note bisa dijual ke user berbeda (ownership transfer)
- ⚠️ **Standard Mode:** Multiple sales allowed, buyer tidak bisa resell (lihat detail di bagian Note Selling Rules)
- ⚠️ **Original creator selalu dapat komisi** di setiap penjualan (default: 0%, bisa di-setting di admin)
- ⚠️ **Withdraw:** Approval admin minimal 24 jam (tidak otomatis)
- ✅ **All features are free** - Premium subscription has been removed, all users have full access
- ✅ Iklan catatan unggulan (Featured Notes) — Platform untuk mempromosikan note dengan bayar per iklan
  - Lokasi iklan: Landing Hero, Landing Carousel, Marketplace Banner, Marketplace Grid, Popup Welcome, Popup Exit Intent, Popup Interstitial
  - Durasi: 7, 14, atau 30 hari
  - Auto-approve untuk verified sellers
  - Analytics tracking (impressions, clicks, CTR, ROI)
  - Admin approval system dengan refund jika reject

### 🧱 Tech Stack

**Backend:**
- Laravel 12 (PHP 8.2+)
- MySQL
- Spatie Permission
- Laravel Breeze

**Frontend:**
- Blade Templates 
- Tailwind CSS
- Alpine.js
- Vite 6.4.1 
- Quill (Rich Text Editor)
- SweetAlert2 (Notifications)
- Laravel Echo (Realtime Notifications)
- Pusher/Ably (Broadcasting)

**Tools:**
- Midtrans (Payment Gateway)
- Laravel Telescope (Debugging & Query Monitoring)
- Pest (Testing)
- Laravel Queue (Background Jobs)
- Laravel Scheduler (Cron Jobs)
- Redis (Caching - Optional)
- Intervention Image (Image Processing - Optional)

**Database:** All tables use **UUID** primary keys for security

---

## 📚 Documentation

### Quick Start
- **[LOCAL_SETUP.md](LOCAL_SETUP.md)** — Complete local development setup guide
- **[VPS_SETUP.md](VPS_SETUP.md)** — VPS deployment & production setup (manual configuration)
- **[PRODUCTION_SETUP.md](PRODUCTION_SETUP.md)** — Production setup guide for Ubuntu & aaPanel (recommended)
- **[TASKLIST.md](TASKLIST.md)** — Development phases & task tracking
- **[PERFORMANCE_SETUP.md](PERFORMANCE_SETUP.md)** — Performance optimization setup guide (Redis, CDN, Image Processing, Telescope)
- **[SECURITY.md](SECURITY.md)** — Security hardening guide dan best practices

### Key Features

**Marketplace Platform (Current):** 
- ✅ User authentication (Breeze)
- ✅ Notes CRUD with tagging & Rich Text Editor (Quill)
- ✅ Marketplace with search & filters
- ✅ Dynamic tax rules (country/category) & checkout tax breakdown
- ✅ Minimum pricing & recommended price guidance (real-time on seller form)
- ✅ Wallet system & transactions
- ✅ Withdraw management
- ✅ Rating & reviews
- ✅ Public profiles with avatar upload & share functionality
- ✅ **All users have free access to all features** - Premium subscription feature has been removed
  - All users can create unlimited notes
  - All users have access to advanced analytics
  - All users can upload files up to 20MB
  - All users have access to workspace management
  - All users have access to collections, wishlist, and buyer analytics
- ✅ File upload limits: All users (20MB)
- ✅ Referral system
- ✅ Notification system (SweetAlert2)
- ✅ Support ticket system
- ✅ Admin panel with comprehensive analytics (Telescope)
- ✅ CMS (FAQ, dynamic pages)
- ✅ Marketing simulators
- ✅ Multi-Tier Content Protection (Preview, secure file uploads, download control)
- ✅ **Content Protection Settings** - Admin-configurable anti-copy, anti-AI, and security features
  - 25+ protection features with on/off toggles
  - Disable text selection, right-click, keyboard shortcuts, copy/paste
  - Screenshot protection (Print Screen, Snipping Tool, mobile)
  - AI bot detection (ChatGPT, Claude, Perplexity, etc.)
  - Headless browser detection (Selenium, Puppeteer, Playwright)
  - Mouse movement and click pattern analysis
  - Clipboard monitoring and periodic clearing
  - DevTools detection and blocking
  - Blur overlay protection
  - All features disabled by default, can be enabled via admin settings
  - **Smart exclusion**: Protection automatically excludes form pages (create/edit note) and rich text editors to allow normal editing
- ✅ **Rich Media & Gallery Features**
  - Video preview upload with automatic thumbnail generation (max 2 minutes)
  - Audio preview player for audio ecosystem notes
  - Interactive code preview with syntax highlighting (Prism.js)
  - 3D model viewer with model-viewer support (GLB/GLTF formats)
  - PDF preview with page navigation and zoom controls (PDF.js)
  - Advanced media gallery with lightbox, zoom, and swipe gestures
  - Support for multiple thumbnails per note with responsive grid layout
- ✅ **PWA Support** - Progressive Web App dengan offline support
  - Installable sebagai aplikasi mobile
  - Service worker untuk caching dan offline access
  - Install prompt banner
  - Manifest dengan shortcuts dan icons
- ✅ **Dark Mode** - Toggle dark/light mode dengan persistent preference
  - CSS variables untuk smooth transitions
  - LocalStorage untuk menyimpan preferensi
  - Auto-detect system preference
- ✅ **Performance Optimizations**
  - Database indexes untuk query optimization
  - Redis cache support (auto-detect, fallback ke database)
  - Query optimization dengan select specific columns
  - Eager loading optimization dengan limits
  - Caching untuk popular notes, featured content, tags
  - Image lazy loading untuk semua images
  - Responsive images dengan srcset support
  - CDN support untuk static assets
  - Image processing service untuk multiple sizes (thumbnail, medium, large)
  - Laravel Telescope query monitoring dengan slow query detection
- ✅ Internationalization (i18n): 3 languages (EN, ID, AR), 2 currencies (USD, IDR)
- ✅ Featured Notes Advertising System
  - Seller can request featured placement for their notes
  - Multiple locations: Landing Hero, Carousel, Marketplace Banner/Grid, Popup modals
  - Pricing per location & duration (configurable in admin settings)
  - Auto-approve for verified sellers
  - Analytics tracking (impressions, clicks, CTR, ROI)
  - Admin approval system with refund if rejected
- ✅ **Workspace System** - Platform masa depan untuk plugin-plugin keren
  - Multi-workspace support untuk semua users
  - Folder organization dalam workspace
  - AI Features hanya dapat diakses dalam workspace context
  - Navigation update dengan Workspaces link
  - Sidebar AI Features menu di workspace
- ✅ **Admin Full Access** - Admin memiliki akses penuh ke semua fitur
  - Admin dapat membeli dan menjual notes
  - Admin dapat menggunakan semua fitur seller dan buyer
  - Admin memiliki premium access otomatis
  - Admin dapat mengakses semua fitur AI tanpa premium
- ✅ Note History & Versioning System
  - Buyer history untuk seller (list semua buyer yang pernah membeli)
  - Update history dengan versioning (detail perubahan setiap update)
  - Prevent delete jika note sudah dijual
- ✅ Sale Mode System
  - **Scarcity Mode**: One-time purchase, buyer bisa resell, original creator dapat komisi, grace period untuk repurchase
  - **Standard Mode**: Multiple sales, buyer tidak bisa resell, tidak ada komisi, ownership tetap dengan seller
  - Grace period untuk pembelian ulang (configurable, default: 30 hari)
  - Relist price multiplier untuk pembelian ulang setelah grace period (default: 1.5x)
  - Buyer bisa set harga custom saat resell
  - Admin analytics & repurchase report dashboard
  - Comprehensive test suite (unit & feature tests)
- ✅ Resell Flow & One-Time Sale System
  - Original creator commission tracking
  - One-time sale rule (buyer yang sudah menjual tidak bisa akses lagi)
  - Ownership transfer dengan access control
- ✅ Collections Enhancement
  - Add purchased notes to collections
  - Dropdown untuk memilih purchased notes
- ✅ Duplicate content protection & resale guard (content hashing + purchase history checks)

- ✅ Buyer Analytics Dashboard
  - Purchase statistics (total purchased, total spent)
  - Download statistics
  - Completion rate tracking
  - Recent purchases & categories
- ✅ Identity Verification (KYC)
  - Agreement consent during registration
  - KTP & selfie upload with secure private storage
  - Admin approval workflow (required for sellers)
  - Document download for admin review
  - Verification status tracking
- ✅ Ecosystem Creative (Envato-like)
  - Elements: Unlimited creative subscription
  - AudioJungle: Music tracks & sound effects
  - Code: Plugins, code, scripts
  - GraphicRiver: Graphic assets & designs
  - PhotoDune: Royalty-free stock photography
  - Themeforest: Premium website templates
  - VideoHive: Videos, templates, motion graphics
  - 3DOcean: 3D models, textures, render setups
  - Marketplace filtering by ecosystem category
- ✅ Studio Order Flow (Service Marketplace)
  - Brief creation by buyers
  - Quote system with milestones (admin/vendor)
  - Escrow funding, release, and refund
  - Vendor role & dedicated dashboard
  - Platform fee on escrow release (configurable)
  - Order activity timeline
  - SLA reminders (milestone due, funding reminders)
  - Bulk vendor assignment (admin)
  - Email notifications (toggleable per event)
  - Realtime notifications via Echo/Broadcasting
- ✅ Note Time & Language Management
  - Scheduled publish (auto-publish at specified time)
  - Language specification per note
  - Marketplace filtering by language
- ✅ System Health Monitoring (Admin)
  - Database, Queue, Cache, Scheduler health checks
  - Broadcaster configuration verification
  - Queue jobs monitoring (pending/failed)
  - Scheduler run detection
  - Critical component alerts with notifications

**New Features (2025):**
- ✅ **Refund System** — Buyers can request refunds, admins can approve/reject with full workflow
- ✅ **Note Bundles** — Create and sell bundles of multiple notes with discounts
- ✅ **Gift Notes** — Send notes as gifts to other users with email notifications
- ✅ **Comments System** — Threaded comments with nested replies and likes
- ✅ **Reactions System** — 5 reaction types (Like, Love, Helpful, Insightful, Thanks) with real-time updates
- ✅ **Q&A System** — Ask questions about notes, sellers can answer, mark helpful feature
- ✅ **Note Templates** — Create reusable templates for quick note creation
- ✅ **Note Series** — Organize notes into series with ordering
- ✅ **Categories** — Hierarchical category system for better organization
- ✅ **Activity Feed** — Track and view user activities with polymorphic relationships
- ✅ **In-App Messaging** — Direct messaging between users with conversation threads
- ✅ **Webhooks** (Premium) — Create webhooks for events (note.purchased, etc.)
- ✅ **Recently Viewed Notes** (Premium) — Track and view recently viewed notes
- ✅ **Draft & Scheduled Publishing** — Save drafts and schedule notes for future publishing with auto-publish command
- ✅ **User Verification** — Admin can verify users with verified badges
- ✅ **Video Previews** — Upload video preview for notes (max 2 minutes) with auto-generated thumbnails and hover-to-play functionality
- ✅ **Rich Media Support** — Enhanced media previews for different content types:
  - Audio preview player for audio notes (AudioJungle ecosystem)
  - Interactive code preview with syntax highlighting for code notes (Code ecosystem)
  - 3D model viewer using model-viewer for 3D assets (3DOcean ecosystem)
  - PDF preview with page navigation and zoom controls
- ✅ **Media Gallery** — Advanced image gallery with lightbox viewer:
  - Multiple image gallery for notes with responsive grid layout
  - Fullscreen lightbox viewer with smooth transitions
  - Image zoom functionality (1x to 3x) with pan support
  - Swipe gestures for mobile navigation
  - Keyboard navigation (Arrow keys, +/- for zoom, Escape to close)
  - Touch-friendly controls optimized for mobile devices

**Note:** Semua fitur AI telah dihapus dari aplikasi. Platform sekarang fokus pada marketplace dan fitur premium untuk seller dan buyer.

---

## 🎮 Marketing Tools

**✅ All Marketing Tools Implemented:**

**Top 3 Priorities:**
1. ✅ **Earnings Calculator** — Simulate seller earnings potential
2. ✅ **Referral ROI Calculator** — Track referral program ROI
3. ✅ **Premium vs Basic Comparison** — Interactive plan comparison

**Additional Simulators:**
- ✅ Wallet Simulator — Track balance and transactions
- ✅ Transaction Flow Visualizer — Visualize payment process
- ✅ Price Benchmark Tool — Compare note prices with market average
- ✅ Marketplace Preview — Explore marketplace features

**Note:** AI-related simulators (AI Summary Demo, Tag Suggestion) have been removed as all AI features have been removed from the application.

### 🌟 Fitur Utama Ekosistem Kreatif

- **Elements (Langganan Creative Unlimited):**
  - Langganan kreatif tak terbatas (unlimited)
  - Akses ke jutaan aset kreatif dengan satu biaya rendah
  - Cocok untuk kreator yang butuh banyak aset cepat dengan lisensi aman

- **AudioJungle (Audio & SFX):**
  - Ratusan ribu trek musik dan efek suara
  - Dibuat oleh komunitas profesional musik global
  - Cocok untuk video, podcast, game, dan iklan

- **Code (Kode & Plugin):**
  - Ribuan plugin, kode, dan skrip
  - Dukungan framework populer: Bootstrap, JavaScript, PHP, WordPress, HTML5, dsb.
  - Cocok untuk mempercepat pengembangan produk digital

- **GraphicRiver (Grafis & Desain):**
  - Aset grafis dan desain siap pakai
  - Contoh: template logo, font, Photoshop actions, materi cetak

- **PhotoDune (Fotografi Stok):**
  - Koleksi besar fotografi stok bebas royalti (royalty-free)
  - Siap digunakan untuk berbagai jenis proyek

- **Themeforest (Template Premium):**
  - Marketplace template situs web premium terkemuka
  - Menawarkan tema untuk WordPress, Shopify, dan lainnya

- **VideoHive (Video & Motion Graphics):**
  - Marketplace untuk semua kebutuhan video
  - Koleksi besar video, template, dan motion graphics bebas royalti

- **3DOcean (3D Assets):**
  - Komunitas global untuk semua hal 3D
  - Menawarkan model 3D, tekstur, dan render setups

### 🎨 Studio — Service Marketplace

**Studio** adalah marketplace jasa kreatif (mirip Envato Studio) yang memungkinkan buyer memesan layanan custom dari vendor terverifikasi.

**Fitur Utama:**
- ✅ **Brief Creation:** Buyer membuat brief dengan title, description, dan budget
- ✅ **Quote System:** Admin/Vendor mengirim quote dengan milestones breakdown
- ✅ **Escrow System:** 
  - Buyer fund escrow dari wallet
  - Release per milestone dengan validasi
  - Refund jika cancel
  - Platform fee deduction (configurable, default: 10%)
- ✅ **Vendor Dashboard:** 
  - Daftar orders assigned
  - Quotes yang dikirim
  - Milestone tracking
- ✅ **Admin Features:**
  - Manual vendor assignment
  - Bulk assign orders
  - Unassigned orders list
  - Platform fee configuration
- ✅ **Notifications:**
  - Email notifications (toggleable per event)
  - Realtime in-app notifications via Echo
  - Automated alerts untuk quote, escrow, milestone events
- ✅ **SLA Reminders:**
  - Milestone due reminders
  - Funding reminders untuk quoted orders
  - Automated hourly scheduler
- ✅ **Order Timeline:** Activity log untuk semua order actions

**Workflow:**
1. Buyer creates brief → Status: `submitted`
2. Admin/Vendor creates quote with milestones → Status: `quoted`
3. Buyer accepts quote → Vendor assigned, milestones locked
4. Buyer funds escrow → Status: `in_progress`
5. Vendor completes milestone → Buyer releases escrow
6. Platform fee deducted, vendor receives net amount
7. Order completed when all milestones done

---

## 💡 Monetization & Content Strategy

### 💚 Free vs Paid Notes Strategy

**Default: Free Sharing**
- Price field default: `Rp 0` (free)
- Platform supports **knowledge sharing** tanpa mengharuskan monetisasi
- Free notes tetap bisa di-rate & di-review untuk visibility

**Free Note View Monetization:**
- ✅ Free notes (price = 0) dapat menghasilkan revenue dari views
- ✅ 0.01 IDR per view dikreditkan ke wallet pemilik note
- ✅ **Sistem Persetujuan Monetization:**
  - Free notes memerlukan persetujuan admin ATAU seller harus memiliki minimal 1 penjualan berhasil
  - Auto-approved jika seller memiliki minimal 1 transaksi berhasil (dari note manapun)
  - Admin dapat approve/reject monetization secara manual di admin panel
  - View monetization hanya bekerja untuk free notes yang sudah di-approve
- ✅ Bot detection dan rate limiting untuk mencegah fake views
- ✅ Browser fingerprinting untuk validasi view
- ✅ Admin view history dengan filtering dan export CSV
- ✅ Scheduled validation command untuk pending views (hourly)
- ✅ View revenue tracking dan analytics

**Paid Notes Benefits:**
- Exclusive content (premium)
- Downloadable resources (PDF, templates, files)
- Priority support dari seller
- Direct access ke seller knowledge

**Seller Choice:**
- Option 1: **Free** → Share untuk community building & reputation
- Option 2: **Paid** → Monetize expertise & time investment
- Option 3: **Hybrid** → Mix gratis untuk branding, premium untuk advanced content

**⚠️ Important Note Selling Rules:**

**Scarcity Mode (Default):**
- ✅ **Setiap user hanya bisa membeli note 1x** (per user, bukan global)
- ✅ **Note bisa dijual ke banyak user berbeda** (user A jual ke B, B jual ke C, dst)
- ✅ Setelah pembelian, **ownership note transfer ke buyer** (buyer bisa jual lagi)
- ✅ **Original creator selalu dapat komisi** di setiap penjualan (jika di-setting)
- ✅ Buyer bisa **resell dengan harga custom** setelah membeli
- ✅ Buyer bisa **repurchase** setelah menjual (dalam grace period dengan harga original, setelah grace period dengan harga premium)

**Standard Mode:**
- ✅ **Multiple sales allowed** - Seller bisa jual ke banyak buyer sekaligus
- ✅ **Buyer tidak bisa resell** - Ownership tetap dengan seller
- ✅ **Tidak ada komisi** - Seller mendapat full amount (minus tax)
- ✅ **Ownership tetap dengan seller** - Buyer hanya mendapatkan akses, bukan ownership

**General:**
- ✅ Jika ingin digunakan banyak orang sekaligus, **gunakan Standard Mode atau gratiskan saja** (bisa dilihat banyak orang)

**Commission System:**

**Scarcity Mode:**
- **Platform Fee**: Deducted dari **setiap transaksi** (default: 20%, configurable di admin settings)
- **Creator Commission**: **Selalu untuk original creator** (pembuat pertama) di **setiap penjualan**
  - Default: 0% (bisa di-setting di admin)
  - Original creator dapat komisi di **setiap transaksi**, bukan hanya pertama kali
  - **Penjual kedua dan seterusnya tidak dapat komisi** (hanya original creator yang dapat komisi)

**Standard Mode:**
- **Platform Fee**: 0% (tidak ada platform fee)
- **Creator Commission**: 0% (tidak ada creator commission)
- **Seller Amount**: Full amount (minus tax only)

**Free notes**: 0% commission (encourage sharing)

**Example Flow - Scarcity Mode (Platform Fee: 20%, Creator Commission: 10%):**
- **User A (creator) jual ke User B (Rp 100.000):**
  - A dapat: Rp 90.000 (seller amount Rp 80.000 + creator commission Rp 10.000)
  - Platform dapat: Rp 20.000
  - Note ownership transfer ke User B
  
- **User B jual ke User C (Rp 100.000):**
  - A (creator) dapat: Rp 10.000 (creator commission)
  - B dapat: Rp 70.000 (seller amount = 100% - 20% platform - 10% creator commission)
  - Platform dapat: Rp 20.000
  - Note ownership transfer ke User C
  
- **User C jual ke User D (Rp 100.000):**
  - A (creator) dapat: Rp 10.000 (creator commission - selalu dapat di setiap penjualan)
  - C dapat: Rp 70.000 (seller amount)
  - Platform dapat: Rp 20.000
  - Note ownership transfer ke User D

- **Setiap user hanya bisa beli note ini 1x**, tapi **note bisa dijual ke user berbeda terus menerus**
- **Original creator (User A) selalu dapat komisi di setiap penjualan** (jika di-setting)

**Example Flow - Standard Mode:**
- **User A (seller) jual ke User B (Rp 100.000):**
  - A dapat: Rp 100.000 (full amount, minus tax)
  - Platform dapat: Tax amount only
  - Ownership tetap dengan A
  
- **User A (seller) jual ke User C (Rp 100.000):**
  - A dapat: Rp 100.000 (full amount, minus tax)
  - Platform dapat: Tax amount only
  - Ownership tetap dengan A (B dan C bisa akses, tapi tidak bisa resell)

**Withdraw System:**
- ⚠️ Withdraw memerlukan **approval admin** minimal **24 jam** setelah request
- ⚠️ Tidak bisa langsung otomatis masuk rekening (harus menunggu approval admin)
- Admin dapat approve/reject setelah 24 jam berlalu

### 💡 Premium Content Protection Strategy

**Problem:** User bisa melihat full content gratis di marketplace, mengurangi insentif pembelian.

**Solution:** Multi-Tier Content System dengan preview terbatas (see [TASKLIST.md](TASKLIST.md) for implementation)

**File Upload Security:**
- ✅ Safe extensions only: PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, PNG, GIF, XLS, PPT, etc.
- ✅ Blocked dangerous files: EXE, BAT, SH, PHP, JS, HTML, PS1, VBS, SCR, JAR
- ✅ MIME type validation + size limits (max 50MB per file)
- ⚠️ Optional: Virus scanning (ClamAV integration)

**Content Protection Settings (Admin Configurable):**
- ✅ **25+ Protection Features** - All disabled by default, can be enabled via Admin → Settings → Content Protection
- ✅ **Anti-Copy Protection:**
  - Disable text selection, right-click, keyboard shortcuts (Ctrl+C, Ctrl+V, Ctrl+P, etc.)
  - Disable copy/cut/paste events, drag & drop, image saving
  - Disable print, view source (Ctrl+U), F12, DevTools shortcuts
- ✅ **Screenshot Protection:**
  - Disable Print Screen key and Windows+Print Screen
  - Disable Snipping Tool (Windows+Shift+S)
  - Detect window blur and tab visibility changes
  - Mobile screenshot prevention (iOS/Android)
  - Blur overlay when screenshot detected
- ✅ **AI & Bot Detection:**
  - Detect AI bots from User-Agent (ChatGPT, Claude, Perplexity, etc.)
  - Detect headless browsers (Selenium, Puppeteer, Playwright)
  - Analyze mouse movement patterns (detect AI-like behavior)
  - Analyze click patterns (detect too-consistent AI behavior)
  - Screen recording detection using canvas fingerprinting
- ✅ **Advanced Protection:**
  - Clipboard monitoring and periodic clearing
  - DevTools detection and warning
  - Console blocking
  - Visibility change detection

#### 🔄 Alternative Monetization Models

1. **Freemium + Premium Bundle**
2. **Pay-Per-View (PPV)**
3. **Subscription Access**
4. **Hybrid Model** (Recommended)

---

## 🚀 Current Development Phase

**Current Phase:** ✅ Marketplace v1.0 (Active Development)

### 📅 Roadmap

| Phase | Focus | Timeline | Status |
|-------|-------|----------|--------|
| **Phase 0: Marketplace v1.0** | Catatan + Marketplace + Wallet | 2025 Q4 | ✅ In Progress |
| **Phase 1: Team Workspace** | Multi user + kolaborasi real-time | 2026 Q1 | 🚧 Planned |
| **Phase 2: Integrasi & API** | Integrasi dengan tools eksternal | 2026 Q2 | 🚧 Planned |
| **Phase 3: Marketplace Plugin** | Pengembang eksternal bisa menambah modul | 2026 Q3 | 🚧 Planned |
| **Phase 4: AI Memory Platform** | AI Q&A, Semantic Search, Insights (Premium Plugin) | 🚧 Coming Soon |

### Tech Stack Business (Future)

- **Database:** PostgreSQL
- **Search:** Meilisearch
- **Frontend:** Vue 3 + Pinia
- **AI:** Coming Soon (AI Memory Platform Plugin)
- **Infrastructure:** Docker Compose

See [TASKLIST.md](TASKLIST.md) for full roadmap

---

## 🛠️ Development Quick Commands

### Local Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
```

### Daily Development
```bash
php artisan serve
npm run build  # with Herd
php artisan queue:work  # For background jobs (emails, notifications)
php artisan schedule:run  # For scheduled tasks (or setup cron)
```

**Important:** 
- Run `php artisan queue:work` for email notifications and broadcasting
- Setup cron for scheduler: `* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1`
- Configure broadcaster (Pusher/Ably) in `.env` for realtime notifications

See [LOCAL_SETUP.md](LOCAL_SETUP.md) for detailed instructions

### Database Seeding

Sistem menggunakan banyak seeder untuk mengisi database dengan data awal. Berikut daftar lengkap seeder yang tersedia:

**Core System Seeders:**
- `RoleSeeder` - Roles dan permissions (admin, seller, buyer, vendor)
- `BadgeSeeder` - Gamification badges (milestone, quality, community)
- `LevelSeeder` - Seller/Buyer level system (Bronze, Silver, Gold, Platinum, Diamond)
- `AdminSeeder` - Admin users dengan workspace testing
- `SettingSeeder` - System settings dan configurations
- `ExchangeRateSeeder` - Exchange rates untuk multi-currency
- `CommissionTierSeeder` - Tiered commission system
- `TaxRuleSeeder` - Dynamic tax rules (country/category based)
- `UserSeeder` - Sample users untuk testing
- `WalletSeeder` - Wallet instances untuk users
- `ReferralCodeSeeder` - Referral codes
- `ReferralSeeder` - Referral relationships

**Workspace & Content Seeders:**
- `WorkspaceSeeder` - Workspaces untuk testing
- `WorkspaceCollaborationSeeder` - Workspace members dan collaborations
- `FolderSeeder` - Folders dalam workspaces
- `NoteSeeder` - Sample notes dengan berbagai ecosystem categories
- `StudyMaterialSeeder` - Study materials
- `DocumentationSeeder` - Documentation entries (22+ entries)
- `CategorySeeder` - Hierarchical category system
- `NoteTemplateSeeder` - Reusable note templates
- `NoteSeriesSeeder` - Note series organization

**Marketplace & Commerce Seeders:**
- `TransactionSeeder` - Sample transactions
- `MonetizationApprovalSeeder` - Monetization approvals untuk free notes
- `PurchasedNoteSeeder` - Purchased notes history
- `NoteEngagementSeeder` - Note engagement metrics
- `FeaturedNoteSeeder` - Featured notes advertising
- `NoteBundleSeeder` - Note bundles
- `RefundSeeder` - Refund requests dan history
- `GiftNoteSeeder` - Gift notes
- `WithdrawSeeder` - Withdrawal requests

**Social & Community Seeders:**
- `NoteReviewSeeder` - Note reviews dan ratings
- `NoteCommentSeeder` - Threaded comments
- `NoteReactionSeeder` - Reactions (Like, Love, Helpful, etc.)
- `NoteQuestionSeeder` - Q&A system
- `SocialFeatureSeeder` - Social features (shares, likes)
- `ActivitySeeder` - Activity feed entries
- `MessageSeeder` - In-app messaging conversations
- `AppNotificationSeeder` - App notifications

**CMS & Content Seeders:**
- `LandingPageSectionSeeder` - Landing page sections
- `CmsPageSeeder` - CMS pages
- `FaqSeeder` - FAQ entries
- `SocialMediaLinkSeeder` - Social media links

**Studio & Services Seeders:**
- `StudioSeeder` - Studio orders (service marketplace)
- `SupportSeeder` - Support tickets

**Advanced Features Seeders:**
- `NoteCollaborationSeeder` - Note collaborations
- `NoteReportSeeder` - Note reports
- `WebhookSeeder` - Webhook configurations

**Disabled Seeders:**
- `AiAnalysisSeeder` - AI features are now workspace-based (commented out)

**Menjalankan Seeder:**
```bash
# Seed semua data
php artisan db:seed

# Seed seeder spesifik
php artisan db:seed --class=BadgeSeeder
php artisan db:seed --class=LevelSeeder

# Fresh migration + seed
php artisan migrate:fresh --seed
```

**Urutan Seeding:**
Seeder dijalankan dalam urutan tertentu di `DatabaseSeeder.php` untuk memastikan dependencies terpenuhi:
1. Core system (Roles, Badges, Levels, Admin)
2. Settings & configurations
3. Users & wallets
4. Workspaces & folders
5. Notes & content
6. Transactions & commerce
7. Social features
8. CMS content

### Testing
```bash
# Run all tests
./vendor/bin/pest

# Run Sale Mode tests specifically
php artisan test --filter=SaleMode

# Run specific test file
php artisan test tests/Unit/NoteSaleModeTest.php
php artisan test tests/Feature/SaleModeScarcityPurchaseTest.php

# Code style
composer pint
```

---

## 🔒 Security Features

- ✅ **UUID** untuk semua primary keys (prevent enumeration)
- ✅ **Spatie Permission** untuk role-based access control
- ✅ **HTTPS** mandatory di production
- ✅ **Security Headers** (CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy)
- ✅ **Rate Limiting** pada sensitive endpoints (purchase, wallet, withdraw, escrow, quotes)
- ✅ **Input Sanitization** middleware untuk semua request
- ✅ **CSRF** protection on all forms (exempt untuk Midtrans webhook)
- ✅ **SQL Injection** prevention via Eloquent ORM dan parameterized queries
- ✅ **XSS** protection via Blade escaping dan Content Security Policy
- ✅ **File Upload Security** dengan:
  - Extension whitelist dan blacklist
  - MIME type validation
  - Magic bytes validation untuk images
  - Filename sanitization
  - Double extension detection
  - File size limits
- ✅ **Identity Verification (KYC)** dengan secure private storage untuk dokumen sensitif
- ✅ **Private disk storage** untuk KTP dan selfie (tidak accessible via public URL)
- ✅ **Security Logging** untuk suspicious activities (file upload failures, MIME mismatches)
- ✅ **Session Security** dengan secure cookies dan HTTP-only flags

---

## 🏥 System Health & Monitoring

**Admin System Health Dashboard** (`/admin/system-health`) menyediakan monitoring real-time untuk komponen kritis sistem:

**Health Checks:**
- ✅ **Database:** Connection status, driver info
- ✅ **Queue:** Driver status, pending/failed jobs count, worker detection
- ✅ **Cache:** Driver test, read/write verification
- ✅ **Scheduler:** Last run detection, scheduled commands count
- ✅ **Broadcaster:** Configuration verification (Pusher/Ably), test connection

**Monitoring Features:**
- ✅ **Queue Jobs:** Real-time count of pending and failed jobs
- ✅ **Scheduler Detection:** Automatic marker when `schedule:run` executes
- ✅ **Critical Alerts:** Automatic notifications to admins when components fail
- ✅ **Rate Limiting:** Alerts sent max once per hour to prevent spam

**Alert System:**
- ✅ **Critical Alerts:** Database/Queue down → In-app notification to all admins
- ✅ **Warning Alerts:** Scheduler not running, failed jobs detected
- ✅ **Visual Indicators:** Color-coded badges (Healthy/Warning/Error)

**Setup Instructions:**
- Broadcaster configuration guide included in dashboard
- Cron setup instructions for scheduler
- Queue worker commands and troubleshooting

---

## 📊 Project Status

**Completed:**
- ✅ FASE 1: Setup & Authentication
- ✅ FASE 2: Notes Module & Tagging
- ✅ FASE 3: Marketplace
- ✅ FASE 4: Wallet & Transactions
- ✅ FASE 5: Withdraw & Admin Panel
- ✅ FASE 6: Rating & Reviews
- ✅ FASE 7: Premium Plans
- ✅ Referral System
- ✅ Notification System (SweetAlert2)
- ✅ Admin Manual Subscription Creation
- ✅ Admin Dashboard Analytics (Wallet, Referral, Notes, Revenue, Top Performers)
- ✅ CMS (FAQ & Dynamic Pages)
- ✅ Marketing Simulators
- ✅ Rich Text Editor (Quill) for Notes
- ✅ Tag deletion bug fix
- ✅ Multi-Tier Content Protection (Preview, File Upload, Download Control, Trust Indicators)
- ✅ Content Protection Settings (25+ admin-configurable anti-copy, anti-AI, and security features)
- ✅ Featured Notes Advertising System (Landing page, Marketplace, Popup modals)
- ✅ Seller Analytics Dashboard (Impressions, Clicks, CTR, ROI)
- ✅ Auto-approve Featured Notes untuk Premium Users
- ✅ Note History & Versioning System (Buyer history, Update history, Prevent delete)
- ✅ Collections Enhancement (Add Purchased Notes)
- ✅ Resell Flow & One-Time Sale System (Original creator commission, Access control)
- ✅ Profile Features (Avatar upload, Share functionality, Open Graph tags)
- ✅ Buyer Analytics Dashboard (Purchase stats, Downloads, Completion rate)
- ✅ Dynamic Tax & Pricing Controls (Tax rules, pricing guidance panel, tax notifications)
- ✅ Tiered Commission System dengan admin reporting dashboard
- ✅ Subscription auto-renew flow dengan sufficient/insufficient balance handling & notifications
- ✅ Sale Mode System (Scarcity & Standard modes dengan repurchase, resale, analytics)
- ✅ Comprehensive Documentation System (22 documentation entries via DocumentationSeeder)
- ✅ Identity Verification (KYC) dengan admin approval workflow
- ✅ Ecosystem Creative (8 categories: Elements, AudioJungle, Code, GraphicRiver, PhotoDune, Themeforest, VideoHive, 3DOcean)
- ✅ Studio Order Flow (Brief, Quote, Escrow, Milestones, Vendor dashboard)
- ✅ Note Time & Language Management (Scheduled publish, language filtering)
- ✅ System Health Monitoring dengan realtime alerts
- ✅ Realtime Notifications (Broadcast + Echo integration)
- ✅ Refund System (Full workflow dengan admin approval)
- ✅ Note Bundles (Create, view, purchase bundles)
- ✅ Gift Notes (Send gifts dengan email notifications)
- ✅ Comments System (Threaded dengan nested replies)
- ✅ Reactions System (5 types dengan real-time updates)
- ✅ Q&A System (Questions & answers dengan helpful marking)
- ✅ Note Templates (Reusable templates)
- ✅ Note Series (Organize notes into series)
- ✅ Categories (Hierarchical category system)
- ✅ Activity Feed (User activity tracking)
- ✅ In-App Messaging (Direct messaging dengan conversations)
- ✅ Webhooks (Premium feature untuk event tracking)
- ✅ Recently Viewed Notes (Premium feature)
- ✅ Draft & Scheduled Publishing (Draft status & auto-publish command)
- ✅ User Verification (Admin verification dengan badges)
- ✅ JavaScript Fixes (Marketplace show page JavaScript properly wrapped in script tags)
- ✅ Controller Fixes (Base Controller extends BaseController for middleware support)
- ✅ PWA Support (Progressive Web App dengan offline support dan install prompt)
- ✅ Dark Mode (Toggle dark/light mode dengan persistent preference)
- ✅ Performance Optimizations (Database indexes, Redis cache, query optimization, image lazy loading, CDN support)
- ✅ Image Processing Service (Generate multiple sizes: thumbnail, medium, large)
- ✅ Search Autocomplete (AJAX-based search suggestions untuk notes dan tags)
- ✅ Structured Data (JSON-LD untuk SEO: Product, BreadcrumbList, CollectionPage)

**In Progress:**
- ⚠️ FASE 8: Deployment & Launch

**Withdraw System:**
- ⚠️ Withdraw memerlukan **approval admin** minimal **24 jam** setelah request
- ⚠️ Tidak bisa langsung otomatis masuk rekening (harus menunggu approval admin)

**Featured Notes Advertising System:**
- ✅ Seller dapat request featured placement untuk note mereka
- ✅ Multiple locations: Landing Hero, Carousel, Marketplace Banner/Grid, Popup modals
- ✅ Pricing per location & duration (configurable di admin settings)
- ✅ Auto-approve untuk premium users (instant activation)
- ✅ Admin approval system dengan refund jika reject
- ✅ Analytics tracking: Impressions, Clicks, CTR, ROI
- ✅ Auto-expire command (daily at 01:00 WIB)
- ✅ Seller dashboard analytics dengan detailed metrics

**AI Memory Platform (Active - Workspace Feature):**
- ✅ **AI Memory Platform** — Aktif menggunakan API/model gratis (Ollama)
  - **Semua fitur AI hanya dapat diakses dalam workspace context**
  - Knowledge Base System: Build knowledge base dari semua catatan user di workspace
  - Support untuk jutaan datasheet/notes dengan caching optimal
  - Multi-workspace system dengan AI-powered features
  - Semantic search dengan embeddings integration
  - Natural Language Q&A menggunakan seluruh knowledge base sebagai context
  - Context linking antar catatan dengan embedding similarity
  - Activity timeline & history dari knowledge base
  - Auto insights & weekly summaries dengan AI-powered analysis
  - Training Data Preparation untuk future fine-tuning
  - Free AI API: Menggunakan Ollama (gratis, open source) yang dapat dipintarkan dengan data aplikasi
  - **AI Chat**: Chat dengan AI tentang semua notes di workspace
  - **Akses Control**: Admin bebas akses, Seller/Buyer wajib premium
  - **Workspace sebagai platform masa depan** untuk plugin-plugin keren

**Planned:**
- ⚠️ Mobile App

See [TASKLIST.md](TASKLIST.md) for full task list

---

## 👨‍💻 Developer Info

- **Author:** Wahyu Honorare
- **Website:** [https://noteds.com](https://noteds.com)
- **Project:** Noteds — Marketplace v1.0
- **License:** © 2025-2026 Noteds by Wahyu Dedik. All Rights Reserved.

---

## 📞 Support & Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev/)
- [Midtrans API](https://docs.midtrans.com/)

---

## 🎯 Next Steps

1. Review [LOCAL_SETUP.md](LOCAL_SETUP.md) untuk development environment
2. Check [TASKLIST.md](TASKLIST.md) untuk roadmap & priorities
3. Deploy ke production:
   - **Ubuntu + aaPanel:** [PRODUCTION_SETUP.md](PRODUCTION_SETUP.md) (recommended)
   - **Manual VPS Setup:** [VPS_SETUP.md](VPS_SETUP.md)
4. Optimize performance dengan [PERFORMANCE_SETUP.md](PERFORMANCE_SETUP.md) (Redis, CDN, Image Processing)
5. Review security dengan [SECURITY.md](SECURITY.md) (Security headers, rate limiting, file upload security)

---

## 🔧 Recent Updates (2025)

### Major Updates (Latest)
- ✅ **PWA Support**: Progressive Web App dengan offline support
  - Installable sebagai aplikasi mobile
  - Service worker untuk caching dan offline access
  - Install prompt banner otomatis
  - Manifest dengan shortcuts dan icons
- ✅ **Dark Mode**: Toggle dark/light mode dengan persistent preference
  - CSS variables untuk smooth transitions
  - LocalStorage untuk menyimpan preferensi
  - Auto-detect dan apply preferensi
  - Tailwind dark mode configuration
- ✅ **Performance Optimizations**: Comprehensive performance improvements
  - Database indexes untuk query optimization
  - Redis cache support (auto-detect, fallback ke database)
  - Query optimization dengan select specific columns
  - Eager loading optimization dengan limits
  - Caching untuk popular notes, featured content, tags
  - Image lazy loading untuk semua images
  - Responsive images dengan srcset support
  - CDN support untuk static assets
  - Image processing service untuk multiple sizes
  - Laravel Telescope query monitoring
- ✅ **Search & SEO Enhancements**:
  - Search autocomplete dengan AJAX suggestions
  - Structured data (JSON-LD) untuk SEO
  - Product, BreadcrumbList, CollectionPage schemas
- ✅ **Security Hardening**: Comprehensive security improvements
  - Security headers middleware (CSP, HSTS, X-Frame-Options, etc.)
  - Rate limiting untuk sensitive endpoints (purchase, wallet, withdraw, resale, escrow, quote)
  - Input sanitization middleware untuk semua request
  - Enhanced file upload security dengan MIME validation dan magic bytes
  - Security logging untuk monitoring suspicious activities
  - Complete security documentation (SECURITY.md)
- ✅ **AI Features Migration to Workspace**: Semua fitur AI dipindahkan ke workspace context
  - AI Chat, AI Memory, MyNoteds hanya dapat diakses dalam workspace
  - Workspace sebagai platform masa depan untuk plugin-plugin keren
  - Navigation update dengan Workspaces link untuk admin & premium users
  - Workspace sidebar dengan AI Features menu
- ✅ **Admin Full Access**: Admin memiliki akses penuh ke semua fitur
  - Admin dapat membeli dan menjual notes
  - Admin dapat menggunakan semua fitur seller dan buyer
  - Admin memiliki premium access otomatis
  - Admin dapat mengakses semua fitur AI tanpa premium
  - Middleware `ai.access` untuk kontrol akses AI features
- ✅ **AI Memory Platform Active**: Knowledge base system dengan support jutaan notes
  - Build knowledge base dari semua catatan user
  - AI akan semakin pintar seiring bertambahnya data
  - Training data preparation untuk future fine-tuning
- ✅ **Seeder Updates**: Admin dan premium users sekarang memiliki test workspace
  - Admin seeder membuat "Admin Test Workspace" untuk testing AI features
  - Premium users seeder membuat workspace untuk setiap premium user
  - Workspace member setup otomatis untuk testing

### Bug Fixes
- ✅ Fixed JavaScript code rendering issue in marketplace show page (all JS properly wrapped in script tags)
- ✅ Fixed NoteQuestionController middleware error (Base Controller now extends BaseController)
- ✅ Added GET route handler for questions endpoint to prevent Method Not Allowed errors

### Technical Improvements
- ✅ All JavaScript functions properly scoped and wrapped in script tags
- ✅ Improved error handling in frontend JavaScript (reactions, comments, Q&A)
- ✅ Better user feedback with SweetAlert2 for all interactive features
- ✅ Route optimization for better error handling
- ✅ Middleware `EnsureAiAccess` untuk kontrol akses AI features
- ✅ WorkspaceAiController untuk AI Chat di workspace

### Documentation
- ✅ Updated README.md with all new features
- ✅ Updated TASKLIST.md with complete implementation status
- ✅ All database tables documented
- ✅ All features properly categorized and documented
- ✅ Updated seeder untuk admin workspace testing

---

**Happy Coding! NJAYYY🚀**
