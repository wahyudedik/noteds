<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Home/Feed (redirect authenticated users to feed)
Route::get('/home', [App\Http\Controllers\PostController::class, 'index'])
    ->middleware(['auth', 'verified', 'throttle:60,1'])
    ->name('home'); // 60 requests per minute (allows for infinite scroll)

// Global Search
Route::middleware('auth')->group(function () {
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])
        ->middleware('throttle:30,1')
        ->name('search.index'); // 30 searches per minute
    Route::get('/search/suggestions', [App\Http\Controllers\SearchController::class, 'suggestions'])
        ->middleware('throttle:60,1')
        ->name('search.suggestions'); // 60 suggestions per minute
});

// Dashboard for analytics
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Keep posts route for backward compatibility, but redirect to home for authenticated users
Route::get('/posts', [App\Http\Controllers\PostController::class, 'index'])->name('posts.index');
Route::middleware(['auth'])->group(function () {
    Route::get('/posts/create', [App\Http\Controllers\PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [App\Http\Controllers\PostController::class, 'store'])
        ->middleware('throttle:10,5') // 10 posts per 5 minutes (more reasonable limit)
        ->name('posts.store');
    Route::get('/posts/{post}', [App\Http\Controllers\PostController::class, 'show'])->name('posts.show');
    Route::post('/posts/{post}/vote', [App\Http\Controllers\VoteController::class, 'votePost'])
        ->middleware('throttle:30,5') // 30 votes per 5 minutes
        ->name('votes.post');
    Route::post('/posts/{post}/comments', [App\Http\Controllers\CommentController::class, 'store'])
        ->middleware('throttle:10,5') // 10 comments per 5 minutes
        ->name('comments.store');
    Route::post('/comments/{comment}/best-answer', [App\Http\Controllers\CommentController::class, 'markBestAnswer'])
        ->middleware('throttle:10,5') // 10 per 5 minutes
        ->name('comments.best-answer');
    Route::post('/comments/{comment}/vote', [App\Http\Controllers\VoteController::class, 'voteComment'])
        ->middleware('throttle:30,5') // 30 votes per 5 minutes
        ->name('votes.comment');
    Route::post('/posts/{post}/validate', [App\Http\Controllers\IdeaValidationController::class, 'store'])
        ->middleware('throttle:5,30') // 5 validations per 30 minutes
        ->name('idea-validations.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:10,60') // 10 updates per hour
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:3,1440') // 3 requests per 24 hours
        ->name('profile.destroy');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])
        ->middleware('throttle:60,1')
        ->name('notifications.read'); // 60 actions per minute
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])
        ->middleware('throttle:10,1')
        ->name('notifications.read-all'); // 10 actions per minute
    Route::delete('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'destroy'])
        ->middleware('throttle:60,1')
        ->name('notifications.destroy'); // 60 deletions per minute

    // Settings
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

    // Follow System
    Route::post('/users/{user}/follow', [App\Http\Controllers\FollowController::class, 'follow'])
        ->middleware('throttle:20,5')
        ->name('users.follow'); // 20 actions per 5 minutes
    Route::delete('/users/{user}/unfollow', [App\Http\Controllers\FollowController::class, 'unfollow'])
        ->middleware('throttle:20,5')
        ->name('users.unfollow'); // 20 actions per 5 minutes
    Route::get('/users/{user}/followers', [App\Http\Controllers\FollowController::class, 'followers'])->name('users.followers');
    Route::get('/users/{user}/following', [App\Http\Controllers\FollowController::class, 'following'])->name('users.following');

    // Report Content
    Route::post('/posts/{post}/report', [App\Http\Controllers\ReportController::class, 'reportPost'])
        ->middleware('throttle:10,60')
        ->name('posts.report'); // 10 reports per hour
    Route::post('/comments/{comment}/report', [App\Http\Controllers\ReportController::class, 'reportComment'])
        ->middleware('throttle:10,60')
        ->name('comments.report'); // 10 reports per hour
    Route::post('/users/{user}/report', [App\Http\Controllers\ReportController::class, 'reportUser'])
        ->middleware('throttle:10,60')
        ->name('users.report'); // 10 reports per hour

    // Bookmarks
    Route::get('/bookmarks', [App\Http\Controllers\BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/posts/{post}/bookmark', [App\Http\Controllers\BookmarkController::class, 'store'])
        ->middleware('throttle:30,5')
        ->name('posts.bookmark'); // 30 bookmarks per 5 minutes
    Route::delete('/posts/{post}/unbookmark', [App\Http\Controllers\BookmarkController::class, 'destroy'])
        ->middleware('throttle:30,5')
        ->name('posts.unbookmark'); // 30 unbookmarks per 5 minutes
});

// Marketplace Routes
Route::get('/marketplace', [App\Http\Controllers\Marketplace\ProductController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/search', [App\Http\Controllers\Marketplace\SearchController::class, 'search'])->name('marketplace.search');

Route::middleware('auth')->group(function () {
    // Products
    Route::get('marketplace/products', [App\Http\Controllers\Marketplace\ProductController::class, 'index'])->name('marketplace.products.index');
    Route::get('marketplace/products/create', [App\Http\Controllers\Marketplace\ProductController::class, 'create'])->name('marketplace.products.create');
    Route::post('marketplace/products', [App\Http\Controllers\Marketplace\ProductController::class, 'store'])
        ->middleware('throttle:3,60') // 3 products per hour
        ->name('marketplace.products.store');
    Route::get('marketplace/products/{product}', [App\Http\Controllers\Marketplace\ProductController::class, 'show'])->name('marketplace.products.show');
    Route::get('marketplace/products/{product}/edit', [App\Http\Controllers\Marketplace\ProductController::class, 'edit'])->name('marketplace.products.edit');
    Route::put('marketplace/products/{product}', [App\Http\Controllers\Marketplace\ProductController::class, 'update'])
        ->middleware('throttle:10,60') // 10 updates per hour
        ->name('marketplace.products.update');
    Route::delete('marketplace/products/{product}', [App\Http\Controllers\Marketplace\ProductController::class, 'destroy'])->name('marketplace.products.destroy');

    // Orders
    Route::get('marketplace/orders', [App\Http\Controllers\Marketplace\OrderController::class, 'index'])->name('marketplace.orders.index');
    Route::get('marketplace/orders/{order}', [App\Http\Controllers\Marketplace\OrderController::class, 'show'])->name('marketplace.orders.show');
    Route::post('marketplace/orders', [App\Http\Controllers\Marketplace\OrderController::class, 'store'])
        ->middleware('throttle:10,1') // 10 orders per minute
        ->name('marketplace.orders.store');
    Route::post('/marketplace/orders/{order}/cancel', [App\Http\Controllers\Marketplace\OrderController::class, 'cancel'])
        ->middleware('throttle:5,60') // 5 cancellations per hour
        ->name('marketplace.orders.cancel');

    // Cart
    Route::get('/marketplace/cart', [App\Http\Controllers\Marketplace\CartController::class, 'index'])->name('marketplace.cart');

    // Downloads
    Route::get('/marketplace/products/{product}/download', [App\Http\Controllers\Marketplace\DownloadController::class, 'download'])
        ->middleware('throttle:20,5') // 20 downloads per 5 minutes
        ->name('marketplace.products.download');

    // Withdrawals
    Route::get('marketplace/withdrawals', [App\Http\Controllers\Marketplace\WithdrawalController::class, 'index'])->name('marketplace.withdrawals.index');
    Route::get('marketplace/withdrawals/create', [App\Http\Controllers\Marketplace\WithdrawalController::class, 'create'])->name('marketplace.withdrawals.create');
    Route::post('marketplace/withdrawals', [App\Http\Controllers\Marketplace\WithdrawalController::class, 'store'])
        ->middleware('throttle:3,1440') // 3 requests per 24 hours (1440 minutes)
        ->name('marketplace.withdrawals.store');
    Route::get('marketplace/withdrawals/{withdrawal}', [App\Http\Controllers\Marketplace\WithdrawalController::class, 'show'])->name('marketplace.withdrawals.show');

    // Sales Analytics
    Route::get('/marketplace/sales/analytics', [App\Http\Controllers\Marketplace\SalesAnalyticsController::class, 'index'])->name('marketplace.sales.analytics');

    // Clipper Routes
    Route::prefix('clipper')->name('clipper.')->group(function () {
        // Top Up
        Route::get('top-ups', [App\Http\Controllers\Clipper\TopUpController::class, 'index'])->name('top-ups.index');
        Route::get('top-ups/create', [App\Http\Controllers\Clipper\TopUpController::class, 'create'])->name('top-ups.create');
        Route::post('top-ups', [App\Http\Controllers\Clipper\TopUpController::class, 'store'])
            ->middleware('throttle:5,60') // 5 requests per hour
            ->name('top-ups.store');
        Route::get('top-ups/{topUp}', [App\Http\Controllers\Clipper\TopUpController::class, 'show'])->name('top-ups.show');
        Route::post('top-ups/webhook', [App\Http\Controllers\Clipper\TopUpController::class, 'webhook'])->name('top-ups.webhook');
        
        // Campaigns (Creator)
        Route::get('campaigns', [App\Http\Controllers\Clipper\CampaignController::class, 'index'])->name('campaigns.index');
        Route::get('campaigns/create', [App\Http\Controllers\Clipper\CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns', [App\Http\Controllers\Clipper\CampaignController::class, 'store'])
            ->middleware('throttle:3,60') // 3 campaigns per hour
            ->name('campaigns.store');
        Route::get('campaigns/{campaign}', [App\Http\Controllers\Clipper\CampaignController::class, 'show'])->name('campaigns.show');
        Route::get('campaigns/{campaign}/edit', [App\Http\Controllers\Clipper\CampaignController::class, 'edit'])->name('campaigns.edit');
        Route::put('campaigns/{campaign}', [App\Http\Controllers\Clipper\CampaignController::class, 'update'])
            ->middleware('throttle:10,60') // 10 updates per hour
            ->name('campaigns.update');
        Route::delete('campaigns/{campaign}', [App\Http\Controllers\Clipper\CampaignController::class, 'destroy'])->name('campaigns.destroy');
        Route::post('campaigns/{campaign}/activate', [App\Http\Controllers\Clipper\CampaignController::class, 'activate'])->name('campaigns.activate');
        Route::post('campaigns/{campaign}/pause', [App\Http\Controllers\Clipper\CampaignController::class, 'pause'])->name('campaigns.pause');
        Route::post('campaigns/{campaign}/cancel', [App\Http\Controllers\Clipper\CampaignController::class, 'cancel'])->name('campaigns.cancel');
        Route::post('campaigns/{campaign}/share', [App\Http\Controllers\Clipper\CampaignController::class, 'shareAsPost'])->name('campaigns.share');
        
        // Clips (Clipper)
        Route::get('campaigns/available', [App\Http\Controllers\Clipper\ClipController::class, 'availableCampaigns'])->name('campaigns.available');
        Route::get('clips', [App\Http\Controllers\Clipper\ClipController::class, 'index'])->name('clips.index');
        Route::get('clips/create', [App\Http\Controllers\Clipper\ClipController::class, 'create'])->name('clips.create');
        Route::post('clips', [App\Http\Controllers\Clipper\ClipController::class, 'store'])
            ->middleware('throttle:10,5') // 10 clips per 5 minutes
            ->name('clips.store');
        Route::get('clips/{clip}', [App\Http\Controllers\Clipper\ClipController::class, 'show'])->name('clips.show');
        Route::get('clips/{clip}/edit', [App\Http\Controllers\Clipper\ClipController::class, 'edit'])->name('clips.edit');
        Route::put('clips/{clip}', [App\Http\Controllers\Clipper\ClipController::class, 'update'])->name('clips.update');
        Route::delete('clips/{clip}', [App\Http\Controllers\Clipper\ClipController::class, 'destroy'])->name('clips.destroy');
        Route::post('clips/{clip}/track-views', [App\Http\Controllers\Clipper\ClipController::class, 'trackViews'])
            ->middleware('throttle:10,60') // 10 requests per hour (critical, prevent abuse)
            ->name('clips.track-views');
        
        // Wallets
        Route::get('wallet/creator', [App\Http\Controllers\Clipper\CreatorWalletController::class, 'index'])->name('wallet.creator');
        Route::get('wallet/clipper', [App\Http\Controllers\Clipper\ClipperWalletController::class, 'index'])->name('wallet.clipper');
        Route::get('wallet/creator/history', [App\Http\Controllers\Clipper\CreatorWalletController::class, 'history'])->name('wallet.creator.history');
        Route::get('wallet/clipper/history', [App\Http\Controllers\Clipper\ClipperWalletController::class, 'history'])->name('wallet.clipper.history');
        
        // Campaign Analytics (Brand Dashboard)
        Route::get('campaigns/analytics', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'index'])->name('campaigns.analytics');
        Route::get('campaigns/{campaign}/analytics', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'show'])->name('campaigns.analytics.show');
        Route::get('campaigns/{campaign}/analytics/views-chart', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'getViewsChart'])->name('campaigns.analytics.views-chart');
        Route::get('campaigns/{campaign}/analytics/roi', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'getROI'])->name('campaigns.analytics.roi');
        
        // Clipper Withdrawals
        Route::get('withdrawals', [App\Http\Controllers\Clipper\ClipperWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('withdrawals/create', [App\Http\Controllers\Clipper\ClipperWithdrawalController::class, 'create'])->name('withdrawals.create');
        Route::post('withdrawals', [App\Http\Controllers\Clipper\ClipperWithdrawalController::class, 'store'])
            ->middleware('throttle:3,1440') // 3 requests per 24 hours (very strict, financial operation)
            ->name('withdrawals.store');
        Route::get('withdrawals/{withdrawal}', [App\Http\Controllers\Clipper\ClipperWithdrawalController::class, 'show'])->name('withdrawals.show');
    });

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('withdrawals', App\Http\Controllers\Admin\AdminWithdrawalController::class)->names([
            'index' => 'withdrawals.index',
            'show' => 'withdrawals.show',
        ]);
        Route::post('/withdrawals/{withdrawal}/approve', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'approve'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'reject'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('withdrawals.reject');
        Route::post('/withdrawals/{withdrawal}/complete', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'complete'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('withdrawals.complete');
        Route::get('products', [App\Http\Controllers\Admin\ProductModerationController::class, 'index'])->name('products.index');
        Route::put('products/{product}', [App\Http\Controllers\Admin\ProductModerationController::class, 'update'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('products.update');
        Route::delete('products/{product}', [App\Http\Controllers\Admin\ProductModerationController::class, 'destroy'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('products.destroy');
        
        // FAQs
        Route::get('faqs', [App\Http\Controllers\Admin\FaqController::class, 'index'])->name('faqs.index');
        Route::get('faqs/create', [App\Http\Controllers\Admin\FaqController::class, 'create'])->name('faqs.create');
        Route::post('faqs', [App\Http\Controllers\Admin\FaqController::class, 'store'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('faqs.store');
        Route::get('faqs/{faq}', [App\Http\Controllers\Admin\FaqController::class, 'show'])->name('faqs.show');
        Route::get('faqs/{faq}/edit', [App\Http\Controllers\Admin\FaqController::class, 'edit'])->name('faqs.edit');
        Route::put('faqs/{faq}', [App\Http\Controllers\Admin\FaqController::class, 'update'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('faqs.update');
        Route::delete('faqs/{faq}', [App\Http\Controllers\Admin\FaqController::class, 'destroy'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('faqs.destroy');
        Route::post('faqs/{faq}/toggle-status', [App\Http\Controllers\Admin\FaqController::class, 'toggleStatus'])->name('faqs.toggle-status');
        Route::post('faqs/reorder', [App\Http\Controllers\Admin\FaqController::class, 'reorder'])->name('faqs.reorder');
        
        // Documentations
        Route::get('documentations', [App\Http\Controllers\Admin\DocumentationController::class, 'index'])->name('documentations.index');
        Route::get('documentations/create', [App\Http\Controllers\Admin\DocumentationController::class, 'create'])->name('documentations.create');
        Route::post('documentations', [App\Http\Controllers\Admin\DocumentationController::class, 'store'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('documentations.store');
        Route::get('documentations/{documentation}', [App\Http\Controllers\Admin\DocumentationController::class, 'show'])->name('documentations.show');
        Route::get('documentations/{documentation}/edit', [App\Http\Controllers\Admin\DocumentationController::class, 'edit'])->name('documentations.edit');
        Route::put('documentations/{documentation}', [App\Http\Controllers\Admin\DocumentationController::class, 'update'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('documentations.update');
        Route::delete('documentations/{documentation}', [App\Http\Controllers\Admin\DocumentationController::class, 'destroy'])
            ->middleware('throttle:20,1') // 20 actions per minute
            ->name('documentations.destroy');
        Route::post('documentations/{documentation}/toggle-status', [App\Http\Controllers\Admin\DocumentationController::class, 'toggleStatus'])->name('documentations.toggle-status');
        Route::post('documentations/reorder', [App\Http\Controllers\Admin\DocumentationController::class, 'reorder'])->name('documentations.reorder');
        
        // Clipper Admin Routes
        Route::resource('campaigns', App\Http\Controllers\Admin\AdminCampaignController::class)->only(['index', 'show']);
        Route::resource('clips', App\Http\Controllers\Admin\AdminClipController::class)->only(['index', 'show']);
        Route::post('clips/{clip}/approve', [App\Http\Controllers\Admin\AdminClipController::class, 'approve'])->name('clips.approve');
        Route::post('clips/{clip}/reject', [App\Http\Controllers\Admin\AdminClipController::class, 'reject'])->name('clips.reject');
        Route::post('clips/{clip}/adjust-reward', [App\Http\Controllers\Admin\AdminClipController::class, 'adjustReward'])->name('clips.adjust-reward');
        
        // Brand Approvals
        Route::get('brand-approvals', [App\Http\Controllers\Admin\AdminBrandApprovalController::class, 'index'])->name('brand-approvals.index');
        Route::get('brand-approvals/{registration}', [App\Http\Controllers\Admin\AdminBrandApprovalController::class, 'show'])->name('brand-approvals.show');
        Route::post('brand-approvals/{registration}/approve', [App\Http\Controllers\Admin\AdminBrandApprovalController::class, 'approve'])->name('brand-approvals.approve');
        Route::post('brand-approvals/{registration}/reject', [App\Http\Controllers\Admin\AdminBrandApprovalController::class, 'reject'])->name('brand-approvals.reject');
        
        Route::prefix('wallets')->name('wallets.')->group(function () {
            Route::get('ledger', [App\Http\Controllers\Admin\AdminWalletController::class, 'viewLedger'])->name('ledger');
            Route::get('audit-log', [App\Http\Controllers\Admin\AdminWalletController::class, 'viewAuditLog'])->name('audit-log');
        });

        // User Management
        Route::resource('users', App\Http\Controllers\Admin\UserManagementController::class)->only(['index', 'show', 'edit', 'update']);
        Route::post('users/{user}/ban', [App\Http\Controllers\Admin\UserManagementController::class, 'ban'])->name('users.ban');
        Route::post('users/{user}/unban', [App\Http\Controllers\Admin\UserManagementController::class, 'unban'])->name('users.unban');

        // Reports Management
        Route::resource('reports', App\Http\Controllers\Admin\ReportController::class)->only(['index', 'show', 'update']);
        Route::post('reports/{report}/resolve', [App\Http\Controllers\Admin\ReportController::class, 'resolve'])->name('reports.resolve');
        Route::post('reports/{report}/dismiss', [App\Http\Controllers\Admin\ReportController::class, 'dismiss'])->name('reports.dismiss');
    });
});

// Payment Webhook (no auth required)
Route::post('/payment/webhook', [App\Http\Controllers\PaymentController::class, 'webhook'])->name('payment.webhook');

// Explorer Routes
Route::middleware('auth')->group(function () {
    Route::get('/explorer', [App\Http\Controllers\ExplorerController::class, 'index'])->name('explorer.index');
    Route::get('/explorer/search', [App\Http\Controllers\ExplorerController::class, 'search'])
        ->middleware('throttle:30,1') // 30 searches per minute
        ->name('explorer.search');
});

// FAQs (Public)
Route::get('/faq', [App\Http\Controllers\FaqController::class, 'index'])->name('faqs.index');
Route::get('/faq/{id}', [App\Http\Controllers\FaqController::class, 'show'])->name('faqs.show');

// Documentations (Public)
Route::get('/documentation', [App\Http\Controllers\DocumentationController::class, 'index'])->name('documentations.index');
Route::get('/documentation/{slug}', [App\Http\Controllers\DocumentationController::class, 'show'])->name('documentations.show');
Route::get('/documentation/search', [App\Http\Controllers\DocumentationController::class, 'search'])->name('documentations.search');

// Legal Pages (Public)
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/privacy-policy', function () {
        return Inertia::render('Legal/PrivacyPolicy');
    })->name('privacy-policy');

    Route::get('/terms-conditions', function () {
        return Inertia::render('Legal/TermsConditions');
    })->name('terms-conditions');

    Route::get('/disclaimer', function () {
        return Inertia::render('Legal/Disclaimer');
    })->name('disclaimer');

    Route::get('/cookie-policy', function () {
        return Inertia::render('Legal/CookiePolicy');
    })->name('cookie-policy');

    Route::get('/refund-policy', function () {
        return Inertia::render('Legal/RefundPolicy');
    })->name('refund-policy');

    Route::get('/contact', function () {
        return Inertia::render('Legal/Contact');
    })->name('contact');

    Route::post('/contact', [App\Http\Controllers\Legal\ContactController::class, 'submit'])
        ->middleware('throttle:3,60') // 3 submissions per hour
        ->name('contact.submit');
});

require __DIR__ . '/auth.php';
