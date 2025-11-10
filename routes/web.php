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
use App\Http\Controllers\Admin\CommissionTierController as AdminCommissionTierController;
use App\Http\Controllers\Admin\PostModerationController;
use App\Http\Controllers\Admin\NoteModerationController;
use App\Http\Controllers\Admin\AccountModerationController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicCmsPageController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\NoteReportController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserReportController;
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
use App\Http\Controllers\Admin\TaxRuleController as AdminTaxRuleController;
use App\Http\Controllers\Admin\PriceRuleController as AdminPriceRuleController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\NoteAttachmentController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\PostAnalyticsController;
use App\Http\Controllers\ForumPreferenceController;
use App\Http\Controllers\NoteConversationController;
use App\Http\Controllers\NoteReviewReplyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Locale & i18n routes
Route::get('/locale/{locale}', [LocaleController::class, 'switchLocale'])->name('locale.switch');
Route::post('/locale/currency', [LocaleController::class, 'setCurrency'])->middleware(['auth', 'verified', 'username.setup'])->name('locale.set-currency');
Route::post('/locale/timezone', [LocaleController::class, 'setTimezone'])->middleware(['auth', 'verified', 'username.setup'])->name('locale.set-timezone');

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
Route::get('/page', [PublicCmsPageController::class, 'index'])->name('cms.index');
Route::get('/page/{cmsPage}', [PublicCmsPageController::class, 'show'])->name('cms.show');

// Marketplace routes
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/{note}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::post('/marketplace/{note}/purchase', [MarketplaceController::class, 'purchase'])->middleware(['auth', 'verified', 'username.setup', 'buyer'])->name('marketplace.purchase');

// Resale routes - for buyers who own notes (scarcity mode only)
Route::middleware(['auth', 'verified', 'username.setup'])->group(function () {
    Route::get('/notes/{note}/resale', [NoteController::class, 'resaleForm'])->name('notes.resale.form');
    Route::post('/notes/{note}/resale', [NoteController::class, 'resale'])->name('notes.resale');
});

    // Public profile routes
    Route::get('/u/{username}', [PublicProfileController::class, 'show'])->name('public.profile.show');
    Route::get('/u/{username}/ai-chat', [PublicProfileController::class, 'aiChat'])->name('public.profile.ai-chat');
    Route::post('/u/{username}/ai-chat/ask', [PublicProfileController::class, 'askSeller'])->name('public.profile.ai-chat.ask');

    // Forum routes - Available for all authenticated users
    Route::middleware(['auth', 'verified', 'username.setup'])->prefix('forum')->name('forum.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ForumController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\ForumController::class, 'store'])->name('store');
        Route::get('/analytics', [PostAnalyticsController::class, 'index'])->name('analytics');
        Route::get('/preferences', [ForumPreferenceController::class, 'edit'])->name('preferences.edit');
        Route::put('/preferences', [ForumPreferenceController::class, 'update'])->name('preferences.update');
        Route::get('/hashtag/{slug}', [\App\Http\Controllers\ForumController::class, 'hashtag'])->name('hashtag');
        Route::get('/bookmarks', [\App\Http\Controllers\PostBookmarkController::class, 'index'])->name('bookmarks');
        Route::post('/post/{post}/bookmark', [\App\Http\Controllers\PostBookmarkController::class, 'toggle'])->name('bookmark');
        Route::get('/post/{post}', [\App\Http\Controllers\ForumController::class, 'show'])->name('show');
        Route::put('/post/{post}', [\App\Http\Controllers\ForumController::class, 'update'])->name('update');
        Route::post('/post/{post}/like', [\App\Http\Controllers\ForumController::class, 'like'])->name('like');
        Route::post('/post/{post}/share', [\App\Http\Controllers\ForumController::class, 'share'])->name('share');
        Route::post('/post/{post}/comment', [\App\Http\Controllers\ForumController::class, 'comment'])->name('comment');
        Route::put('/comment/{comment}', [\App\Http\Controllers\ForumController::class, 'updateComment'])->name('comment.update');
        Route::delete('/comment/{comment}', [\App\Http\Controllers\ForumController::class, 'destroyComment'])->name('comment.destroy');
        Route::post('/comment/{comment}/like', [\App\Http\Controllers\ForumController::class, 'likeComment'])->name('comment.like');
        Route::post('/post/{post}/pin', [\App\Http\Controllers\ForumController::class, 'pin'])->name('pin');
        Route::post('/post/{post}/report', [\App\Http\Controllers\PostReportController::class, 'store'])->name('report');
        Route::delete('/post/{post}', [\App\Http\Controllers\ForumController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth', 'verified', 'username.setup'])->group(function () {
    Route::get('/note-conversations', [NoteConversationController::class, 'index'])->name('note-conversations.index');
    Route::get('/note-conversations/{conversation}', [NoteConversationController::class, 'show'])->name('note-conversations.show');
    Route::post('/note-conversations/{conversation}', [NoteConversationController::class, 'store'])->name('note-conversations.store');

    Route::post('/notes/{note}/report', [NoteReportController::class, 'store'])->name('notes.report');
    Route::post('/users/{user}/report', [UserReportController::class, 'store'])->name('users.report');
});

    // Follow routes
    Route::get('/follow/{user}', function (\App\Models\User $user) {
        return redirect()->route('public.profile.show', $user->username);
    })->name('follow.view');

    Route::middleware(['auth', 'verified', 'username.setup'])->prefix('follow')->name('follow.')->group(function () {
        Route::post('/{user}', [\App\Http\Controllers\FollowController::class, 'follow'])->name('follow');
        Route::delete('/{user}', [\App\Http\Controllers\FollowController::class, 'unfollow'])->name('unfollow');
    });

// Review routes
Route::post('/notes/{note}/reviews', [ReviewController::class, 'store'])->middleware(['auth', 'verified', 'username.setup'])->name('reviews.store');
Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->middleware(['auth', 'verified', 'username.setup'])->name('reviews.update');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->middleware(['auth', 'verified', 'username.setup'])->name('reviews.destroy');
Route::post('/reviews/{review}/replies', [NoteReviewReplyController::class, 'store'])->middleware(['auth', 'verified', 'username.setup'])->name('reviews.replies.store');
Route::delete('/review-replies/{reply}', [NoteReviewReplyController::class, 'destroy'])->middleware(['auth', 'verified', 'username.setup'])->name('reviews.replies.destroy');

Route::get('/dashboard', function () {
    $user = auth()->user();
    
    // Workspace users should be redirected to workspaces
    if ($user->role === 'user_workspaces') {
        return redirect()->route('workspaces.index');
    }
    
    return view('dashboard');
})->middleware(['auth', 'verified', 'username.setup'])->name('dashboard');

// Setup username route (must be before username.setup middleware)
Route::middleware('auth')->prefix('setup-username')->name('setup-username.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SetupUsernameController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\SetupUsernameController::class, 'store'])->name('store');
});

Route::middleware(['auth', 'verified', 'username.setup', 'workspace.user'])->group(function () {
    // Notes routes - for sellers and workspace users
    // Check is done in NoteController and middleware
    Route::resource('notes', NoteController::class);
    Route::get('/notes/{note}/attachments/{filename}', [NoteAttachmentController::class, 'download'])->name('notes.attachments.download');
    
    // Batch Download routes - Premium features only
    Route::middleware('premium')->prefix('batch-download')->name('batch-download.')->group(function () {
        Route::get('/', [NoteAttachmentController::class, 'batchDownloadIndex'])->name('index');
        Route::post('/', [NoteAttachmentController::class, 'batchDownload'])->name('download');
    });

    // Folders (Premium feature - enhanced organization)
    Route::middleware('premium')->group(function () {
        Route::resource('folders', \App\Http\Controllers\FolderController::class);
        Route::post('/folders/update-order', [\App\Http\Controllers\FolderController::class, 'updateOrder'])->name('folders.update-order');
    });

    // Workspaces (Premium feature - multi workspace)
    // Workspace users can access workspaces even without premium
    Route::middleware(['auth', 'verified', 'username.setup'])->group(function () {
        // Check access in WorkspaceController
        Route::resource('workspaces', \App\Http\Controllers\WorkspaceController::class);
        Route::get('/workspaces/{workspace}/invite', [\App\Http\Controllers\WorkspaceController::class, 'invite'])->name('workspaces.invite');
        Route::post('/workspaces/{workspace}/invite', [\App\Http\Controllers\WorkspaceController::class, 'storeInvite'])->name('workspaces.invite.store');
        Route::delete('/workspaces/{workspace}/invite/{invitation}', [\App\Http\Controllers\WorkspaceController::class, 'cancelInvite'])->name('workspaces.invite.cancel');
        Route::post('/workspaces/{workspace}/sell', [\App\Http\Controllers\WorkspaceController::class, 'sell'])->name('workspaces.sell');
        Route::post('/workspaces/{workspace}/purchase', [\App\Http\Controllers\WorkspaceController::class, 'purchase'])->name('workspaces.purchase');
    });

    // AI routes - Premium features only (require subscription)
    Route::post('/ai/analyze', [AiController::class, 'analyze'])->name('ai.analyze');
    Route::post('/ai/generate-content', [AiController::class, 'generateContent'])->name('ai.generate-content');
    Route::post('/ai/search-images', [AiController::class, 'searchImages'])->name('ai.search-images');
    Route::post('/ai/generate-image', [AiController::class, 'generateImage'])->name('ai.generate-image');
    Route::post('/ai/generate-video', [AiController::class, 'generateVideo'])->name('ai.generate-video');
    Route::post('/ai/edit-video', [AiController::class, 'editVideo'])->name('ai.edit-video');
    Route::post('/ai/generate-ideas', [AiController::class, 'generateIdeas'])->name('ai.generate-ideas');
    Route::get('/ai/status', [AiController::class, 'status'])->name('ai.status');

    // Buyer AI routes - Premium features only (for purchased notes)
    // Rate limiting: 10 requests per minute per user
    Route::middleware(['premium', 'throttle.ai:10,1'])->prefix('buyer-ai')->name('buyer-ai.')->group(function () {
        Route::post('/analyze/{note}', [\App\Http\Controllers\BuyerAiController::class, 'analyzePurchasedNote'])->name('analyze');
        Route::post('/ask', [\App\Http\Controllers\BuyerAiController::class, 'askPurchasedNote'])->name('ask');
        Route::post('/study-materials/{note}', [\App\Http\Controllers\BuyerAiController::class, 'generateStudyMaterials'])->name('study-materials');
        Route::post('/compare', [\App\Http\Controllers\BuyerAiController::class, 'compareNotes'])->name('compare');
        Route::get('/recommendations', [\App\Http\Controllers\BuyerAiController::class, 'getRecommendations'])->name('recommendations');
        Route::post('/extract-content/{note}', [\App\Http\Controllers\BuyerAiController::class, 'extractContent'])->name('extract-content');
    });

    // Collections (Wishlist) routes - Premium features only
    Route::middleware('premium')->resource('collections', \App\Http\Controllers\CollectionController::class);
    Route::middleware('premium')->post('/collections/{collection}/add-note', [\App\Http\Controllers\CollectionController::class, 'addNote'])->name('collections.add-note');
    Route::middleware('premium')->delete('/collections/{collection}/remove-note/{note}', [\App\Http\Controllers\CollectionController::class, 'removeNote'])->name('collections.remove-note');

    // Export routes - Premium features only
    Route::middleware('premium')->prefix('export')->name('export.')->group(function () {
        Route::get('/note/{note}/pdf', [\App\Http\Controllers\ExportController::class, 'exportPdf'])->name('pdf');
        Route::get('/note/{note}/docx', [\App\Http\Controllers\ExportController::class, 'exportDocx'])->name('docx');
        Route::get('/note/{note}/markdown', [\App\Http\Controllers\ExportController::class, 'exportMarkdown'])->name('markdown');
    });

    // Reading Progress routes - Premium features only
    Route::middleware('premium')->prefix('reading-progress')->name('reading-progress.')->group(function () {
        Route::post('/note/{note}', [\App\Http\Controllers\ReadingProgressController::class, 'update'])->name('update');
        Route::get('/note/{note}', [\App\Http\Controllers\ReadingProgressController::class, 'show'])->name('show');
    });

    // Bookmarks routes - Premium features only
    Route::middleware('premium')->prefix('bookmarks')->name('bookmarks.')->group(function () {
        Route::get('/note/{note}', [\App\Http\Controllers\BookmarkController::class, 'index'])->name('index');
        Route::post('/note/{note}', [\App\Http\Controllers\BookmarkController::class, 'store'])->name('store');
        Route::put('/{bookmark}', [\App\Http\Controllers\BookmarkController::class, 'update'])->name('update');
        Route::delete('/{bookmark}', [\App\Http\Controllers\BookmarkController::class, 'destroy'])->name('destroy');
    });

    // Buyer Analytics routes - Premium features only
    Route::middleware('premium')->prefix('buyer-analytics')->name('buyer-analytics.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BuyerAnalyticsController::class, 'index'])->name('index');
        Route::get('/purchase-history', [\App\Http\Controllers\BuyerAnalyticsController::class, 'purchaseHistory'])->name('purchase-history');
    });

    // Reading History routes - Premium features only
    Route::middleware('premium')->prefix('reading-history')->name('reading-history.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReadingHistoryController::class, 'index'])->name('index');
    });

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

    // Featured Notes routes - only for sellers
    Route::middleware('seller')->group(function () {
        Route::get('/featured-notes', [\App\Http\Controllers\FeaturedNoteController::class, 'index'])->name('featured-notes.index');
        Route::get('/featured-notes/create', [\App\Http\Controllers\FeaturedNoteController::class, 'create'])->name('featured-notes.create');
        Route::post('/featured-notes', [\App\Http\Controllers\FeaturedNoteController::class, 'store'])->name('featured-notes.store');
        Route::get('/featured-notes/export', [\App\Http\Controllers\FeaturedNoteController::class, 'exportReport'])->name('featured-notes.export');
    });

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
Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin', 'username.setup'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/repurchase-report', [DashboardController::class, 'repurchaseReport'])->name('repurchase-report');
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/release', [UserController::class, 'release'])->name('users.release');
    Route::resource('commission-tiers', AdminCommissionTierController::class)->except(['show']);
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
    Route::post('/tax-rules', [AdminTaxRuleController::class, 'store'])->name('tax-rules.store');
    Route::put('/tax-rules/{taxRule}', [AdminTaxRuleController::class, 'update'])->name('tax-rules.update');
    Route::delete('/tax-rules/{taxRule}', [AdminTaxRuleController::class, 'destroy'])->name('tax-rules.destroy');
    Route::post('/price-rules', [AdminPriceRuleController::class, 'store'])->name('price-rules.store');
    Route::put('/price-rules/{tagSlug}', [AdminPriceRuleController::class, 'update'])->name('price-rules.update');
    Route::delete('/price-rules/{tagSlug}', [AdminPriceRuleController::class, 'destroy'])->name('price-rules.destroy');

    Route::get('/forum/moderation', [PostModerationController::class, 'index'])->name('forum.moderation.index');
    Route::get('/forum/moderation/{post}', [PostModerationController::class, 'show'])->name('forum.moderation.show');
    Route::post('/forum/moderation/{post}/hide', [PostModerationController::class, 'hide'])->name('forum.moderation.hide');
    Route::post('/forum/moderation/{post}/unhide', [PostModerationController::class, 'unhide'])->name('forum.moderation.unhide');
    Route::delete('/forum/moderation/{post}', [PostModerationController::class, 'destroy'])->name('forum.moderation.destroy');
    Route::post('/forum/moderation/report/{report}/status', [PostModerationController::class, 'updateReportStatus'])->name('forum.moderation.report.status');

    Route::get('/notes/moderation', [NoteModerationController::class, 'index'])->name('notes.moderation.index');
    Route::get('/notes/moderation/{note}', [NoteModerationController::class, 'show'])->name('notes.moderation.show');
    Route::post('/notes/moderation/{note}/suspend', [NoteModerationController::class, 'suspend'])->name('notes.moderation.suspend');
    Route::post('/notes/moderation/{note}/activate', [NoteModerationController::class, 'activate'])->name('notes.moderation.activate');
    Route::post('/notes/moderation/report/{report}/status', [NoteModerationController::class, 'updateReportStatus'])->name('notes.moderation.report.status');

    Route::get('/accounts/moderation', [AccountModerationController::class, 'index'])->name('accounts.moderation.index');
    Route::get('/accounts/moderation/{user}', [AccountModerationController::class, 'show'])->name('accounts.moderation.show');
    Route::post('/accounts/moderation/report/{report}/status', [AccountModerationController::class, 'updateReportStatus'])->name('accounts.moderation.report.status');

    // Featured Notes Admin routes
    Route::get('/featured-notes', [\App\Http\Controllers\Admin\FeaturedNoteController::class, 'index'])->name('featured-notes.index');
    Route::get('/featured-notes/ab-testing', [\App\Http\Controllers\Admin\FeaturedNoteController::class, 'abTesting'])->name('featured-notes.ab-testing');
    Route::get('/featured-notes/{featuredNote}', [\App\Http\Controllers\Admin\FeaturedNoteController::class, 'show'])->name('featured-notes.show');
    Route::post('/featured-notes/{featuredNote}/approve', [\App\Http\Controllers\Admin\FeaturedNoteController::class, 'approve'])->name('featured-notes.approve');
    Route::post('/featured-notes/{featuredNote}/reject', [\App\Http\Controllers\Admin\FeaturedNoteController::class, 'reject'])->name('featured-notes.reject');
});

// Public Documentation routes
Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');
Route::get('/docs/{category}', [DocumentationController::class, 'category'])->name('docs.category');
Route::get('/docs/{category}/{documentation:slug}', [DocumentationController::class, 'show'])->name('docs.show');
Route::post('/docs/{category}/{documentation:slug}/helpful', [DocumentationController::class, 'markHelpful'])->middleware(['auth', 'verified', 'username.setup'])->name('docs.helpful');

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

// Featured Notes Tracking API (no auth required for analytics)
Route::post('/api/featured-notes/{featuredNote}/click', [\App\Http\Controllers\FeaturedNoteController::class, 'trackClick'])
    ->name('api.featured-notes.click');
Route::post('/api/featured-notes/{featuredNote}/impression', [\App\Http\Controllers\FeaturedNoteController::class, 'trackImpression'])
    ->name('api.featured-notes.impression');

require __DIR__ . '/auth.php';
