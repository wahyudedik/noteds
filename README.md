# 💡 **Noteds** — Catatan & Ide Digital yang Menghasilkan Uang

> **Marketplace platform untuk catatan digital dengan sistem komisi dan monetisasi yang adil.**  
> **Tempat Menulis, Menjual, dan Menemukan Ide Digital yang Menghasilkan Uang.**

---

## 📑 Quick Navigation

- 🎯 [Platform Overview](#platform-overview)
- 🧱 [Tech Stack](#tech-stack)
- 📚 [Documentation](#documentation)
- 📋 [Features](#features)
- 💡 [Monetization Strategy](#monetization--content-strategy)
- 🚀 [Development & Deployment](#-development--deployment)
- 📊 [Project Status](#-project-status)

---

## 🌟 Platform Overview

**Noteds** adalah marketplace digital di mana pengguna dapat:
- ✅ Menulis dan menyimpan catatan pribadi dengan Rich Text Editor
- ✅ Menjual template atau ide digital dengan sistem komisi yang fair
- ✅ Membeli catatan/template dari content creator lain
- ✅ Menghasilkan uang dari setiap penjualan + view monetization

### 💰 Key Monetization Features

| Feature | Detail |
|---------|--------|
| **Platform Commission** | 20% dari transaksi (configurable) |
| **Creator Commission** | Original creator dapat komisi di setiap penjualan (default: 0%, configurable) |
| **Free Notes** | 0% commission (encourage sharing) |
| **Sale Modes** | Scarcity Mode (resellable) atau Standard Mode (fixed seller) |
| **View Monetization** | Free notes bisa earn dari views (0.01 IDR per view, requires approval) |
| **Featured Ads** | Promote notes dengan advertising (7/14/30 hari) |
| **Withdraw** | Admin approval minimal 24 jam (not automatic)

## 🧱 Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 12 (PHP 8.2+), MySQL, Spatie Permission, Laravel Breeze |
| **Frontend** | Blade, Tailwind CSS, Alpine.js, Vite 6.4.1 |
| **Editors** | Quill (Rich Text), Prism.js (Code Syntax) |
| **Payments** | Midtrans, Dynamic Tax System, Wallet Management |
| **Broadcasting** | Pusher/Ably + Laravel Echo (Real-time) |
| **Media** | Intervention Image (Processing), PDF.js, model-viewer (3D) |
| **Testing** | Pest, PHPUnit |
| **Performance** | Redis Cache, CDN Support, Image Optimization, Telescope Monitoring |
| **Database** | UUID primary keys for security |

**Optional/Future:** Meilisearch (Full-text search), PostgreSQL (High-scale)

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| **[FITUR.md](FITUR.md)** | 📋 Complete feature list dengan detail setiap fitur |
| **[LOCAL_SETUP.md](LOCAL_SETUP.md)** | 🛠️ Local development setup guide |
| **[PRODUCTION_SETUP.md](PRODUCTION_SETUP.md)** | 🚀 Production deployment (Ubuntu + aaPanel) |
| **[VPS_SETUP.md](VPS_SETUP.md)** | 🖥️ Manual VPS setup (untuk preferensi lain) |
| **[PERFORMANCE_SETUP.md](PERFORMANCE_SETUP.md)** | ⚡ Performance optimization (Redis, CDN, Telescope) |
| **[SECURITY.md](SECURITY.md)** | 🔒 Security hardening & best practices |
| **[TASKLIST.md](TASKLIST.md)** | 📅 Development roadmap & task tracking |

---

## ✨ Features (42+ Sub-Features) 
### Core Marketplace Features
- ✅ **Note Management** - Create, edit, draft, schedule publish, version tracking, templates, series
- ✅ **Content Protection** - 25+ anti-copy, anti-screenshot, anti-AI, anti-automation features
- ✅ **Rich Media** - Video (max 2min), Audio, Code Preview, 3D Models, PDF, Advanced Gallery
- ✅ **Two Sale Modes** - Scarcity Mode (resellable) + Standard Mode (fixed seller)
- ✅ **Note Bundles** - Create & sell bundled notes dengan discount
- ✅ **Gift Notes** - Send notes ke users lain dengan email notifications

### Engagement & Community
- ✅ **Rating & Reviews** - 5-star system dengan review replies
- ✅ **Comments System** - Threaded comments dengan nested replies
- ✅ **Reactions** - 5 types (Like, Love, Helpful, Insightful, Thanks)
- ✅ **Q&A System** - Ask questions, seller answers, mark helpful
- ✅ **Activity Feed** - Track user activities dengan polymorphic relationships
- ✅ **Follow System** - Follow creators & get updates

### Financial Features
- ✅ **Buyer Dashboard** - Purchase stats, downloads, completion rate, history
- ✅ **Seller Dashboard** - Revenue tracking, buyer history, best performers
- ✅ **Share Analytics** - Share referrals, commission tracking, leaderboard (NEW)
- ✅ **Affiliate System** - Affiliate links, commissions, leaderboard, promotional materials, quick navigation cards, conversion rate tracking, landing page builder (NEW: Enhanced dashboard with all features complete)
- ✅ **Referral System with Automation** - Referral links, commissions, automated sending from admin wallet, configurable schedules, transaction tracking (NEW: Complete automation system)
  - 🔄 Scheduled commission sending (daily/weekly/monthly)
  - 💾 Transaction history with filtering and export
  - 📧 Automated notifications (admin batch + user individual)
  - ⚙️ Admin settings panel (enable/disable, schedule, thresholds, batch size)
  - 🛡️ Restricted access (seller/buyer only, not admin)
- ✅ **Featured Ads** - Promote notes dengan analytics (impressions, clicks, CTR, ROI)
- ✅ **Wallet System** - Balance tracking, transaction history
- ✅ **Refund System** - Buyer request → Admin approval workflow
- ✅ **Withdraw Management** - Request + 24hr admin approval

### User & Community
- ✅ **Authentication** - Login/signup dengan Laravel Breeze
- ✅ **Public Profiles** - Avatar, bio, social links, badges, levels
- ✅ **KYC Verification** - KTP + selfie dengan admin approval (required for sellers)
- ✅ **Direct Messaging** - User-to-user messaging dengan conversation threads
- ✅ **Support Tickets** - Create & track support requests
- ✅ **Leaderboards** - Affiliate, share, top sellers ranking
- ✅ **Contests** - Create contests dengan voting system

### Creative Ecosystem (Envato-Style)
- ✅ **8 Content Categories** - Elements, Audio, Code, Graphic, Photo, Theme, Video, 3D
- ✅ **Ecosystem Filtering** - Marketplace filtering by content type
- ✅ **Studio Services** - Service marketplace dengan brief, quotes, escrow, milestones

### Analytics & Insights
- ✅ **Buyer Analytics** - Purchase trends, download stats, completion rate
- ✅ **Seller Analytics** - Revenue tracking, performance metrics
- ✅ **Share Analytics** - Share tracking, commission earnings, leaderboard (NEW)
- ✅ **Featured Notes Analytics** - Impressions, clicks, CTR, ROI tracking
- ✅ **Email Analytics** - Open rates, click rates, conversion, A/B testing
- ✅ **Admin Monitoring** - System health, queue status, scheduler, database monitoring
- ✅ **Workspace Insights** - Knowledge graph, productivity metrics

### Admin & Management
- ✅ **User Moderation** - User management, verification, suspension
- ✅ **Content Moderation** - Reports, reviews, comments moderation
- ✅ **Share Settings** - Configure commission %, payout day, limits, payment mode (NEW)
- ✅ **Settings** - Commission tiers, tax rules, pricing, currency
- ✅ **CMS** - Dynamic pages, FAQ, blog posts, documentation
- ✅ **Email Management** - Templates, campaigns, sequences, A/B testing

### Advanced Features
- ✅ **Workspace System** - Multi-workspace, folder organization, collaboration
- ✅ **Webhooks** - Event-based webhooks for integrations
- ✅ **PWA Support** - Installable app, offline support, service worker
- ✅ **Dark Mode** - Toggle dengan persistent preference
- ✅ **i18n & Multi-Currency** - 3 languages (EN, ID, AR), 2 currencies (USD, IDR)
- ✅ **Performance Optimization** - Redis cache, image lazy loading, CDN support
- ✅ **Marketing Tools** - Earnings calculator, referral ROI, price benchmarking

**For detailed features breakdown, see [FITUR.md](FITUR.md)**

---

## 💡 Monetization & Content Strategy

### 💵 Commission Structure

**Scarcity Mode (Default):**
- Platform Fee: 20% (dari setiap transaksi, configurable)
- Creator Commission: Selalu dapat komisi di setiap penjualan (default: 0%, configurable)
- Seller dapat: Full amount - platform fee - creator commission
- Buyer bisa: Resell dengan harga custom setelah membeli

**Standard Mode:**
- Platform Fee: 0%
- Creator Commission: 0%
- Seller dapat: 100% (minus tax)
- Buyer tidak bisa: Resell (ownership tetap with seller)

**Free Notes:**
- Commission: 0%
- View Monetization: 0.01 IDR per view (requires approval)
- Free sharing untuk knowledge base

### 💚 Content Protection (25+ Features)

**Protection Disabled by Default** - Enable via Admin Settings sesuai kebutuhan:
- 🔒 Anti-copy (text selection, right-click, keyboard shortcuts)
- 🔒 Anti-screenshot (Print Screen, Snipping Tool, mobile)
- 🔒 Anti-AI detection (ChatGPT, Claude, Perplexity, headless browsers)
- 🔒 Clipboard monitoring & DevTools detection
- 🔒 Mouse/click pattern analysis & screen recording detection
- 🔒 Blur overlay & watermarking support

**Smart Exclusion:** Protection automatically excludes form pages & editors untuk allow normal editing

---

## 🚀 Development & Deployment

### Quick Start Commands

```bash
# Setup environment
composer install && npm install
cp .env.example .env
php artisan key:generate

# Database & seeders
php artisan migrate
php artisan db:seed

# Development
php artisan serve
npm run build
php artisan queue:work       # For background jobs
php artisan schedule:run     # For scheduled tasks
```

**Important Setup Notes:**
- Broadcaster (Pusher/Ably) required for real-time notifications
- Queue worker required for email notifications
- Cron job needed for scheduler: `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`

### Testing

```bash
./vendor/bin/pest              # Run all tests
php artisan test --filter=SaleMode  # Run specific tests
composer pint                  # Code style fixing
```

### Available Seeders

**System Core:** RoleSeeder, BadgeSeeder, LevelSeeder, AdminSeeder, SettingSeeder, ExchangeRateSeeder, CommissionTierSeeder, TaxRuleSeeder

**Content:** NoteSeeder, CategorySeeder, NoteTemplateSeeder, NoteSeriesSeeder, FeaturedNoteSeeder, NoteBundleSeeder

**Commerce:** TransactionSeeder, PurchasedNoteSeeder, RefundSeeder, GiftNoteSeeder, WithdrawSeeder

**Community:** NoteReviewSeeder, NoteCommentSeeder, NoteReactionSeeder, NoteQuestionSeeder, ActivitySeeder, MessageSeeder

**Documentation:** Seed with `php artisan db:seed --class=NoteSeeder` untuk include semua content types

### Deployment

| Environment | Guide |
|-------------|-------|
| **Local Dev** | [LOCAL_SETUP.md](LOCAL_SETUP.md) |
| **Production (Recommended)** | [PRODUCTION_SETUP.md](PRODUCTION_SETUP.md) - Ubuntu + aaPanel |
| **Manual VPS** | [VPS_SETUP.md](VPS_SETUP.md) |
| **Performance Tuning** | [PERFORMANCE_SETUP.md](PERFORMANCE_SETUP.md) |
| **Security Hardening** | [SECURITY.md](SECURITY.md) |

---

## 🔒 Security & System Health

### Security Features

✅ **Authentication & Access**
- UUID primary keys (prevent enumeration)
- Spatie Permission (role-based access control)
- Laravel Breeze authentication with secure sessions
- HTTPS mandatory in production

✅ **Data Protection**
- CSRF protection on all forms
- SQL Injection prevention (Eloquent ORM)
- XSS protection (Blade escaping, CSP headers)
- File upload security (extension whitelist, MIME validation, magic bytes)

✅ **Advanced Security**
- Security headers (CSP, HSTS, X-Frame-Options, Referrer-Policy)
- Rate limiting on sensitive endpoints (purchase, wallet, withdraw, etc.)
- Input sanitization middleware
- Private disk storage for KYC documents
- Session security with HTTP-only flags

### System Health Monitoring

**Admin Dashboard** (`/admin/system-health`) menyediakan:
- ✅ Database, Queue, Cache, Scheduler health
- ✅ Real-time monitoring dengan critical alerts
- ✅ Failed job detection
- ✅ Broadcaster configuration verification
- ✅ Color-coded status indicators (Healthy/Warning/Error)

See [SECURITY.md](SECURITY.md) untuk security hardening guide lengkap.

---

## 📊 Project Status

### ✅ Completed Features

**Core Platform:**
- ✅ Notes Module (CRUD, Rich Text, Tagging, Scheduling)
- ✅ Marketplace with search & filters
- ✅ Wallet & Transaction System
- ✅ Dynamic Tax Rules (country/category based)
- ✅ Sale Modes (Scarcity & Standard)
- ✅ Refund System with workflow
- ✅ Withdraw Management with 24hr approval

**Content Protection:**
- ✅ 25+ Anti-copy, Anti-screenshot, Anti-AI features
- ✅ Admin-configurable protection settings
- ✅ File upload security
- ✅ KYC Verification (KTP + selfie)

**Rich Media & Features:**
- ✅ Video preview (max 2 min) with auto-thumbnails
- ✅ Audio player, Code preview, 3D models, PDF viewer
- ✅ Advanced image gallery with lightbox
- ✅ Note Bundles, Gift Notes
- ✅ Comments, Reactions, Q&A System

**Community & Engagement:**
- ✅ Rating & Reviews
- ✅ Follow System, Direct Messaging
- ✅ Leaderboards (affiliate, share, top sellers)
- ✅ Support Tickets
- ✅ Activity Feed

**Monetization:**
- ✅ Featured Notes Advertising with analytics
- ✅ Affiliate System with commissions
- ✅ Share Analytics with commission tracking & monthly payouts (NEW)
- ✅ Referral Program with Automated Commission Sending (NEW: Scheduled automation, admin settings, transaction tracking)
- ✅ Buyer & Seller Analytics
- ✅ Points & Gamification (Badges, Levels)

**Advanced Features:**
- ✅ Workspace System (multi-workspace, collaboration)
- ✅ 8-Category Ecosystem (Elements, AudioJungle, Code, etc.)
- ✅ Studio Order Flow (brief, quote, escrow, milestones)
- ✅ Share Analytics Feature (referrals, commissions, payouts) (NEW)
- ✅ PWA Support (installable, offline)
- ✅ Dark Mode with persistent preference
- ✅ Internationalization (3 languages, 2 currencies)
- ✅ Webhooks for integrations
- ✅ Real-time notifications (Pusher/Ably + Echo)

**Performance & Security:**
- ✅ Redis caching, image lazy loading, CDN support
- ✅ Database optimization, query monitoring (Telescope)
- ✅ Security headers, rate limiting, input sanitization
- ✅ System health monitoring with alerts

### 🚧 Current Phase

**Phase:** Marketplace v1.0 ✅ Active  
**Status:** All core features implemented and tested  
**Next:** Production optimization & bug fixes

### 📅 Roadmap

| Phase | Focus | Status |
|-------|-------|--------|
| **Marketplace v1.0** | Notes, Marketplace, Wallet, Monetization | ✅ Complete |
| **Team Workspace v1.1** | Multi-user collaboration, plugins | 🚧 Planned |
| **API & Integrations v1.2** | REST API, webhook events, third-party | 🚧 Planned |
| **Plugin Marketplace v1.3** | Allow community to build modules | 🚧 Planned |
| **Mobile App v2.0** | Native apps (iOS/Android) | 🚧 Future |

See [TASKLIST.md](TASKLIST.md) for full roadmap & detailed task tracking

---

## 👨‍💻 About

- **Project:** Noteds — Digital Marketplace v1.0
- **Author:** Wahyu Dedik
- **Website:** [https://noteds.com](https://noteds.com)
- **License:** © 2025-2026 Noteds. All Rights Reserved.
- **Status:** ✅ Production Ready

---

## 🎯 Getting Started

### For Developers

1. **Setup:** Follow [LOCAL_SETUP.md](LOCAL_SETUP.md)
2. **Database:** Run `php artisan migrate:fresh --seed`
3. **Development:** Start with `php artisan serve` + `npm run dev`
4. **Testing:** Run `./vendor/bin/pest`

### For DevOps/Deployment

1. **Production (Ubuntu + aaPanel):** [PRODUCTION_SETUP.md](PRODUCTION_SETUP.md)
2. **Manual VPS:** [VPS_SETUP.md](VPS_SETUP.md)
3. **Performance:** [PERFORMANCE_SETUP.md](PERFORMANCE_SETUP.md)
4. **Security:** [SECURITY.md](SECURITY.md)

### For Product Managers

1. **Features Overview:** [FITUR.md](FITUR.md)
2. **Monetization Guide:** See [Monetization & Content Strategy](#monetization--content-strategy)
3. **Roadmap:** [TASKLIST.md](TASKLIST.md)

---

## 📞 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/)
- [Alpine.js](https://alpinejs.dev/)
- [Midtrans API](https://docs.midtrans.com/)
- [Pusher Channels](https://pusher.com/channels)

---

## 🎉 Recent Updates (Dec 9, 2025)

- ✅ **Affiliate Dashboard Enhancements** - Complete implementation with:
  - Quick navigation section with leaderboard, settings, conversion rate, pending commissions
  - Enhanced statistics cards with real-time metrics
  - Copy link function with clipboard API fallback (works on all browsers)
  - Fixed landing page button with correct language keys
  - All language files (EN, ID, AR) synchronized with correct translation keys
  - Fully functional promotional materials manager
  - Commission breakdown by tier and status
  - Recent conversions and commissions tracking
  - Payout request form with available balance validation
  - Recent payouts history table
  - All 4 modals: Create Link, Edit Link, Landing Page Builder, Promotional Materials Manager

- ✅ **Previous Updates (Dec 8, 2025)**
  - Share Analytics Feature - Complete implementation
  - All 8 bugs fixed and verified (0 errors in build)
  - Comprehensive FITUR.md documentation
  - README.md restructured and cleaned up
  - Production ready with all monetization features
  - Security hardening complete
  - Performance optimization complete

**Status: Ready for Production Launch! 🚀**
