<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\CmsPageController as AdminCmsPageController;
use App\Http\Controllers\Admin\NoteController as AdminNoteController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WithdrawController as AdminWithdrawController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicCmsPageController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SimulatorController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Admin\ExchangeRateController;
use App\Http\Controllers\Admin\DocumentationController as AdminDocumentationController;
use App\Http\Controllers\Admin\LandingPageController as AdminLandingPageController;
use App\Http\Controllers\Admin\SocialMediaController as AdminSocialMediaController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\NoteAttachmentController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Locale & i18n routes
Route::get('/locale/{locale}', [LocaleController::class, 'switchLocale'])->name('locale.switch');
Route::post('/locale/currency', [LocaleController::class, 'setCurrency'])->middleware(['auth', 'username.setup'])->name('locale.set-currency');
Route::post('/locale/timezone', [LocaleController::class, 'setTimezone'])->middleware(['auth', 'username.setup'])->name('locale.set-timezone');

// Simulators (public for marketing)
Route::get('/simulators', [SimulatorController::class, 'index'])->name('simulators.index');

// Contact (public)
Route::get('/contact', [App\Http\Controllers\ContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

// FAQ (public - dynamic from database)
Route::get('/faq', function () {
    $faqs = \App\Models\Faq::active()->ordered()->get();
    return view('faq', compact('faqs'));
})->name('faq');

// CMS Pages (public - dynamic from database)
Route::get('/page/{cmsPage}', [PublicCmsPageController::class, 'show'])->name('cms.show');

// Marketplace routes
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/{note}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::post('/marketplace/{note}/purchase', [MarketplaceController::class, 'purchase'])->middleware(['auth', 'username.setup'])->name('marketplace.purchase');

// Public profile routes
Route::get('/u/{username}', [PublicProfileController::class, 'show'])->name('public.profile.show');

// Review routes
Route::post('/notes/{note}/reviews', [ReviewController::class, 'store'])->middleware(['auth', 'username.setup'])->name('reviews.store');
Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->middleware(['auth', 'username.setup'])->name('reviews.update');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->middleware(['auth', 'username.setup'])->name('reviews.destroy');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'username.setup'])->name('dashboard');

// Setup username route (must be before username.setup middleware)
Route::middleware('auth')->prefix('setup-username')->name('setup-username.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SetupUsernameController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\SetupUsernameController::class, 'store'])->name('store');
});

Route::middleware(['auth', 'username.setup'])->group(function () {
    Route::resource('notes', NoteController::class);
    Route::get('/notes/{note}/attachments/{filename}', [NoteAttachmentController::class, 'download'])->name('notes.attachments.download');

    // Folders (Premium feature - enhanced organization)
    Route::middleware('premium')->group(function () {
        Route::resource('folders', \App\Http\Controllers\FolderController::class);
        Route::post('/folders/update-order', [\App\Http\Controllers\FolderController::class, 'updateOrder'])->name('folders.update-order');
    });

    // Workspaces (Premium feature - multi workspace)
    Route::middleware('premium')->group(function () {
        Route::resource('workspaces', \App\Http\Controllers\WorkspaceController::class);
        Route::post('/workspaces/{workspace}/sell', [\App\Http\Controllers\WorkspaceController::class, 'sell'])->name('workspaces.sell');
        Route::post('/workspaces/{workspace}/purchase', [\App\Http\Controllers\WorkspaceController::class, 'purchase'])->name('workspaces.purchase');
    });

    // AI routes - Basic features (available to all authenticated users)
    Route::post('/ai/analyze', [AiController::class, 'analyze'])->name('ai.analyze');
    Route::get('/ai/status', [AiController::class, 'status'])->name('ai.status');

    // MyNoteds (AI Memory Platform) routes - Premium features only
    Route::middleware('premium')->prefix('mynoteds')->name('mynoteds.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MyNotedsController::class, 'index'])->name('index');
        Route::get('/ask', [\App\Http\Controllers\MyNotedsController::class, 'ask'])->name('ask'); // Q&A interface
        Route::get('/search', [\App\Http\Controllers\MyNotedsController::class, 'search'])->name('search'); // Semantic search interface
        Route::get('/insights', [\App\Http\Controllers\MyNotedsController::class, 'insights'])->name('insights'); // AI insights
    });

    // AI Memory Platform API routes - Premium features only
    Route::middleware('premium')->prefix('ai-memory')->name('ai-memory.')->group(function () {
        Route::post('/ask', [AiController::class, 'ask'])->name('ask'); // Q&A API
        Route::post('/search', [AiController::class, 'semanticSearch'])->name('search'); // Semantic search API
        Route::post('/context-links', [AiController::class, 'contextLinks'])->name('context-links'); // Context linking API
        // More premium AI features will be added here
    });

    // Subscription routes
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/create', [SubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
    Route::get('/subscription/{subscription}', [SubscriptionController::class, 'show'])->name('subscription.show');

    // Wallet routes
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');
    Route::get('/wallet/topup-checkout', [WalletController::class, 'topupCheckout'])->name('wallet.topup-checkout');

    // Withdraw routes
    Route::get('/wallet/withdraw', [WithdrawController::class, 'create'])->name('wallet.withdraw.create');
    Route::post('/wallet/withdraw', [WithdrawController::class, 'store'])->name('wallet.withdraw.store');

    // Featured Notes routes
    Route::get('/featured-notes', [\App\Http\Controllers\FeaturedNoteController::class, 'index'])->name('featured-notes.index');
    Route::get('/featured-notes/create', [\App\Http\Controllers\FeaturedNoteController::class, 'create'])->name('featured-notes.create');
    Route::post('/featured-notes', [\App\Http\Controllers\FeaturedNoteController::class, 'store'])->name('featured-notes.store');

    // Referral routes
    Route::get('/referral', [ReferralController::class, 'index'])->name('referral.index');
    Route::get('/referral/statistics', [ReferralController::class, 'statistics'])->name('referral.statistics');

    // Support Ticket routes
    Route::resource('support-tickets', SupportTicketController::class);
    Route::post('/support-tickets/{supportTicket}/reply', [SupportTicketController::class, 'reply'])->name('support-tickets.reply');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::prefix('admin')->middleware(['auth', 'role:admin', 'username.setup'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('faqs', AdminFaqController::class);
    Route::resource('cms-pages', AdminCmsPageController::class);
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/withdraws', [AdminWithdrawController::class, 'index'])->name('withdraws.index');
    Route::get('/withdraws/{withdraw}', [AdminWithdrawController::class, 'show'])->name('withdraws.show');
    Route::patch('/withdraws/{withdraw}', [AdminWithdrawController::class, 'update'])->name('withdraws.update');
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/create', [AdminSubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('/subscriptions', [AdminSubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/{subscription}/approve', [AdminSubscriptionController::class, 'approve'])->name('subscriptions.approve');
    Route::post('/subscriptions/{subscription}/reject', [AdminSubscriptionController::class, 'reject'])->name('subscriptions.reject');
    Route::get('/notes', [AdminNoteController::class, 'index'])->name('notes.index');
    Route::resource('tickets', AdminTicketController::class)->only(['index', 'show', 'update']);
    Route::post('/tickets/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply');
    Route::resource('exchange-rates', ExchangeRateController::class)->except(['show']);
    Route::resource('documentations', AdminDocumentationController::class);
    Route::resource('landing-page', AdminLandingPageController::class);
    Route::resource('social-media', AdminSocialMediaController::class);
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-s3', [AdminSettingsController::class, 'testS3'])->name('settings.test-s3');

    // Featured Notes Admin routes
    Route::get('/featured-notes', [\App\Http\Controllers\Admin\FeaturedNoteController::class, 'index'])->name('featured-notes.index');
    Route::get('/featured-notes/{featuredNote}', [\App\Http\Controllers\Admin\FeaturedNoteController::class, 'show'])->name('featured-notes.show');
    Route::post('/featured-notes/{featuredNote}/approve', [\App\Http\Controllers\Admin\FeaturedNoteController::class, 'approve'])->name('featured-notes.approve');
    Route::post('/featured-notes/{featuredNote}/reject', [\App\Http\Controllers\Admin\FeaturedNoteController::class, 'reject'])->name('featured-notes.reject');
});

// Public Documentation routes
Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');
Route::get('/docs/{category}', [DocumentationController::class, 'category'])->name('docs.category');
Route::get('/docs/{category}/{documentation:slug}', [DocumentationController::class, 'show'])->name('docs.show');
Route::post('/docs/{category}/{documentation:slug}/helpful', [DocumentationController::class, 'markHelpful'])->middleware(['auth', 'username.setup'])->name('docs.helpful');

// Midtrans Webhook & Payment Callback Routes (no auth required, CSRF exempt)
Route::post('/wallet/webhook', [WalletController::class, 'webhook'])
    ->middleware('web')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('wallet.webhook');

// Payment Notification URL (alternative webhook endpoint)
Route::post('/payment/callback', [WalletController::class, 'paymentCallback'])
    ->middleware('web')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('payment.callback');

// Payment Redirect URLs (no auth required for redirects)
Route::get('/payment/finish', [WalletController::class, 'paymentFinish'])
    ->name('payment.finish');

Route::get('/payment/unfinish', [WalletController::class, 'paymentUnfinish'])
    ->name('payment.unfinish');

Route::get('/payment/error', [WalletController::class, 'paymentError'])
    ->name('payment.error');

require __DIR__ . '/auth.php';
