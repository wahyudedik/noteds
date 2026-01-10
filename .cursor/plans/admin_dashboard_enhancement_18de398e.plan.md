---
name: Admin Dashboard Enhancement
overview: Meningkatkan dashboard admin dengan menambahkan semua fitur yang diperlukan untuk administrasi lengkap platform, termasuk post moderation, reports management, user management, withdrawal management yang terpisah per type, clipper system monitoring, dan analytics yang lebih komprehensif.
todos:
  - id: enhance-dashboard-controller
    content: Update AdminDashboardController dengan statistik lengkap (reports, posts, users, withdrawals by type, clipper stats, analytics)
    status: completed
  - id: create-post-controller
    content: Create AdminPostController dengan fitur moderation (index, show, moderate, restore, bulkModerate)
    status: completed
  - id: create-post-pages
    content: Create Post moderation pages (Index.vue dan Show.vue) dengan filter, search, dan moderation actions
    status: completed
    dependencies:
      - create-post-controller
  - id: enhance-dashboard-component
    content: Update Dashboard.vue dengan semua widgets baru (statistics, reports, users, withdrawals, clipper, analytics)
    status: completed
    dependencies:
      - enhance-dashboard-controller
  - id: create-clipper-widget
    content: Create ClipperSystemWidget component untuk fraud alerts dan pending items
    status: completed
  - id: create-analytics-service
    content: Create AdminAnalyticsService untuk trends dan metrics calculation
    status: completed
  - id: create-analytics-charts
    content: Create AnalyticsCharts component untuk visualisasi trends (user growth, sales, etc.)
    status: completed
    dependencies:
      - create-analytics-service
  - id: create-recent-activities
    content: Create RecentActivitiesWidget untuk menampilkan timeline admin actions
    status: completed
  - id: create-quick-actions
    content: Create QuickActionsPanel component untuk quick access common admin tasks
    status: completed
  - id: create-pending-summary
    content: Create PendingItemsSummary component untuk summary semua pending items
    status: completed
  - id: enhance-withdrawal-pages
    content: Update Withdrawals Index page dengan filter tabs untuk clipper/creator/marketplace
    status: completed
  - id: add-routes
    content: Add routes untuk post moderation di routes/web.php
    status: completed
    dependencies:
      - create-post-controller
---

#Admin Dashboard Enhancement Plan

## Overview

Dashboard admin saat ini hanya menampilkan statistik dasar dan recent withdrawals. Perlu ditambahkan fitur-fitur penting untuk administrasi lengkap platform, termasuk post moderation, reports management, user management, withdrawal management terpisah per type, clipper system monitoring, dan analytics yang lebih komprehensif.

## Current State Analysis

### Yang Sudah Ada

- Basic statistics (pending withdrawals, total users, total sales, total products)
- Recent withdrawals list
- Quick links ke FAQ dan Documentation management
- Routes untuk semua fitur admin sudah ada tapi tidak ditampilkan di dashboard

### Yang Kurang

1. **Post Moderation Interface** - Tidak ada admin interface untuk moderate posts
2. **Reports List** - Ada controller tapi tidak ditampilkan di dashboard
3. **Users List** - Ada controller tapi tidak ditampilkan di dashboard
4. **Withdrawal Separation** - Tidak dipisah antara clipper, creator, dan marketplace withdrawals
5. **Clipper System Monitoring** - Fraud alerts, pending clips, pending campaigns
6. **Post List dengan Status Filter** - Tidak ada interface untuk melihat semua posts dengan filter status
7. **Analytics & Statistics** - Statistik lebih detail (growth, trends, etc.)
8. **Recent Activities** - Activity log untuk monitoring admin actions
9. **Quick Actions** - Quick access untuk common admin tasks
10. **Pending Items Summary** - Summary semua pending items yang perlu review

## Implementation Plan

### Phase 1: Enhanced Statistics & Overview

#### 1.1 Update AdminDashboardController

**File**: `app/Http/Controllers/Admin/AdminDashboardController.php`Tambah statistik yang lebih lengkap:

- Pending reports count (by type: post, comment, user)
- Pending posts untuk moderation (status: moderated, archived)
- Pending brand approvals
- Pending clips untuk approval
- Pending campaigns
- Fraud alerts count (clipper system)
- Withdrawal breakdown (clipper, creator, marketplace)
- User growth (new users today, this week, this month)
- Sales statistics (today, this week, this month)
- Active campaigns count
- Total clips count
- Total campaigns count

#### 1.2 Enhanced Dashboard Statistics Component

**File**: `resources/js/Pages/Admin/Dashboard.vue`Update dashboard dengan:

- Grid statistics cards yang lebih lengkap
- Charts untuk trends (user growth, sales, etc.)
- Color coding untuk different metrics
- Clickable cards yang link ke detail pages

### Phase 2: Post Moderation Interface

#### 2.1 Create AdminPostController

**File**: `app/Http/Controllers/Admin/AdminPostController.php` (NEW)Fitur:

- `index()` - List semua posts dengan filter (status, purpose_type, search)
- `show()` - Detail post dengan moderation actions
- `moderate()` - Moderate post (warn, hide, delete)
- `restore()` - Restore moderated/archived post
- `bulkModerate()` - Bulk moderation actions

#### 2.2 Create Post Moderation Pages

**Files**:

- `resources/js/Pages/Admin/Posts/Index.vue` (NEW)
- `resources/js/Pages/Admin/Posts/Show.vue` (NEW)

Features:

- List posts dengan filter dan search
- Status badges (active, moderated, archived)
- Quick actions (moderate, restore, delete)
- Post detail dengan moderation history
- Moderation form dengan reason input

#### 2.3 Add Routes

**File**: `routes/web.php`

```php
Route::resource('posts', App\Http\Controllers\Admin\AdminPostController::class)->only(['index', 'show']);
Route::post('posts/{post}/moderate', [App\Http\Controllers\Admin\AdminPostController::class, 'moderate'])->name('posts.moderate');
Route::post('posts/{post}/restore', [App\Http\Controllers\Admin\AdminPostController::class, 'restore'])->name('posts.restore');
```



### Phase 3: Reports Management Dashboard Integration

#### 3.1 Update AdminDashboardController

Tambah recent reports dan pending reports count ke dashboard data.

#### 3.2 Update Dashboard Component

**File**: `resources/js/Pages/Admin/Dashboard.vue`Tambah section:

- Recent Reports widget (last 5-10 reports)
- Pending Reports count dengan breakdown by type
- Quick link ke Reports page

### Phase 4: User Management Dashboard Integration

#### 4.1 Update AdminDashboardController

Tambah:

- Recent users (new registrations)
- Banned users count
- Users by role breakdown
- Active users count

#### 4.2 Update Dashboard Component

Tambah section:

- Recent Users widget
- User statistics cards
- Quick link ke Users page

### Phase 5: Withdrawal Management Enhancement

#### 5.1 Update AdminDashboardController

Pisahkan withdrawals:

- Pending clipper withdrawals
- Pending creator withdrawals  
- Pending marketplace withdrawals
- Total pending amount per type

#### 5.2 Update Dashboard Component

Tambah:

- Withdrawal cards terpisah per type (Clipper, Creator, Marketplace)
- Recent withdrawals dengan type indicator
- Quick links ke withdrawal pages dengan filter

#### 5.3 Update Withdrawal Index Page

**File**: `resources/js/Pages/Admin/Withdrawals/Index.vue` (if exists)Tambah filter tabs untuk:

- All Withdrawals
- Clipper Withdrawals
- Creator Withdrawals
- Marketplace Withdrawals

### Phase 6: Clipper System Monitoring

#### 6.1 Update AdminDashboardController

Tambah clipper system stats:

- Pending clips count
- Fraud alerts count (from AdminClipController::getFraudAlerts)
- Pending campaigns count
- Pending brand approvals count
- Active campaigns count
- Total clips dengan fraud detection status

#### 6.2 Create Clipper System Widget

**File**: `resources/js/Components/Admin/ClipperSystemWidget.vue` (NEW)Widget untuk menampilkan:

- Fraud alerts dengan severity indicator
- Pending clips untuk review
- Pending campaigns
- Quick actions untuk clipper management

#### 6.3 Update Dashboard Component

Tambah Clipper System section dengan:

- Fraud alerts list
- Pending items summary
- Quick links ke Clips, Campaigns, Brand Approvals

### Phase 7: Analytics & Trends

#### 7.1 Create AdminAnalyticsService

**File**: `app/Services/AdminAnalyticsService.php` (NEW)Service untuk:

- User growth trends (daily, weekly, monthly)
- Sales trends
- Post creation trends
- Engagement metrics
- Clipper system metrics

#### 7.2 Update AdminDashboardController

Integrate AdminAnalyticsService untuk provide trend data.

#### 7.3 Create Analytics Charts Component

**File**: `resources/js/Components/Admin/AnalyticsCharts.vue` (NEW)Charts untuk:

- User growth line chart
- Sales bar chart
- Post creation trends
- Engagement metrics

### Phase 8: Recent Activities & Activity Log

#### 8.1 Update AdminDashboardController

Tambah recent activities dari AuditLog:

- Recent admin actions
- Recent moderation actions
- Recent approvals/rejections

#### 8.2 Create Recent Activities Widget

**File**: `resources/js/Components/Admin/RecentActivitiesWidget.vue` (NEW)Widget untuk menampilkan:

- Timeline of recent admin actions
- Action type badges
- Links ke related items

### Phase 9: Quick Actions Panel

#### 9.1 Create Quick Actions Component

**File**: `resources/js/Components/Admin/QuickActionsPanel.vue` (NEW)Quick actions untuk:

- Approve pending withdrawal
- Moderate reported post
- Review fraud alert
- Approve brand registration
- Ban user
- etc.

### Phase 10: Pending Items Summary

#### 10.1 Create Pending Items Summary Component

**File**: `resources/js/Components/Admin/PendingItemsSummary.vue` (NEW)Summary semua pending items:

- Pending withdrawals (by type)
- Pending reports
- Pending posts untuk moderation
- Pending clips
- Pending campaigns
- Pending brand approvals
- Fraud alerts

Dengan priority indicators dan quick action buttons.

## File Structure

### New Files to Create

**Backend:**

- `app/Http/Controllers/Admin/AdminPostController.php`
- `app/Services/AdminAnalyticsService.php`

**Frontend:**

- `resources/js/Pages/Admin/Posts/Index.vue`
- `resources/js/Pages/Admin/Posts/Show.vue`
- `resources/js/Components/Admin/ClipperSystemWidget.vue`
- `resources/js/Components/Admin/AnalyticsCharts.vue`
- `resources/js/Components/Admin/RecentActivitiesWidget.vue`
- `resources/js/Components/Admin/QuickActionsPanel.vue`
- `resources/js/Components/Admin/PendingItemsSummary.vue`

### Files to Modify

**Backend:**

- `app/Http/Controllers/Admin/AdminDashboardController.php`
- `routes/web.php`

**Frontend:**

- `resources/js/Pages/Admin/Dashboard.vue`
- `resources/js/Pages/Admin/Withdrawals/Index.vue` (if exists)

## Database Considerations

Tidak perlu migration baru, semua data sudah ada di:

- `posts` table (status field)
- `content_reports` table
- `users` table
- `withdrawals` table (user_type field)
- `clips` table
- `campaigns` table
- `audit_logs` table (jika ada)

## Success Criteria

- [ ] Dashboard menampilkan semua statistik penting
- [ ] Post moderation interface lengkap dan functional
- [ ] Reports terintegrasi di dashboard
- [ ] User management terintegrasi di dashboard
- [ ] Withdrawals terpisah per type (clipper, creator, marketplace)
- [ ] Clipper system monitoring lengkap dengan fraud alerts
- [ ] Analytics charts menampilkan trends
- [ ] Recent activities widget functional
- [ ] Quick actions panel accessible
- [ ] Pending items summary comprehensive
- [ ] Semua quick links functional
- [ ] Responsive design untuk mobile

## Implementation Priority

1. **High Priority**: Post moderation, Reports integration, Withdrawal separation, Clipper monitoring
2. **Medium Priority**: Analytics charts, Recent activities, Quick actions
3. **Low Priority**: Advanced analytics, Detailed trends

## Technical Notes

- Gunakan existing components dan patterns yang sudah ada
- Follow Laravel best practices untuk controllers