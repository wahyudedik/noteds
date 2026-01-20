---
name: Implementasi Throttling untuk Semua Endpoint
overview: Menambahkan throttling/rate limiting pada semua endpoint penting aplikasi untuk mencegah abuse dan spam. Menggunakan Laravel throttle middleware dengan limit ketat yang sesuai untuk setiap jenis endpoint.
todos:
  - id: throttle-auth
    content: Tambahkan throttle middleware ke authentication routes (register, password update)
    status: completed
  - id: throttle-content
    content: Tambahkan throttle middleware ke content creation routes (posts, comments, votes, validations)
    status: completed
  - id: throttle-marketplace
    content: Tambahkan throttle middleware ke marketplace routes (products, orders, withdrawals)
    status: completed
  - id: throttle-profile
    content: Tambahkan throttle middleware ke profile routes (update, delete)
    status: completed
  - id: throttle-contact
    content: Tambahkan throttle middleware ke contact form route
    status: completed
  - id: throttle-admin
    content: Tambahkan throttle middleware ke admin operation routes (withdrawal approvals, product moderation, FAQ/Documentation CRUD)
    status: completed
  - id: throttle-downloads
    content: Tambahkan throttle middleware ke file download routes (marketplace downloads)
    status: completed
  - id: throttle-explorer
    content: Tambahkan throttle middleware ke explorer search route
    status: completed
  - id: throttle-clipper
    content: Tambahkan throttle middleware ke clipper system routes (top-ups, campaigns, clips, view tracking, withdrawals) - akan ditambahkan saat fitur Clipper diimplementasikan
    status: completed
  - id: verify-existing
    content: Verifikasi throttle yang sudah ada (login, password reset, email verification) sudah benar
    status: completed
  - id: test-throttling
    content: Test throttling untuk memastikan semua limit bekerja dengan benar
    status: completed
    dependencies:
      - throttle-auth
      - throttle-content
      - throttle-marketplace
      - throttle-profile
      - throttle-contact
      - throttle-admin
      - throttle-downloads
      - throttle-explorer
---

# Implementasi Throttling untuk Semua Endpoint

## Overview

Menambahkan rate limiting/throttling pada semua endpoint penting untuk mencegah abuse, spam, dan serangan brute force. Menggunakan Laravel's built-in `throttle` middleware dengan limit yang ketat sesuai karakteristik masing-masing endpoint.

## Endpoint yang Akan Diberi Throttling

### Authentication & Registration

- **Registration**: 3 requests per menit (mencegah spam registrasi)
- **Login**: Sudah ada (via LoginRequest dengan 5 attempts)
- **Password Reset**: Sudah ada (6,1)
- **Email Verification**: Sudah ada (6,1)
- **Password Update**: 5 requests per jam (mencegah abuse)

### Content Creation (Posts & Comments)

- **Post Creation**: 5 posts per 10 menit (mencegah spam posting)
- **Comment Creation**: 10 comments per 5 menit (lebih longgar karena natural conversation)
- **Post Update**: 10 updates per jam (mencegah abuse)

### Voting & Engagement

- **Post Vote**: 30 votes per 5 menit (mencegah vote manipulation)
- **Comment Vote**: 30 votes per 5 menit
- **Idea Validation**: 5 validations per 30 menit (karena proses lebih kompleks)

### Marketplace

- **Product Creation**: 3 products per jam (mencegah spam produk)
- **Product Update**: 10 updates per jam
- **Order Creation**: 10 orders per menit (normal flow bisa multiple items)
- **Order Cancellation**: 5 cancellations per jam (mencegah abuse)

### Financial Operations

- **Withdrawal Request**: 3 requests per 24 jam (sangat ketat karena financial)
- **Withdrawal Update (Admin)**: Tidak perlu throttle (admin operation)

### Profile & User Operations

- **Profile Update**: 10 updates per jam
- **Profile Delete**: 3 requests per 24 jam (sangat ketat, irreversible)

### Contact & Public Forms

- **Contact Form**: 3 submissions per jam (mencegah spam email)

### Admin Operations

- **Admin Dashboard Access**: Tidak perlu throttle (internal)
- **Product Moderation**: 20 actions per menit (admin bisa bulk)
- **Withdrawal Approval/Reject**: 20 actions per menit
- **FAQ CRUD Operations**: 20 actions per menit (admin operations)
- **Documentation CRUD Operations**: 20 actions per menit (admin operations)

### Clipper System (Future/Planned)

- **Top Up Request**: 5 requests per jam (mencegah abuse top up)
- **Campaign Creation**: 3 campaigns per jam (mencegah spam campaigns)
- **Campaign Update**: 10 updates per jam
- **Clip Submission**: 10 clips per 5 menit (mencegah spam clips)
- **View Tracking API**: 10 requests per jam per user (critical, prevent abuse)
- **Withdrawal Request (Clipper)**: 3 requests per 24 jam (sangat ketat karena financial)

### File Downloads & Read Operations

- **Marketplace Download**: 20 downloads per 5 menit (mencegah abuse download)
- **Explorer Search**: 30 searches per menit (read-only, lebih longgar)
- **Explorer Index**: Tidak perlu throttle (read-only, cached)

## Implementation

### 1. Update Routes - Authentication (`routes/auth.php`)

```php
Route::middleware('guest')->group(function () {
    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:3,1'); // 3 requests per minute

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1'); // Already has, verify

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1'); // Add if missing
});

Route::middleware('auth')->group(function () {
    Route::put('password', [PasswordController::class, 'update'])
        ->middleware('throttle:5,60')
        ->name('password.update'); // 5 requests per hour
});
```



### 2. Update Routes - Posts & Comments (`routes/web.php`)

```php
Route::middleware(['auth'])->group(function () {
    Route::post('/posts', [App\Http\Controllers\PostController::class, 'store'])
        ->middleware('throttle:5,10')
        ->name('posts.store'); // 5 posts per 10 minutes
    
    Route::post('/posts/{post}/comments', [App\Http\Controllers\CommentController::class, 'store'])
        ->middleware('throttle:10,5')
        ->name('comments.store'); // 10 comments per 5 minutes
    
    Route::post('/posts/{post}/vote', [App\Http\Controllers\VoteController::class, 'votePost'])
        ->middleware('throttle:30,5')
        ->name('votes.post'); // 30 votes per 5 minutes
    
    Route::post('/comments/{comment}/vote', [App\Http\Controllers\VoteController::class, 'voteComment'])
        ->middleware('throttle:30,5')
        ->name('votes.comment'); // 30 votes per 5 minutes
    
    Route::post('/posts/{post}/validate', [App\Http\Controllers\IdeaValidationController::class, 'store'])
        ->middleware('throttle:5,30')
        ->name('idea-validations.store'); // 5 validations per 30 minutes
    
    Route::post('/comments/{comment}/best-answer', [App\Http\Controllers\CommentController::class, 'markBestAnswer'])
        ->middleware('throttle:10,5')
        ->name('comments.best-answer'); // 10 per 5 minutes
});
```



### 3. Update Routes - Marketplace (`routes/web.php`)

```php
Route::middleware('auth')->group(function () {
    // Products
    Route::post('marketplace/products', [App\Http\Controllers\Marketplace\ProductController::class, 'store'])
        ->middleware('throttle:3,60')
        ->name('marketplace.products.store'); // 3 products per hour
    
    Route::put('marketplace/products/{product}', [App\Http\Controllers\Marketplace\ProductController::class, 'update'])
        ->middleware('throttle:10,60')
        ->name('marketplace.products.update'); // 10 updates per hour

    // Orders
    Route::post('marketplace/orders', [App\Http\Controllers\Marketplace\OrderController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('marketplace.orders.store'); // 10 orders per minute
    
    Route::post('/marketplace/orders/{order}/cancel', [App\Http\Controllers\Marketplace\OrderController::class, 'cancel'])
        ->middleware('throttle:5,60')
        ->name('marketplace.orders.cancel'); // 5 cancellations per hour

    // Withdrawals
    Route::post('marketplace/withdrawals', [App\Http\Controllers\Marketplace\WithdrawalController::class, 'store'])
        ->middleware('throttle:3,1440')
        ->name('marketplace.withdrawals.store'); // 3 requests per 24 hours (1440 minutes)
});
```



### 4. Update Routes - Profile (`routes/web.php`)

```php
Route::middleware('auth')->group(function () {
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:10,60')
        ->name('profile.update'); // 10 updates per hour
    
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:3,1440')
        ->name('profile.destroy'); // 3 requests per 24 hours
});
```



### 5. Update Routes - Contact Form (`routes/web.php`)

```php
Route::post('/contact', [App\Http\Controllers\Legal\ContactController::class, 'submit'])
    ->middleware('throttle:3,60')
    ->name('contact.submit'); // 3 submissions per hour
```



### 6. Update Routes - Admin Operations (`routes/web.php`)

```php
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/withdrawals/{withdrawal}/approve', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'approve'])
        ->middleware('throttle:20,1')
        ->name('withdrawals.approve'); // 20 actions per minute
    
    Route::post('/withdrawals/{withdrawal}/reject', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'reject'])
        ->middleware('throttle:20,1')
        ->name('withdrawals.reject');
    
    Route::post('/withdrawals/{withdrawal}/complete', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'complete'])
        ->middleware('throttle:20,1')
        ->name('withdrawals.complete');
    
    // Product moderation already handled by resource controller
    Route::put('admin/products/{product}', [App\Http\Controllers\Admin\ProductModerationController::class, 'update'])
        ->middleware('throttle:20,1');
    
    Route::delete('admin/products/{product}', [App\Http\Controllers\Admin\ProductModerationController::class, 'destroy'])
        ->middleware('throttle:20,1');
    
    // FAQ & Documentation CRUD (if exists)
    Route::post('admin/faqs', [App\Http\Controllers\Admin\FaqController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('admin.faqs.store');
    
    Route::put('admin/faqs/{faq}', [App\Http\Controllers\Admin\FaqController::class, 'update'])
        ->middleware('throttle:20,1')
        ->name('admin.faqs.update');
    
    Route::delete('admin/faqs/{faq}', [App\Http\Controllers\Admin\FaqController::class, 'destroy'])
        ->middleware('throttle:20,1')
        ->name('admin.faqs.destroy');
    
    Route::post('admin/documentations', [App\Http\Controllers\Admin\DocumentationController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('admin.documentations.store');
    
    Route::put('admin/documentations/{documentation}', [App\Http\Controllers\Admin\DocumentationController::class, 'update'])
        ->middleware('throttle:20,1')
        ->name('admin.documentations.update');
    
    Route::delete('admin/documentations/{documentation}', [App\Http\Controllers\Admin\DocumentationController::class, 'destroy'])
        ->middleware('throttle:20,1')
        ->name('admin.documentations.destroy');
});
```



### 7. Update Routes - Marketplace Downloads (`routes/web.php`)

```php
Route::middleware('auth')->group(function () {
    Route::get('/marketplace/products/{product}/download', [App\Http\Controllers\Marketplace\DownloadController::class, 'download'])
        ->middleware('throttle:20,5')
        ->name('marketplace.products.download'); // 20 downloads per 5 minutes
});
```



### 8. Update Routes - Explorer (`routes/web.php`)

```php
Route::middleware('auth')->group(function () {
    Route::get('/explorer/search', [App\Http\Controllers\ExplorerController::class, 'search'])
        ->middleware('throttle:30,1')
        ->name('explorer.search'); // 30 searches per minute
    
    // Explorer index tidak perlu throttle karena read-only dan cached
    Route::get('/explorer', [App\Http\Controllers\ExplorerController::class, 'index'])
        ->name('explorer.index');
});
```



### 9. Update Routes - Clipper System (Future/Planned) (`routes/web.php`)

```php
// Routes ini akan ditambahkan saat fitur Clipper diimplementasikan
Route::middleware('auth')->prefix('clipper')->name('clipper.')->group(function () {
    // Top Up
    Route::post('top-ups', [App\Http\Controllers\Clipper\TopUpController::class, 'store'])
        ->middleware('throttle:5,60')
        ->name('clipper.top-ups.store'); // 5 requests per hour
    
    // Campaigns
    Route::post('campaigns', [App\Http\Controllers\Clipper\CampaignController::class, 'store'])
        ->middleware('throttle:3,60')
        ->name('clipper.campaigns.store'); // 3 campaigns per hour
    
    Route::put('campaigns/{campaign}', [App\Http\Controllers\Clipper\CampaignController::class, 'update'])
        ->middleware('throttle:10,60')
        ->name('clipper.campaigns.update'); // 10 updates per hour
    
    // Clips
    Route::post('clips', [App\Http\Controllers\Clipper\ClipController::class, 'store'])
        ->middleware('throttle:10,5')
        ->name('clipper.clips.store'); // 10 clips per 5 minutes
    
    Route::post('clips/{clip}/track-views', [App\Http\Controllers\Clipper\ClipController::class, 'trackViews'])
        ->middleware('throttle:10,60')
        ->name('clipper.clips.track-views'); // 10 requests per hour (critical, prevent abuse)
    
    // Withdrawals (Clipper)
    Route::post('withdrawals', [App\Http\Controllers\Clipper\ClipperWalletController::class, 'withdraw'])
        ->middleware('throttle:3,1440')
        ->name('clipper.withdrawals.store'); // 3 requests per 24 hours
});
```



## Optional: Custom Rate Limiters (Advanced)

Jika diperlukan kontrol lebih granular, bisa membuat custom rate limiters di `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    // Custom rate limiter untuk post creation
    RateLimiter::for('post-creation', function (Request $request) {
        return Limit::perMinutes(10, 5)->by($request->user()?->id ?: $request->ip());
    });
    
    // Custom rate limiter untuk marketplace operations
    RateLimiter::for('marketplace-creation', function (Request $request) {
        return Limit::perHour(3)->by($request->user()?->id ?: $request->ip());
    });
}
```

Kemudian gunakan di routes:

```php
Route::post('/posts', [PostController::class, 'store'])
    ->middleware('throttle:post-creation');
```



## Throttle Limit Reference

| Endpoint | Limit | Window | Reason ||----------|-------|--------|--------|| Registration | 3 | 1 minute | Prevent spam accounts || Password Update | 5 | 60 minutes | Prevent abuse || Post Creation | 5 | 10 minutes | Prevent spam posts || Comment Creation | 10 | 5 minutes | Allow natural conversation || Post Vote | 30 | 5 minutes | Prevent manipulation, allow engagement || Comment Vote | 30 | 5 minutes | Same as post vote || Idea Validation | 5 | 30 minutes | Complex operation, less frequent || Product Creation | 3 | 60 minutes | Prevent spam products || Product Update | 10 | 60 minutes | Allow edits || Order Creation | 10 | 1 minute | Normal checkout flow || Order Cancel | 5 | 60 minutes | Prevent abuse || Withdrawal Request | 3 | 1440 minutes (24h) | Financial operation, very strict || Profile Update | 10 | 60 minutes | Allow edits || Profile Delete | 3 | 1440 minutes (24h) | Irreversible, very strict || Contact Form | 3 | 60 minutes | Prevent spam emails || Admin Actions | 20 | 1 minute | Bulk operations allowed || FAQ/Documentation CRUD (Admin) | 20 | 1 minute | Admin operations, bulk allowed || Marketplace Download | 20 | 5 minutes | Prevent abuse download || Explorer Search | 30 | 1 minute | Read-only, allow frequent searches || Global Search | 30 | 1 minute | Unified search, allow frequent searches || Follow/Unfollow | 20 | 5 minutes | Prevent abuse, allow normal usage || Bookmark/Unbookmark | 30 | 5 minutes | Allow frequent bookmarking || Report Content | 10 | 60 minutes | Prevent abuse reporting || Settings Update | 10 | 60 minutes | Prevent abuse || Notification Actions | 60 | 1 minute | Allow frequent mark as read/delete || Notification Read All | 10 | 1 minute | Limit bulk actions || Top Up (Clipper) | 5 | 60 minutes | Prevent abuse top up || Campaign Creation (Clipper) | 3 | 60 minutes | Prevent spam campaigns || Campaign Update (Clipper) | 10 | 60 minutes | Allow edits || Clip Submission (Clipper) | 10 | 5 minutes | Prevent spam clips || View Tracking API (Clipper) | 10 | 60 minutes | Critical, prevent abuse || Clipper Withdrawal | 3 | 1440 minutes (24h) | Financial operation, very strict |

## Testing

Setelah implementasi, test dengan:

1. Mencoba melebihi limit untuk setiap endpoint
2. Memastikan error message yang jelas muncul
3. Memastikan limit reset setelah window time

## Endpoint yang Tidak Perlu Throttle

- **GET routes (read-only)**: Sebagian besar GET routes tidak perlu throttle karena read-only
- Explorer index (read-only, cached)
- Product listing (read-only)
- Post listing (read-only)
- Profile viewing (read-only)
- FAQ/Documentation pages (read-only)
- **Webhook endpoints**: Payment webhooks biasanya sudah memiliki signature verification
- **Admin dashboard access**: Internal use, tidak perlu throttle

## Notes