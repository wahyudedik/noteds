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
Route::get('/home', [App\Http\Controllers\PostController::class, 'index'])->middleware(['auth', 'verified'])->name('home');

// Dashboard for analytics
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Keep posts route for backward compatibility, but redirect to home for authenticated users
Route::get('/posts', [App\Http\Controllers\PostController::class, 'index'])->name('posts.index');
Route::middleware(['auth'])->group(function () {
    Route::get('/posts/create', [App\Http\Controllers\PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [App\Http\Controllers\PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [App\Http\Controllers\PostController::class, 'show'])->name('posts.show');
    Route::post('/posts/{post}/vote', [App\Http\Controllers\VoteController::class, 'votePost'])->name('votes.post');
    Route::post('/posts/{post}/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/best-answer', [App\Http\Controllers\CommentController::class, 'markBestAnswer'])->name('comments.best-answer');
    Route::post('/comments/{comment}/vote', [App\Http\Controllers\VoteController::class, 'voteComment'])->name('votes.comment');
    Route::post('/posts/{post}/validate', [App\Http\Controllers\IdeaValidationController::class, 'store'])->name('idea-validations.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
});

// Marketplace Routes
Route::get('/marketplace', [App\Http\Controllers\Marketplace\ProductController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/search', [App\Http\Controllers\Marketplace\SearchController::class, 'search'])->name('marketplace.search');

Route::middleware('auth')->group(function () {
    // Products
    Route::resource('marketplace/products', App\Http\Controllers\Marketplace\ProductController::class)->names([
        'index' => 'marketplace.products.index',
        'create' => 'marketplace.products.create',
        'store' => 'marketplace.products.store',
        'show' => 'marketplace.products.show',
        'edit' => 'marketplace.products.edit',
        'update' => 'marketplace.products.update',
        'destroy' => 'marketplace.products.destroy',
    ]);

    // Orders
    Route::resource('marketplace/orders', App\Http\Controllers\Marketplace\OrderController::class)->names([
        'index' => 'marketplace.orders.index',
        'show' => 'marketplace.orders.show',
        'store' => 'marketplace.orders.store',
    ]);
    Route::post('/marketplace/orders/{order}/cancel', [App\Http\Controllers\Marketplace\OrderController::class, 'cancel'])->name('marketplace.orders.cancel');

    // Cart
    Route::get('/marketplace/cart', [App\Http\Controllers\Marketplace\CartController::class, 'index'])->name('marketplace.cart');

    // Downloads
    Route::get('/marketplace/products/{product}/download', [App\Http\Controllers\Marketplace\DownloadController::class, 'download'])->name('marketplace.products.download');

    // Withdrawals
    Route::resource('marketplace/withdrawals', App\Http\Controllers\Marketplace\WithdrawalController::class)->names([
        'index' => 'marketplace.withdrawals.index',
        'create' => 'marketplace.withdrawals.create',
        'store' => 'marketplace.withdrawals.store',
        'show' => 'marketplace.withdrawals.show',
    ]);

    // Sales Analytics
    Route::get('/marketplace/sales/analytics', [App\Http\Controllers\Marketplace\SalesAnalyticsController::class, 'index'])->name('marketplace.sales.analytics');

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('withdrawals', App\Http\Controllers\Admin\AdminWithdrawalController::class)->names([
            'index' => 'withdrawals.index',
            'show' => 'withdrawals.show',
        ]);
        Route::post('/withdrawals/{withdrawal}/approve', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
        Route::post('/withdrawals/{withdrawal}/complete', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'complete'])->name('withdrawals.complete');
        Route::resource('products', App\Http\Controllers\Admin\ProductModerationController::class)->names([
            'index' => 'products.index',
            'update' => 'products.update',
            'destroy' => 'products.destroy',
        ]);
    });
});

// Payment Webhook (no auth required)
Route::post('/payment/webhook', [App\Http\Controllers\PaymentController::class, 'webhook'])->name('payment.webhook');

// Explorer Routes
Route::middleware('auth')->group(function () {
    Route::get('/explorer', [App\Http\Controllers\ExplorerController::class, 'index'])->name('explorer.index');
    Route::get('/explorer/search', [App\Http\Controllers\ExplorerController::class, 'search'])->name('explorer.search');
});

require __DIR__.'/auth.php';
