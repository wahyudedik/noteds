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
- ⚠️ **Setiap user hanya bisa beli note 1x**, tapi **note bisa dijual ke user berbeda** (ownership transfer)
- ⚠️ **Original creator selalu dapat komisi** di setiap penjualan (jika di-setting)
- ⚠️ **Withdraw:** Approval admin minimal 24 jam (tidak otomatis)
- ⚠️ Paket Premium: Rp25.000/bln (FASE 7 - opsional)
- ✅ Iklan catatan unggulan (Featured Notes) — Platform untuk mempromosikan note dengan bayar per iklan
  - Lokasi iklan: Landing Hero, Landing Carousel, Marketplace Banner, Marketplace Grid, Popup Welcome, Popup Exit Intent, Popup Interstitial
  - Durasi: 7, 14, atau 30 hari
  - Auto-approve untuk premium users
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

**Tools:**
- Midtrans (Payment Gateway)
- Ollama (AI Local LLM)
- Laravel Telescope (Debugging)
- Pest (Testing)

**Database:** All tables use **UUID** primary keys for security

---

## 📚 Documentation

### Quick Start
- **[LOCAL_SETUP.md](LOCAL_SETUP.md)** — Complete local development setup guide
- **[VPS_SETUP.md](VPS_SETUP.md)** — VPS deployment & production setup
- **[TASKLIST.md](TASKLIST.md)** — Development phases & task tracking

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
- ✅ Premium subscriptions
- ✅ AI-powered summaries & tags (Ollama - Basic)
- ✅ AI Chat untuk Seller Profile (Public feature - semua user bisa akses)
- ✅ Referral system
- ✅ Notification system (SweetAlert2)
- ✅ Support ticket system
- ✅ Admin panel with comprehensive analytics (Telescope)
- ✅ CMS (FAQ, dynamic pages)
- ✅ Marketing simulators
- ✅ Multi-Tier Content Protection (Preview, secure file uploads, download control)
- ✅ Internationalization (i18n): 3 languages (EN, ID, AR), 2 currencies (USD, IDR)
- ✅ Featured Notes Advertising System
  - Seller can request featured placement for their notes
  - Multiple locations: Landing Hero, Carousel, Marketplace Banner/Grid, Popup modals
  - Pricing per location & duration (configurable in admin settings)
  - Auto-approve for premium users
  - Analytics tracking (impressions, clicks, CTR, ROI)
  - Admin approval system with refund if rejected
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

**AI Memory Platform Plugin (Premium Subscription Feature - Planned):**
- 🚧 Multi-workspace system (personal, team, organization)
- 🚧 Semantic search dengan embeddings
- 🚧 Natural Language Q&A ("Apa yang kubicarakan dengan Rina minggu lalu?")
- 🚧 Context linking antar catatan
- 🚧 Activity timeline & history tracking
- 🚧 Auto insights & weekly summaries
- 🚧 Enhanced folder & tag system
- 🚧 Separate authentication/role untuk AI Platform (optional)

---

## 🎮 Marketing Tools (Coming Soon)

**Top 3 Priorities:**
1. **Earnings Calculator** — Simulate seller earnings potential
2. **Referral ROI Calculator** — Track referral program ROI
3. **Premium vs Basic Comparison** — Interactive plan comparison

**Additional Simulators:**
- Wallet Simulator
- AI Summary Demo
- Transaction Flow Visualizer
- Price Benchmark Tool

---

## 💡 Monetization & Content Strategy

### 💚 Free vs Paid Notes Strategy

**Default: Free Sharing**
- Price field default: `Rp 0` (free)
- Platform supports **knowledge sharing** tanpa mengharuskan monetisasi
- Free notes tetap bisa di-rate & di-review untuk visibility

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

#### 🔄 Alternative Monetization Models

1. **Freemium + Premium Bundle**
2. **Pay-Per-View (PPV)**
3. **Subscription Access**
4. **Hybrid Model** (Recommended)

---

## 🧠 Noteds Business AI Vision

> **AI Memory Platform** pertama dari Indonesia yang mampu memahami konteks dari setiap catatan — baik teks, dokumen, maupun aktivitas — untuk membantu pengguna mengambil keputusan lebih cepat dan cerdas.

**Current Phase:** ✅ Marketplace v1.0 (Active Development)
**Next Phase:** 🚧 AI Memory Platform Plugin (Premium Subscription Feature - 2026 Q1)

### 🎯 Visi

Menjadi **AI Memory Platform** pertama dari Indonesia yang mampu memahami konteks dari setiap catatan — baik teks, dokumen, maupun aktivitas — untuk membantu pengguna mengambil keputusan lebih cepat dan cerdas.

### 💡 Konsep Singkat

Noteds adalah aplikasi pencatat cerdas (AI-based note system) yang:

- Mengelola catatan teks, gambar, dan dokumen
- Menghubungkan konteks antar catatan (meeting, transaksi, ide, pelanggan, dll)
- Menghadirkan **AI assistant** untuk menjawab pertanyaan berbasis data milik pengguna
- Menjadi fondasi "memori digital" pribadi maupun organisasi

### ⚙️ AI Layer (Context Engine)

- **Natural Language Understanding:** Model LLM lokal via Ollama (tanpa ketergantungan API eksternal)
- **Embedding & Semantic Search:** 
  - `sentence-transformers` atau `OpenAI embeddings` (opsional, bisa pakai model lokal `all-MiniLM-L6-v2`)
- **Insight Engine:** Modul Laravel khusus untuk:
  - Summarization (ringkasan otomatis) ✅
  - Q&A berbasis catatan
  - Context linking antar catatan
  - Keyword tagging otomatis ✅

### 🚀 Fitur Utama AI Memory Platform (MVP)

1. 📝 Catatan teks, gambar, & dokumen (upload + auto-tagging) ✅ (Basic sudah ada di marketplace)
2. 🔍 Pencarian pintar (AI-based semantic search)
3. 💬 Tanya catatanmu: "Apa yang kubicarakan dengan Rina minggu lalu?"
4. 🧠 Insight otomatis (ringkasan mingguan, deteksi topik)
5. 📂 Folder & tag system (enhanced)
6. 👥 Multi workspace (personal, tim, lembaga)
7. 🔒 Autentikasi dan role khusus untuk AI Memory Platform (bisa terpisah dari marketplace)
8. 🕓 Aktivitas & histori (timeline perubahan catatan)

### 📅 Roadmap

| Phase | Focus | Timeline | Status |
|-------|-------|----------|--------|
| **Phase 0: Marketplace v1.0** | Catatan + Marketplace + Wallet | 2025 Q4 | ✅ In Progress |
| **Phase 1: AI Memory MVP** | Catatan + AI Q&A sederhana (Premium Plugin) | 2026 Q1 | 🚧 Planned |
| **Phase 2: Team Workspace** | Multi user + kolaborasi real-time | 2026 Q2 | 🚧 Planned |
| **Phase 3: Integrasi & API** | Integrasi dengan tools eksternal | 2026 Q3 | 🚧 Planned |
| **Phase 4: AI Insight Center** | Laporan & rekomendasi berbasis AI | 2026 Q4 | 🚧 Planned |
| **Phase 5: Marketplace Plugin** | Pengembang eksternal bisa menambah modul | 2027 | 🚧 Planned |

### Tech Stack Business (Future)

- **Database:** PostgreSQL
- **Search:** Meilisearch
- **Frontend:** Vue 3 + Pinia
- **AI:** Ollama + Embeddings
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
```

See [LOCAL_SETUP.md](LOCAL_SETUP.md) for detailed instructions

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
- ✅ **Spatie Permission** untuk role-based access
- ✅ **HTTPS** mandatory di production
- ✅ **Rate limiting** on sensitive endpoints
- ✅ **CSRF** protection on all forms
- ✅ **SQL injection** prevention via Eloquent
- ✅ **XSS** protection via Blade escaping

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
- ✅ AI Integration (Ollama)
- ✅ Referral System
- ✅ Notification System (SweetAlert2)
- ✅ Admin Manual Subscription Creation
- ✅ Admin Dashboard Analytics (Wallet, Referral, Notes, Revenue, Top Performers)
- ✅ CMS (FAQ & Dynamic Pages)
- ✅ Marketing Simulators
- ✅ Rich Text Editor (Quill) for Notes
- ✅ Tag deletion bug fix
- ✅ Multi-Tier Content Protection (Preview, File Upload, Download Control, Trust Indicators)
- ✅ Featured Notes Advertising System (Landing page, Marketplace, Popup modals)
- ✅ Seller Analytics Dashboard (Impressions, Clicks, CTR, ROI)
- ✅ Auto-approve Featured Notes untuk Premium Users
- ✅ Note History & Versioning System (Buyer history, Update history, Prevent delete)
- ✅ AI Chat untuk Seller Profile (Public feature - semua user bisa akses)
- ✅ Collections Enhancement (Add Purchased Notes)
- ✅ Resell Flow & One-Time Sale System (Original creator commission, Access control)
- ✅ Profile Features (Avatar upload, Share functionality, Open Graph tags)
- ✅ Buyer Analytics Dashboard (Purchase stats, Downloads, Completion rate)
- ✅ Dynamic Tax & Pricing Controls (Tax rules, pricing guidance panel, tax notifications)
- ✅ Tiered Commission System dengan admin reporting dashboard
- ✅ Subscription auto-renew flow dengan sufficient/insufficient balance handling & notifications
- ✅ Sale Mode System (Scarcity & Standard modes dengan repurchase, resale, analytics)
- ✅ Comprehensive Documentation System (22 documentation entries via DocumentationSeeder)

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

**Planned (Premium Subscription Plugin):**
- 🚧 AI Memory Platform Plugin
  - Multi-workspace system
  - Semantic search dengan embeddings
  - Natural Language Q&A
  - Context linking antar catatan
  - Activity timeline & history
  - Auto insights & weekly summaries

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
- [Ollama](https://ollama.ai/)

---

## 🎯 Next Steps

1. Review [LOCAL_SETUP.md](LOCAL_SETUP.md) untuk development environment
2. Check [TASKLIST.md](TASKLIST.md) untuk roadmap & priorities
3. Deploy ke VPS dengan [VPS_SETUP.md](VPS_SETUP.md)

---

**Happy Coding! NJAYYY🚀**
