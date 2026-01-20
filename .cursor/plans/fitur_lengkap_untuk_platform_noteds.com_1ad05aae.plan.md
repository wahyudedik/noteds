---
name: Fitur Lengkap untuk Platform Noteds.com
overview: "Menambahkan fitur-fitur penting yang masih kurang untuk melengkapi platform: Global Search, Notification Center, User Settings, Follow System, Report Content, Admin User Management, dan Bookmarks. Fitur-fitur ini akan meningkatkan user experience dan functionality platform secara signifikan."
todos:
  - id: migration_user_settings
    content: Create migration create_user_settings_table untuk user preferences
    status: completed
  - id: migration_follows
    content: Create migration create_follows_table untuk follow system
    status: completed
  - id: migration_content_reports
    content: Create migration create_content_reports_table untuk report system
    status: completed
  - id: migration_bookmarks
    content: Create migration create_bookmarks_table untuk bookmarks
    status: completed
  - id: migration_user_ban
    content: Create migration add_ban_fields_to_users_table
    status: completed
  - id: model_user_setting
    content: Create UserSetting model
    status: completed
    dependencies:
      - migration_user_settings
  - id: model_follow
    content: Create Follow model dengan relationships
    status: completed
    dependencies:
      - migration_follows
  - id: model_content_report
    content: Create ContentReport model dengan polymorphic relationships
    status: completed
    dependencies:
      - migration_content_reports
  - id: model_bookmark
    content: Create Bookmark model
    status: completed
    dependencies:
      - migration_bookmarks
  - id: update_user_model
    content: Update User model dengan relationships (followers, following, bookmarks, settings)
    status: completed
    dependencies:
      - model_follow
      - model_bookmark
      - model_user_setting
  - id: update_post_model
    content: Update Post model dengan bookmarkedBy() relationship
    status: completed
    dependencies:
      - model_bookmark
  - id: service_search
    content: Create SearchService untuk global search logic
    status: completed
  - id: service_follow
    content: Create FollowService untuk follow logic
    status: completed
    dependencies:
      - model_follow
  - id: service_report
    content: Create ReportService untuk report logic
    status: completed
    dependencies:
      - model_content_report
  - id: controller_search
    content: Create SearchController untuk global search
    status: completed
    dependencies:
      - service_search
  - id: controller_notification
    content: Create NotificationController untuk notification management
    status: completed
  - id: controller_settings
    content: Create SettingsController untuk user settings
    status: completed
    dependencies:
      - model_user_setting
  - id: controller_follow
    content: Create FollowController untuk follow/unfollow actions
    status: completed
    dependencies:
      - service_follow
  - id: controller_report
    content: Create ReportController untuk report submission
    status: completed
    dependencies:
      - service_report
  - id: controller_bookmark
    content: Create BookmarkController untuk bookmark management
    status: completed
    dependencies:
      - model_bookmark
  - id: controller_admin_users
    content: Create Admin/UserManagementController untuk admin user management
    status: completed
    dependencies:
      - migration_user_ban
  - id: controller_admin_reports
    content: Create Admin/ReportController untuk admin report management
    status: completed
    dependencies:
      - model_content_report
  - id: extend_notification_service
    content: Extend NotificationService dengan methods untuk semua notification types
    status: completed
  - id: routes_all_features
    content: Add routes untuk semua fitur baru (search, notifications, settings, follow, report, bookmarks, admin)
    status: completed
    dependencies:
      - controller_search
      - controller_notification
      - controller_settings
      - controller_follow
      - controller_report
      - controller_bookmark
      - controller_admin_users
      - controller_admin_reports
  - id: component_search_bar
    content: Create Search/SearchBar.vue component untuk header
    status: completed
  - id: component_search_results
    content: Create Search/SearchResults.vue component
    status: completed
  - id: page_search_index
    content: Create Search/Index.vue page
    status: completed
    dependencies:
      - component_search_results
  - id: component_notification_list
    content: Create Notifications/NotificationList.vue component
    status: completed
  - id: component_notification_item
    content: Create Notifications/NotificationItem.vue component
    status: completed
  - id: page_notifications_index
    content: Create Notifications/Index.vue page
    status: completed
    dependencies:
      - component_notification_list
      - component_notification_item
  - id: extend_notification_bell
    content: Extend existing NotificationBell.vue untuk support semua notification types
    status: completed
  - id: page_settings_index
    content: Create Settings/Index.vue dengan tabs
    status: completed
  - id: page_settings_account
    content: Create Settings/Account.vue tab
    status: completed
  - id: page_settings_privacy
    content: Create Settings/Privacy.vue tab
    status: completed
  - id: page_settings_notifications
    content: Create Settings/Notifications.vue tab
    status: completed
  - id: page_settings_security
    content: Create Settings/Security.vue tab
    status: completed
  - id: component_follow_button
    content: Create Follow/FollowButton.vue component
    status: completed
  - id: page_profile_followers
    content: Create Profile/Followers.vue page
    status: completed
  - id: page_profile_following
    content: Create Profile/Following.vue page
    status: completed
  - id: component_report_button
    content: Create Report/ReportButton.vue component
    status: completed
  - id: component_report_modal
    content: Create Report/ReportModal.vue component
    status: completed
  - id: page_admin_reports_index
    content: Create Admin/Reports/Index.vue page
    status: completed
  - id: page_admin_reports_show
    content: Create Admin/Reports/Show.vue page
    status: completed
  - id: component_bookmark_button
    content: Create Bookmark/BookmarkButton.vue component
    status: completed
  - id: page_bookmarks_index
    content: Create Bookmarks/Index.vue page
    status: completed
  - id: page_admin_users_index
    content: Create Admin/Users/Index.vue page
    status: completed
  - id: page_admin_users_show
    content: Create Admin/Users/Show.vue page
    status: completed
  - id: page_admin_users_edit
    content: Create Admin/Users/Edit.vue page
    status: completed
  - id: integrate_components
    content: Integrate new components ke existing pages (PostCard, CommentThread, ProfileHeader, SidebarNav)
    status: completed
    dependencies:
      - component_follow_button
      - component_report_button
      - component_bookmark_button
  - id: update_navigation
    content: Update SidebarNav.vue dengan links baru (Bookmarks, Notifications, Settings, Search)
    status: completed
    dependencies:
      - routes_all_features
  - id: middleware_banned_user
    content: Create EnsureUserNotBanned middleware untuk prevent banned users dari actions
    status: completed
    dependencies:
      - migration_user_ban
  - id: update_feed_service
    content: Update FeedService dengan filter untuk posts from followed users (optional)
    status: completed
    dependencies:
      - service_follow
  - id: policies_authorization
    content: Create/Update Policies untuk authorization (FollowPolicy, BookmarkPolicy, ReportPolicy)
    status: completed
    dependencies:
      - model_follow
      - model_bookmark
      - model_content_report
---

# Fitur Lengk

ap untuk Platform Noteds.com

## Overview

Menambahkan fitur-fitur penting yang masih kurang untuk melengkapi platform Noteds.com. Fitur-fitur ini mencakup Global Search, Notification Center, User Settings, Follow System, Report Content, Admin User Management, dan Bookmarks untuk meningkatkan user experience dan functionality platform.

## Fitur yang Akan Ditambahkan

### 1. Global Search System

Sistem pencarian global untuk mencari posts, users, dan content dalam satu tempat.**Features:**

- Search bar di header/navigation
- Unified search results (posts, users, products)
- Advanced filters (type, date, category)
- Search suggestions/autocomplete
- Recent searches history
- Saved searches

**Implementation:**

- `app/Http/Controllers/SearchController.php` - Global search controller (NEW, berbeda dari Marketplace/SearchController dan ExplorerController)
- `app/Services/SearchService.php` - Search logic service
- `resources/js/Pages/Search/Index.vue` - Search results page
- `resources/js/Components/Search/SearchBar.vue` - Search bar component
- `resources/js/Components/Search/SearchResults.vue` - Search results component
- Route: `GET /search`

**Note:**

- Global Search berbeda dari existing search:
- `Marketplace/SearchController` - khusus untuk search products di marketplace
- `ExplorerController@search` - khusus untuk search articles di explorer
- `SearchController` (new) - unified search untuk posts, users, products, articles
- Global Search bisa memanfaatkan existing search controllers atau melakukan search langsung ke models

### 2. Notification Center

Halaman pusat untuk melihat dan mengelola semua notifications.**Features:**

- List semua notifications (unread/read)
- Mark as read/unread
- Mark all as read
- Filter by type (posts, comments, orders, withdrawals, dll)
- Real-time updates (polling atau websocket)
- Notification preferences
- Delete notifications

**Implementation:**

- `app/Http/Controllers/NotificationController.php` - Notification management
- `resources/js/Pages/Notifications/Index.vue` - Notifications page
- `resources/js/Components/Notifications/NotificationList.vue` - Notification list component
- `resources/js/Components/Notifications/NotificationItem.vue` - Individual notification item
- Routes: `GET /notifications`, `POST /notifications/{id}/read`, `POST /notifications/read-all`

**Extend NotificationService:**

- **Existing notifications** (sudah ada):
- `notifyNewOrder()` - untuk seller saat ada order baru (existing)
- `notifyWithdrawalRequest()` - untuk admin saat ada withdrawal request (existing)
- `notifyWithdrawalStatus()` - untuk user saat withdrawal status update (existing)
- **New notifications** (akan ditambahkan):
- `notifyNewComment()` - untuk post author saat ada comment baru
- `notifyNewVote()` - untuk post/comment author saat ada vote (optional)
- `notifyNewFollow()` - untuk user saat ada yang follow
- `notifyContentReported()` - untuk admin saat ada content report
- `notifyReportResolved()` - untuk reporter saat report di-resolve
- `notifyMention()` - untuk user saat di-mention di post/comment (future)

### 3. User Settings & Preferences

Halaman settings untuk mengelola preferences, privacy, security, dan email settings.**Features:**

- **Account Settings**: Email, password, profile info
- **Privacy Settings**: Who can see profile, posts visibility, search visibility
- **Notification Preferences**: Email notifications, in-app notifications
- **Security Settings**: Active sessions, login history, 2FA (future)
- **Email Preferences**: Email frequency, types of emails
- **Profile Visibility**: Public/Private profile
- **Data & Privacy**: Account deletion, data export (GDPR)

**Implementation:**

- `app/Http/Controllers/SettingsController.php` - Settings management
- `resources/js/Pages/Settings/Index.vue` - Settings page dengan tabs
- `resources/js/Pages/Settings/Account.vue` - Account settings tab
- `resources/js/Pages/Settings/Privacy.vue` - Privacy settings tab
- `resources/js/Pages/Settings/Notifications.vue` - Notification preferences tab
- `resources/js/Pages/Settings/Security.vue` - Security settings tab
- `app/Models/UserSetting.php` - User preferences model
- Migration: `create_user_settings_table`
- Routes: `GET /settings`, `POST /settings/{type}`

### 4. Follow/Following System

Sistem untuk follow user/business untuk membangun business connections.**Features:**

- Follow/Unfollow user
- Followers list
- Following list
- Mutual connections
- Follow suggestions
- Notification saat ada yang follow
- Feed filtering (posts from followed users)

**Implementation:**

- Migration: `create_follows_table` (follower_id, following_id, created_at)
- `app/Models/Follow.php` - Follow model
- `app/Http/Controllers/FollowController.php` - Follow/unfollow actions
- `app/Services/FollowService.php` - Follow logic service
- `resources/js/Components/Follow/FollowButton.vue` - Follow button component
- `resources/js/Pages/Profile/Followers.vue` - Followers list page
- `resources/js/Pages/Profile/Following.vue` - Following list page
- Update `User` model dengan relationships: `followers()`, `following()`
- Routes: `POST /users/{user}/follow`, `DELETE /users/{user}/unfollow`, `GET /users/{user}/followers`, `GET /users/{user}/following`

**Integration:**

- Update Home feed untuk filter posts dari followed users (optional)
- Add follow suggestions di sidebar
- Add follow count di profile

### 5. Report/Flag Content System

Sistem untuk user melaporkan content yang tidak pantas atau melanggar aturan.**Features:**

- Report post
- Report comment
- Report user
- Report reasons (spam, harassment, inappropriate, copyright, dll)
- Report details/notes
- Admin dashboard untuk manage reports
- Auto-moderation berdasarkan report count

**Implementation:**

- Migration: `create_content_reports_table` (user_id, reportable_type, reportable_id, reason, notes, status, admin_id, admin_notes, resolved_at)
- `app/Models/ContentReport.php` - Report model dengan polymorphic relationship
- `app/Http/Controllers/ReportController.php` - Report submission
- `app/Http/Controllers/Admin/ReportController.php` - Admin manage reports
- `app/Services/ReportService.php` - Report logic service
- `resources/js/Components/Report/ReportButton.vue` - Report button/modal component
- `resources/js/Pages/Admin/Reports/Index.vue` - Admin reports list
- `resources/js/Pages/Admin/Reports/Show.vue` - Admin report detail
- Routes: `POST /posts/{post}/report`, `POST /comments/{comment}/report`, `POST /users/{user}/report`

**Auto-moderation:**

- Auto-hide post jika report count > threshold (e.g., 5 reports)
- Notify admin untuk review
- Ban user jika terlalu banyak reports

### 6. Admin User Management

Dashboard admin untuk mengelola users (ban, edit roles, view details, dll).**Features:**

- List semua users dengan filter
- View user details
- Edit user role
- Ban/Unban user
- View user activity
- View user posts/comments
- View user reports received
- Send warning to user
- Delete user account (with data cleanup)
- User statistics

**Implementation:**

- `app/Http/Controllers/Admin/UserManagementController.php` - User management
- `resources/js/Pages/Admin/Users/Index.vue` - Users list page
- `resources/js/Pages/Admin/Users/Show.vue` - User detail page
- `resources/js/Pages/Admin/Users/Edit.vue` - Edit user page
- Update `User` model: add `is_banned`, `banned_at`, `ban_reason`
- Migration: `add_ban_fields_to_users_table`
- Routes: `GET /admin/users`, `GET /admin/users/{user}`, `PUT /admin/users/{user}`, `POST /admin/users/{user}/ban`, `POST /admin/users/{user}/unban`

### 7. Bookmarks/Saved Posts

Sistem untuk save/bookmark posts agar bisa dibaca nanti.**Features:**

- Bookmark post
- Unbookmark post
- View bookmarked posts
- Organize bookmarks dengan tags/collections (optional)
- Share bookmarks collection (optional)

**Implementation:**

- Migration: `create_bookmarks_table` (user_id, post_id, created_at)
- `app/Models/Bookmark.php` - Bookmark model
- `app/Http/Controllers/BookmarkController.php` - Bookmark management
- `resources/js/Components/Bookmark/BookmarkButton.vue` - Bookmark button component
- `resources/js/Pages/Bookmarks/Index.vue` - Bookmarked posts page
- Update `User` model dengan `bookmarks()` relationship
- Update `Post` model dengan `bookmarkedBy()` relationship
- Routes: `POST /posts/{post}/bookmark`, `DELETE /posts/{post}/unbookmark`, `GET /bookmarks`

**Integration:**

- Add bookmark button di PostCard component
- Add "Bookmarks" link di navigation/profile menu

## Database Migrations

### 1. `create_user_settings_table`

```php
Schema::create('user_settings', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id')->unique();
    $table->json('notification_preferences')->nullable();
    $table->json('privacy_settings')->nullable();
    $table->json('email_preferences')->nullable();
    $table->boolean('profile_visibility')->default(true); // public
    $table->boolean('search_visibility')->default(true);
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
```



### 2. `create_follows_table`

```php
Schema::create('follows', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('follower_id');
    $table->uuid('following_id');
    $table->timestamps();
    
    $table->unique(['follower_id', 'following_id']);
    $table->foreign('follower_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('following_id')->references('id')->on('users')->onDelete('cascade');
    $table->index(['follower_id', 'created_at']);
    $table->index(['following_id', 'created_at']);
});
```



### 3. `create_content_reports_table`

```php
Schema::create('content_reports', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id'); // reporter
    $table->uuidMorphs('reportable'); // post, comment, user
    $table->enum('reason', ['spam', 'harassment', 'inappropriate', 'copyright', 'fake', 'other']);
    $table->text('notes')->nullable();
    $table->enum('status', ['pending', 'reviewing', 'resolved', 'dismissed'])->default('pending');
    $table->uuid('admin_id')->nullable();
    $table->text('admin_notes')->nullable();
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
    $table->index(['reportable_type', 'reportable_id', 'status']);
    $table->index(['user_id', 'created_at']);
});
```



### 4. `create_bookmarks_table`

```php
Schema::create('bookmarks', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id');
    $table->uuid('post_id');
    $table->timestamps();
    
    $table->unique(['user_id', 'post_id']);
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
    $table->index(['user_id', 'created_at']);
});
```



### 5. `add_ban_fields_to_users_table`

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_banned')->default(false)->after('role');
    $table->timestamp('banned_at')->nullable();
    $table->text('ban_reason')->nullable();
    $table->uuid('banned_by')->nullable(); // admin who banned
    
    $table->foreign('banned_by')->references('id')->on('users')->onDelete('set null');
    $table->index('is_banned');
});
```



## Routes

### Global Search

```php
Route::middleware('auth')->group(function () {
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])
        ->middleware('throttle:30,1')
        ->name('search.index'); // 30 searches per minute
});
```

**Note:**

- Route `/search` adalah unified global search, berbeda dari:
- `/marketplace/search` - search products (existing)
- `/explorer/search` - search articles (existing)

### Notifications

```php
Route::middleware('auth')->group(function () {
    // Read-only, tidak perlu throttle
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])
        ->middleware('throttle:60,1')
        ->name('notifications.read'); // 60 actions per minute (frequent action)
    
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])
        ->middleware('throttle:10,1')
        ->name('notifications.read-all'); // 10 actions per minute
    
    Route::delete('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'destroy'])
        ->middleware('throttle:60,1')
        ->name('notifications.destroy'); // 60 deletions per minute
});
```



### Settings

```php
Route::middleware('auth')->group(function () {
    // Read-only, tidak perlu throttle
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    
    Route::post('/settings/account', [App\Http\Controllers\SettingsController::class, 'updateAccount'])
        ->middleware('throttle:10,60')
        ->name('settings.account'); // 10 updates per hour
    
    Route::post('/settings/privacy', [App\Http\Controllers\SettingsController::class, 'updatePrivacy'])
        ->middleware('throttle:10,60')
        ->name('settings.privacy'); // 10 updates per hour
    
    Route::post('/settings/notifications', [App\Http\Controllers\SettingsController::class, 'updateNotifications'])
        ->middleware('throttle:10,60')
        ->name('settings.notifications'); // 10 updates per hour
    
    Route::post('/settings/security', [App\Http\Controllers\SettingsController::class, 'updateSecurity'])
        ->middleware('throttle:10,60')
        ->name('settings.security'); // 10 updates per hour
});
```



### Follow System

```php
Route::middleware('auth')->group(function () {
    Route::post('/users/{user}/follow', [App\Http\Controllers\FollowController::class, 'follow'])
        ->middleware('throttle:20,5')
        ->name('users.follow'); // 20 actions per 5 minutes
    
    Route::delete('/users/{user}/unfollow', [App\Http\Controllers\FollowController::class, 'unfollow'])
        ->middleware('throttle:20,5')
        ->name('users.unfollow'); // 20 actions per 5 minutes
    
    // Read-only, tidak perlu throttle
    Route::get('/users/{user}/followers', [App\Http\Controllers\FollowController::class, 'followers'])->name('users.followers');
    Route::get('/users/{user}/following', [App\Http\Controllers\FollowController::class, 'following'])->name('users.following');
});
```



### Report Content

```php
Route::middleware('auth')->group(function () {
    Route::post('/posts/{post}/report', [App\Http\Controllers\ReportController::class, 'reportPost'])
        ->middleware('throttle:10,60')
        ->name('posts.report'); // 10 reports per hour (prevent abuse)
    
    Route::post('/comments/{comment}/report', [App\Http\Controllers\ReportController::class, 'reportComment'])
        ->middleware('throttle:10,60')
        ->name('comments.report'); // 10 reports per hour
    
    Route::post('/users/{user}/report', [App\Http\Controllers\ReportController::class, 'reportUser'])
        ->middleware('throttle:10,60')
        ->name('users.report'); // 10 reports per hour
});
```



### Bookmarks

```php
Route::middleware('auth')->group(function () {
    // Read-only, tidak perlu throttle
    Route::get('/bookmarks', [App\Http\Controllers\BookmarkController::class, 'index'])->name('bookmarks.index');
    
    Route::post('/posts/{post}/bookmark', [App\Http\Controllers\BookmarkController::class, 'store'])
        ->middleware('throttle:30,5')
        ->name('posts.bookmark'); // 30 bookmarks per 5 minutes
    
    Route::delete('/posts/{post}/unbookmark', [App\Http\Controllers\BookmarkController::class, 'destroy'])
        ->middleware('throttle:30,5')
        ->name('posts.unbookmark'); // 30 unbookmarks per 5 minutes
});
```



### Admin User Management

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', App\Http\Controllers\Admin\UserManagementController::class)->only(['index', 'show', 'edit', 'update']);
    Route::post('users/{user}/ban', [App\Http\Controllers\Admin\UserManagementController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [App\Http\Controllers\Admin\UserManagementController::class, 'unban'])->name('users.unban');
    Route::resource('reports', App\Http\Controllers\Admin\ReportController::class)->only(['index', 'show', 'update']);
    Route::post('reports/{report}/resolve', [App\Http\Controllers\Admin\ReportController::class, 'resolve'])->name('reports.resolve');
    Route::post('reports/{report}/dismiss', [App\Http\Controllers\Admin\ReportController::class, 'dismiss'])->name('reports.dismiss');
});
```



## Frontend Components

### Search Components

- `resources/js/Components/Search/SearchBar.vue` - Search bar di header
- `resources/js/Components/Search/SearchResults.vue` - Search results display
- `resources/js/Components/Search/SearchFilters.vue` - Advanced filters
- `resources/js/Pages/Search/Index.vue` - Search results page

### Notification Components

- `resources/js/Components/Notifications/NotificationBell.vue` - Notification bell di header (extend existing)
- `resources/js/Components/Notifications/NotificationList.vue` - Notification list
- `resources/js/Components/Notifications/NotificationItem.vue` - Individual notification
- `resources/js/Pages/Notifications/Index.vue` - Notifications page

### Settings Components

- `resources/js/Pages/Settings/Index.vue` - Settings page dengan tabs
- `resources/js/Pages/Settings/Account.vue` - Account settings
- `resources/js/Pages/Settings/Privacy.vue` - Privacy settings
- `resources/js/Pages/Settings/Notifications.vue` - Notification preferences
- `resources/js/Pages/Settings/Security.vue` - Security settings

### Follow Components

- `resources/js/Components/Follow/FollowButton.vue` - Follow/unfollow button
- `resources/js/Pages/Profile/Followers.vue` - Followers list
- `resources/js/Pages/Profile/Following.vue` - Following list

### Report Components

- `resources/js/Components/Report/ReportButton.vue` - Report button/modal
- `resources/js/Components/Report/ReportModal.vue` - Report form modal
- `resources/js/Pages/Admin/Reports/Index.vue` - Admin reports list
- `resources/js/Pages/Admin/Reports/Show.vue` - Admin report detail

### Bookmark Components

- `resources/js/Components/Bookmark/BookmarkButton.vue` - Bookmark button
- `resources/js/Pages/Bookmarks/Index.vue` - Bookmarked posts page

### Admin Components

- `resources/js/Pages/Admin/Users/Index.vue` - Users list
- `resources/js/Pages/Admin/Users/Show.vue` - User detail
- `resources/js/Pages/Admin/Users/Edit.vue` - Edit user

## Integration Points

### Update Existing Components

1. **PostCard.vue** - Add bookmark button, report button
2. **CommentThread.vue** - Add report button
3. **ProfileHeader.vue** - Add follow button, followers/following count
4. **SidebarNav.vue** - Add Bookmarks, Notifications, Settings links
5. **NotificationBell.vue** - Extend untuk support semua notification types

### Update Services

1. **NotificationService** - Add methods untuk semua notification types
2. **FeedService** - Add filter untuk posts from followed users
3. **SearchService** - Create service untuk global search

## Implementation Priority

### Phase 1 (Critical - Core Functionality)

1. Notification Center
2. User Settings (basic: privacy, notifications)
3. Report Content System

### Phase 2 (Important - User Engagement)

4. Follow/Following System
5. Bookmarks
6. Global Search

### Phase 3 (Administrative)

7. Admin User Management

## Notes

- Semua fitur menggunakan existing authentication dan authorization patterns
- Follow system menggunakan existing User model dan relationships
- Report system menggunakan polymorphic relationships untuk flexibility
- Settings menggunakan JSON column untuk flexibility
- Bookmarks bisa di-extend dengan collections/tags di future
- **Middleware**: Create `EnsureUserNotBanned` middleware untuk prevent banned users dari melakukan actions
- **Policies**: Create policies untuk authorization (FollowPolicy, BookmarkPolicy, ReportPolicy)
- **Throttling**: Tambahkan throttling untuk routes baru sesuai plan throttling (follow, bookmark, report, search)
- **Update FeedService**: Add filter untuk posts from followed users sebagai optional feature