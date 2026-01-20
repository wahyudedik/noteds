---
name: Repost Feature Implementation
overview: Mengimplementasikan fitur repost dimana setiap post bisa di-repost oleh pembuat atau pengguna lain. Repost akan muncul sebagai post terpisah di feed (seperti retweet Twitter) dengan metrics terpisah, namun juga menampilkan repost count pada post asli.
todos:
  - id: "1"
    content: "Create database migrations: create_reposts_table dan add_reposts_count_to_posts_table"
    status: completed
  - id: "2"
    content: Create Repost model dengan relationships ke User dan Post, serta auto-increment/decrement reposts_count
    status: completed
    dependencies:
      - "1"
  - id: "3"
    content: "Update Post model: tambah relationships (reposts, repostedBy, originalPost) dan helper methods"
    status: completed
    dependencies:
      - "1"
  - id: "4"
    content: "Update User model: tambah reposts relationship"
    status: completed
    dependencies:
      - "1"
  - id: "5"
    content: Create RepostController dengan store() dan destroy() methods, termasuk validasi dan rate limiting
    status: completed
    dependencies:
      - "2"
      - "3"
  - id: "6"
    content: Add routes untuk POST /posts/{post}/repost dan DELETE /posts/{post}/repost dengan middleware throttle
    status: completed
    dependencies:
      - "5"
  - id: "7"
    content: Update PostController::index() dan show() untuk include repost data (userReposts array, isReposted boolean)
    status: completed
    dependencies:
      - "3"
  - id: "8"
    content: Create PostRepostedNotification dan update NotificationService dengan notifyPostReposted method
    status: completed
    dependencies:
      - "3"
  - id: "9"
    content: Create RepostButton Vue component dengan toggle functionality, loading states, dan error handling
    status: completed
    dependencies:
      - "6"
  - id: "10"
    content: "Update PostCard component: tambah RepostButton, display repost count, dan handle repost display (original post info)"
    status: completed
    dependencies:
      - "9"
  - id: "11"
    content: "Update Posts/Show.vue page: tambah RepostButton dan display repost information"
    status: completed
    dependencies:
      - "9"
  - id: "12"
    content: Update feed queries untuk include reposts sebagai separate posts dengan original post relationship loaded
    status: completed
    dependencies:
      - "3"
      - "7"
---

# Implementasi Fitur Rep

ost

## Overview

Fitur repost memungkinkan pengguna untuk membagikan ulang post milik mereka sendiri atau pengguna lain. Repost akan muncul sebagai post terpisah di feed dengan informasi post asli, sambil tetap menampilkan repost count pada post original.

## Arsitektur Data

### Database Schema

**1. Migration: `create_reposts_table`**

- `id` (UUID, primary key)
- `user_id` (UUID, foreign key ke users) - user yang melakukan repost
- `post_id` (UUID, foreign key ke posts) - post yang di-repost
- `created_at`, `updated_at`
- Unique constraint: `user_id` + `post_id` (mencegah repost ganda)
- Index pada `post_id` dan `user_id` untuk performa query

**2. Migration: `add_reposts_count_to_posts_table`**

- Tambahkan kolom `reposts_count` (unsigned big integer, default 0)
- Index untuk sorting berdasarkan repost count

### Model Relationships

**Post Model** (`app/Models/Post.php`):

- `reposts(): HasMany` - semua repost dari post ini
- `repostedBy(): BelongsToMany` - users yang telah repost post ini
- `isRepostedBy(string $userId): bool` - check jika user sudah repost
- `originalPost(): BelongsTo` - jika ini adalah repost, link ke post asli
- Tambahkan `original_post_id` ke fillable jika diperlukan

**User Model** (`app/Models/User.php`):

- `reposts(): HasMany` - semua repost yang dibuat user ini

**Repost Model** (`app/Models/Repost.php`):

- Relationship ke `User` dan `Post`
- Auto-increment `reposts_count` pada post saat create
- Auto-decrement saat delete

## Backend Implementation

### 1. RepostController (`app/Http/Controllers/RepostController.php`)

**Methods:**

- `store(Request $request, Post $post): RedirectResponse`
- Validasi: user belum repost post ini sebelumnya
- Create repost record
- Increment `reposts_count` pada post original
- Create notification untuk post author (jika bukan repost sendiri)
- Redirect back dengan success message
- `destroy(Request $request, Post $post): RedirectResponse`
- Find dan delete repost record
- Decrement `reposts_count` pada post original
- Redirect back

**Validations:**

- User tidak bisa repost post yang sudah di-repost sebelumnya
- User bisa repost post mereka sendiri
- Rate limiting: 30 reposts per 5 menit

### 2. Routes (`routes/web.php`)

Tambahkan dalam `Route::middleware('auth')->group()`:

```php
Route::post('/posts/{post}/repost', [App\Http\Controllers\RepostController::class, 'store'])
    ->middleware('throttle:30,5')
    ->name('posts.repost');
Route::delete('/posts/{post}/repost', [App\Http\Controllers\RepostController::class, 'destroy'])
    ->middleware('throttle:30,5')
    ->name('posts.unrepost');
```



### 3. Update PostController

**Method `index()`:**

- Load repost data untuk setiap post
- Include `userReposts` array (mirip dengan `userVotes` dan `userBookmarks`)
- Pass ke Inertia props

**Method `show()`:**

- Check jika user sudah repost post ini
- Include `isReposted` boolean
- Load original post jika ini adalah repost

### 4. Feed Query Updates

**PostController::index()** dan query lainnya:

- Include reposts dalam feed (posts yang di-repost juga muncul)
- Atau filter: hanya show original posts (reposts muncul sebagai indikator)
- **Decision needed**: Apakah reposts muncul sebagai post terpisah di feed atau hanya sebagai indikator?

**Untuk implementasi "separate post":**

- Query posts termasuk reposts
- Load `originalPost` relationship untuk reposts
- Display repost dengan badge/indicator menunjukkan post asli

### 5. Notification

**Create Notification:** `app/Notifications/PostRepostedNotification.php`

- Notify post author ketika post mereka di-repost
- Exclude notification jika user repost post mereka sendiri
- Include link ke post dan profile user yang repost

**Update NotificationService:**

- Method `notifyPostReposted(Post $post, User $reposter)`

## Frontend Implementation

### 1. RepostButton Component (`resources/js/Components/RepostButton.vue`)

**Props:**

- `postId` (String, required)
- `repostsCount` (Number, default 0)
- `isReposted` (Boolean, default false)
- `canRepost` (Boolean, default true)

**Features:**

- Toggle repost/unrepost dengan icon
- Show repost count
- Loading state saat proses
- Error handling
- Success feedback

**Styling:**

- Icon: repost/retweet icon (mirip Twitter)
- Active state: filled icon dengan warna accent
- Hover effects

### 2. Update PostCard Component (`resources/js/Components/PostCard.vue`)

**Changes:**

- Import dan tambahkan `RepostButton` component
- Tambahkan prop `isReposted` dan `repostsCount`
- Place button di actions section (bersama VoteButton, BookmarkButton, Comments)
- Display repost count

**Repost Display:**

- Jika post adalah repost, tampilkan badge/header menunjukkan:
- "Reposted by [User Name]"
- Link ke original post
- Original post author info

### 3. Update PostFeed Component

**Changes:**

- Pass `userReposts` data ke PostCard
- Handle repost count display

### 4. Update Post Show Page (`resources/js/Pages/Posts/Show.vue`)

**Changes:**

- Add RepostButton
- Show repost count
- Display original post info jika ini adalah repost

## Data Flow

```mermaid
flowchart TD
    A[User clicks Repost] --> B{Already Reposted?}
    B -->|Yes| C[Show Unrepost Option]
    B -->|No| D[POST /posts/{id}/repost]
    C --> E[DELETE /posts/{id}/repost]
    D --> F[Create Repost Record]
    E --> G[Delete Repost Record]
    F --> H[Increment reposts_count]
    G --> I[Decrement reposts_count]
    H --> J[Send Notification to Original Author]
    I --> K[Update UI]
    J --> K
    K --> L[Refresh Feed/Post Display]
```



## Testing Considerations

1. **Unit Tests:**

- RepostController::store() - create repost
- RepostController::destroy() - delete repost
- Prevent duplicate reposts
- Repost count increment/decrement
- Notification creation

2. **Feature Tests:**

- User can repost post
- User can unrepost
- Repost count updates correctly
- Notification sent to original author
- Reposts appear in feed

3. **Edge Cases:**

- User repost post mereka sendiri
- Repost post yang sudah dihapus
- Multiple reposts dari user berbeda
- Repost count consistency

## Files to Create/Modify

### New Files:

1. `database/migrations/XXXX_XX_XX_XXXXXX_create_reposts_table.php`
2. `database/migrations/XXXX_XX_XX_XXXXXX_add_reposts_count_to_posts_table.php`
3. `app/Models/Repost.php`
4. `app/Http/Controllers/RepostController.php`
5. `app/Notifications/PostRepostedNotification.php`
6. `resources/js/Components/RepostButton.vue`

### Modified Files:

1. `app/Models/Post.php` - tambah relationships dan methods
2. `app/Models/User.php` - tambah reposts relationship
3. `app/Http/Controllers/PostController.php` - include repost data
4. `routes/web.php` - tambah repost routes
5. `resources/js/Components/PostCard.vue` - tambah RepostButton
6. `resources/js/Pages/Posts/Show.vue` - tambah repost functionality
7. `app/Services/NotificationService.php` - tambah notifyPostReposted method

## Implementation Order

1. Database migrations (reposts table, reposts_count column)
2. Repost model dengan relationships
3. RepostController dengan store/destroy methods
4. Routes
5. Update Post model relationships
6. Update PostController untuk include repost data
7. Notification system
8. Frontend RepostButton component
9. Update PostCard untuk display repost
10. Testing dan refinement

## UI/UX Considerations

- Repost button harus mudah diakses tapi tidak terlalu prominent
- Visual indicator jelas untuk post yang di-repost