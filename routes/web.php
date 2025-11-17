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
Route::get('/ecosystem', [\App\Http\Controllers\EcosystemController::class, 'index'])->name('ecosystem.index');
Route::prefix('ecosystem')->name('ecosystem.')->group(function () {
    Route::get('/audio', [\App\Http\Controllers\EcosystemController::class, 'audio'])->name('audio');
    Route::get('/code', [\App\Http\Controllers\EcosystemController::class, 'code'])->name('code');
    Route::get('/graphics', [\App\Http\Controllers\EcosystemController::class, 'graphics'])->name('graphics');
    Route::get('/photos', [\App\Http\Controllers\EcosystemController::class, 'photos'])->name('photos');
    Route::get('/themes', [\App\Http\Controllers\EcosystemController::class, 'themes'])->name('themes');
    Route::get('/videos', [\App\Http\Controllers\EcosystemController::class, 'videos'])->name('videos');
    Route::get('/3d', [\App\Http\Controllers\EcosystemController::class, 'threeD'])->name('3d');
});
// Tutorial routes (public)
Route::get('/tuts', [\App\Http\Controllers\TutsController::class, 'index'])->name('tuts.index');
Route::get('/tuts/{tutorial:slug}', [\App\Http\Controllers\TutsController::class, 'show'])->name('tuts.show');
Route::get('/studio', [\App\Http\Controllers\StudioController::class, 'index'])->name('studio.index');
Route::middleware(['auth', 'verified', 'role:vendor|admin'])->get('/vendor', [\App\Http\Controllers\VendorController::class, 'index'])->name('vendor.index');
Route::middleware(['auth', 'verified', 'username.setup', 'kyc'])->prefix('studio')->name('studio.')->group(function () {
    Route::get('/orders', [\App\Http\Controllers\ServiceOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [\App\Http\Controllers\ServiceOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [\App\Http\Controllers\ServiceOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [\App\Http\Controllers\ServiceOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/fund-escrow', [\App\Http\Controllers\ServiceOrderController::class, 'fundEscrow'])->middleware('throttle:5,1')->name('orders.fund-escrow');
    Route::post('/orders/{order}/release-escrow', [\App\Http\Controllers\ServiceOrderController::class, 'releaseEscrow'])->middleware('throttle:5,1')->name('orders.release-escrow');
    Route::post('/orders/{order}/refund-escrow', [\App\Http\Controllers\ServiceOrderController::class, 'refundEscrow'])->middleware('throttle:5,1')->name('orders.refund-escrow');
    Route::post('/orders/{order}/assign-vendor', [\App\Http\Controllers\ServiceOrderController::class, 'assignVendor'])->middleware('role:admin')->name('orders.assign-vendor');
    Route::get('/orders/{order}/quotes/create', [\App\Http\Controllers\ServiceQuoteController::class, 'create'])->name('orders.quotes.create');
    Route::post('/orders/{order}/quotes', [\App\Http\Controllers\ServiceQuoteController::class, 'store'])->middleware('throttle:5,1')->name('orders.quotes.store');
    Route::post('/quotes/{quote}/accept', [\App\Http\Controllers\ServiceQuoteController::class, 'accept'])->middleware('throttle:8,1')->name('quotes.accept');
    Route::post('/quotes/{quote}/reject', [\App\Http\Controllers\ServiceQuoteController::class, 'reject'])->middleware('throttle:8,1')->name('quotes.reject');
});

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
Route::middleware(['auth', 'verified', 'username.setup', 'kyc'])->group(function () {
    Route::get('/notes/{note}/resale', [NoteController::class, 'resaleForm'])->name('notes.resale.form');
    Route::post('/notes/{note}/resale', [NoteController::class, 'resale'])->name('notes.resale');
});

    // Public profile routes
    Route::get('/u/{username}', [PublicProfileController::class, 'show'])->name('public.profile.show');

    // Forum routes - KYC required
    Route::middleware(['auth', 'verified', 'username.setup', 'kyc'])->prefix('forum')->name('forum.')->group(function () {
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

Route::middleware(['auth', 'verified', 'username.setup', 'kyc'])->group(function () {
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

    Route::middleware(['auth', 'verified', 'username.setup', 'kyc'])->prefix('follow')->name('follow.')->group(function () {
        Route::post('/{user}', [\App\Http\Controllers\FollowController::class, 'follow'])->name('follow');
        Route::delete('/{user}', [\App\Http\Controllers\FollowController::class, 'unfollow'])->name('unfollow');
    });

// Review routes - KYC required (marketplace actions need KYC)
Route::post('/notes/{note}/reviews', [ReviewController::class, 'store'])->middleware(['auth', 'verified', 'username.setup', 'kyc'])->name('reviews.store');
Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->middleware(['auth', 'verified', 'username.setup', 'kyc'])->name('reviews.update');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->middleware(['auth', 'verified', 'username.setup', 'kyc'])->name('reviews.destroy');
Route::post('/reviews/{review}/replies', [NoteReviewReplyController::class, 'store'])->middleware(['auth', 'verified', 'username.setup', 'kyc'])->name('reviews.replies.store');
Route::delete('/review-replies/{reply}', [NoteReviewReplyController::class, 'destroy'])->middleware(['auth', 'verified', 'username.setup', 'kyc'])->name('reviews.replies.destroy');

Route::get('/dashboard', function () {
    $user = auth()->user();
    
    // Check if user needs to complete profile (KTP and selfie) - redirect once after register
    // Check session to see if this is first time after register (admin tidak perlu verifikasi)
    if (!$user->hasRole('admin') && (!$user->ktp_path || !$user->selfie_path)) {
        // Only redirect if coming from registration (check session or recent registration)
        $justRegistered = session('just_registered', false);
        if ($justRegistered || ($user->created_at->gt(now()->subMinutes(5)) && !$user->ktp_path && !$user->selfie_path)) {
            session()->forget('just_registered');
            return redirect()->route('profile.edit')
                ->with('info', 'Silakan lengkapi profil Anda dengan mengupload KTP dan foto selfie untuk verifikasi identitas.');
        }
    }
    
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

Route::middleware(['auth', 'verified', 'username.setup', 'kyc', 'workspace.user'])->group(function () {
    // Notes routes - for sellers and workspace users
    // KYC required: KTP and selfie must be uploaded
    Route::resource('notes', NoteController::class);
    Route::post('/notes/upload-background', [NoteController::class, 'uploadBackground'])->name('notes.upload-background');
    Route::get('/notes/{note}/attachments/{filename}', [NoteAttachmentController::class, 'download'])->name('notes.attachments.download');
    
    // Batch Download routes - Now available for all users
    Route::prefix('batch-download')->name('batch-download.')->group(function () {
        Route::get('/', [NoteAttachmentController::class, 'batchDownloadIndex'])->name('index');
        Route::post('/', [NoteAttachmentController::class, 'batchDownload'])->name('download');
    });

    // Folders - Now available for all users
    Route::resource('folders', \App\Http\Controllers\FolderController::class);
    Route::post('/folders/update-order', [\App\Http\Controllers\FolderController::class, 'updateOrder'])->name('folders.update-order');

    // Workspaces - KYC required (nested group to avoid workspace.user middleware on other routes)
    Route::middleware(['auth', 'verified', 'username.setup', 'kyc'])->group(function () {
        Route::resource('workspaces', \App\Http\Controllers\WorkspaceController::class);
        Route::get('/workspaces/{workspace}/invite', [\App\Http\Controllers\WorkspaceController::class, 'invite'])->name('workspaces.invite');
        Route::post('/workspaces/{workspace}/invite', [\App\Http\Controllers\WorkspaceController::class, 'storeInvite'])->name('workspaces.invite.store');
        Route::delete('/workspaces/{workspace}/invite/{invitation}', [\App\Http\Controllers\WorkspaceController::class, 'cancelInvite'])->name('workspaces.invite.cancel');
        Route::post('/workspaces/{workspace}/sell', [\App\Http\Controllers\WorkspaceController::class, 'sell'])->name('workspaces.sell');
        Route::post('/workspaces/{workspace}/purchase', [\App\Http\Controllers\WorkspaceController::class, 'purchase'])->name('workspaces.purchase');

        // AI Features removed - all features are now free and accessible
    });
});

// Routes that require KYC but not workspace.user middleware
Route::middleware(['auth', 'verified', 'username.setup', 'kyc'])->group(function () {
    // Collections (Wishlist) routes - KYC required
    Route::resource('collections', \App\Http\Controllers\CollectionController::class);
    Route::post('/collections/{collection}/add-note', [\App\Http\Controllers\CollectionController::class, 'addNote'])->name('collections.add-note');
    Route::delete('/collections/{collection}/remove-note/{note}', [\App\Http\Controllers\CollectionController::class, 'removeNote'])->name('collections.remove-note');

    // Export routes - Now available for all users
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/note/{note}/pdf', [\App\Http\Controllers\ExportController::class, 'exportPdf'])->name('pdf');
        Route::get('/note/{note}/docx', [\App\Http\Controllers\ExportController::class, 'exportDocx'])->name('docx');
        Route::get('/note/{note}/markdown', [\App\Http\Controllers\ExportController::class, 'exportMarkdown'])->name('markdown');
    });

    // Reading Progress routes - Now available for all users
    Route::prefix('reading-progress')->name('reading-progress.')->group(function () {
        Route::post('/note/{note}', [\App\Http\Controllers\ReadingProgressController::class, 'update'])->name('update');
        Route::get('/note/{note}', [\App\Http\Controllers\ReadingProgressController::class, 'show'])->name('show');
    });

    // Bookmarks routes - Now available for all users
    Route::prefix('bookmarks')->name('bookmarks.')->group(function () {
        Route::get('/note/{note}', [\App\Http\Controllers\BookmarkController::class, 'index'])->name('index');
        Route::post('/note/{note}', [\App\Http\Controllers\BookmarkController::class, 'store'])->name('store');
        Route::put('/{bookmark}', [\App\Http\Controllers\BookmarkController::class, 'update'])->name('update');
        Route::delete('/{bookmark}', [\App\Http\Controllers\BookmarkController::class, 'destroy'])->name('destroy');
    });

    // Buyer Analytics routes - Now available for all users
    Route::prefix('buyer-analytics')->name('buyer-analytics.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BuyerAnalyticsController::class, 'index'])->name('index');
        Route::get('/purchase-history', [\App\Http\Controllers\BuyerAnalyticsController::class, 'purchaseHistory'])->name('purchase-history');
    });

    // Reading History routes - Now available for all users
    Route::prefix('reading-history')->name('reading-history.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReadingHistoryController::class, 'index'])->name('index');
    });

    // AI Features removed - Premium Note Features (Smart Search, Q&A, Insights) have been removed to reduce API costs

    // AI Features removed - all features are now free and accessible


    // Subscription routes - REMOVED: All users now have free access to all features
    // Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    // Route::get('/subscription/create', [SubscriptionController::class, 'create'])->name('subscription.create');
    // Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
    // Route::get('/subscription/{subscription}', [SubscriptionController::class, 'show'])->name('subscription.show');

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

    // Refund routes
    Route::get('/refunds', [\App\Http\Controllers\RefundController::class, 'index'])->name('refunds.index');
    Route::get('/transactions/{transaction}/refund/create', [\App\Http\Controllers\RefundController::class, 'create'])->name('refunds.create');
    Route::post('/transactions/{transaction}/refund', [\App\Http\Controllers\RefundController::class, 'store'])->name('refunds.store');
    Route::get('/refunds/{refund}', [\App\Http\Controllers\RefundController::class, 'show'])->name('refunds.show');

    // Bundle routes
    Route::get('/bundles', [\App\Http\Controllers\NoteBundleController::class, 'index'])->name('bundles.index');
    Route::get('/bundles/create', [\App\Http\Controllers\NoteBundleController::class, 'create'])->name('bundles.create');
    Route::post('/bundles', [\App\Http\Controllers\NoteBundleController::class, 'store'])->name('bundles.store');
    Route::get('/bundles/{bundle}', [\App\Http\Controllers\NoteBundleController::class, 'show'])->name('bundles.show');
    Route::post('/bundles/{bundle}/purchase', [\App\Http\Controllers\NoteBundleController::class, 'purchase'])->name('bundles.purchase');

    // Gift Note routes
    Route::get('/gifts', [\App\Http\Controllers\GiftNoteController::class, 'index'])->name('gifts.index');
    Route::get('/notes/{note}/gift/create', [\App\Http\Controllers\GiftNoteController::class, 'create'])->name('gifts.create');
    Route::post('/notes/{note}/gift', [\App\Http\Controllers\GiftNoteController::class, 'store'])->name('gifts.store');
    Route::get('/gifts/{giftNote}', [\App\Http\Controllers\GiftNoteController::class, 'show'])->name('gifts.show');
    Route::post('/gifts/{giftNote}/claim', [\App\Http\Controllers\GiftNoteController::class, 'claim'])->name('gifts.claim');

    // Note Comments routes
    Route::post('/notes/{note}/comments', [\App\Http\Controllers\NoteCommentController::class, 'store'])->name('notes.comments.store');
    Route::post('/comments/{comment}/reply', [\App\Http\Controllers\NoteCommentController::class, 'reply'])->name('comments.reply');
    Route::put('/comments/{comment}', [\App\Http\Controllers\NoteCommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [\App\Http\Controllers\NoteCommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/like', [\App\Http\Controllers\NoteCommentController::class, 'like'])->name('comments.like');

    // Note Reactions routes
    Route::post('/notes/{note}/reactions', [\App\Http\Controllers\NoteReactionController::class, 'store'])->name('notes.reactions.store');
    Route::delete('/notes/{note}/reactions/{reaction}', [\App\Http\Controllers\NoteReactionController::class, 'destroy'])->name('notes.reactions.destroy');
    Route::post('/notes/{note}/reactions/toggle', [\App\Http\Controllers\NoteReactionController::class, 'toggle'])->name('notes.reactions.toggle');

    // Note Q&A routes
    Route::post('/notes/{note}/questions', [\App\Http\Controllers\NoteQuestionController::class, 'store'])->name('notes.questions.store');
    // Handle GET requests (e.g., browser refresh after POST) by redirecting to marketplace
    Route::get('/notes/{note}/questions', function (App\Models\Note $note) {
        return redirect()->route('marketplace.show', $note)->withFragment('questions');
    })->name('notes.questions.index');
    Route::post('/questions/{question}/answer', [\App\Http\Controllers\NoteQuestionController::class, 'answer'])->name('questions.answer');
    Route::post('/questions/{question}/helpful', [\App\Http\Controllers\NoteQuestionController::class, 'markHelpful'])->name('questions.helpful');

    // Note Templates routes
    Route::get('/templates', [\App\Http\Controllers\NoteTemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/create', [\App\Http\Controllers\NoteTemplateController::class, 'create'])->name('templates.create');
    Route::post('/templates', [\App\Http\Controllers\NoteTemplateController::class, 'store'])->name('templates.store');
    Route::get('/templates/{template}', [\App\Http\Controllers\NoteTemplateController::class, 'show'])->name('templates.show');
    Route::post('/templates/{template}/use', [\App\Http\Controllers\NoteTemplateController::class, 'use'])->name('templates.use');

    // Note Series routes
    Route::get('/series', [\App\Http\Controllers\NoteSeriesController::class, 'index'])->name('series.index');
    Route::get('/series/create', [\App\Http\Controllers\NoteSeriesController::class, 'create'])->name('series.create');
    Route::post('/series', [\App\Http\Controllers\NoteSeriesController::class, 'store'])->name('series.store');
    Route::get('/series/{series}', [\App\Http\Controllers\NoteSeriesController::class, 'show'])->name('series.show');
    Route::put('/series/{series}', [\App\Http\Controllers\NoteSeriesController::class, 'update'])->name('series.update');
    Route::delete('/series/{series}', [\App\Http\Controllers\NoteSeriesController::class, 'destroy'])->name('series.destroy');

    // Categories routes
    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'show'])->name('categories.show');

    // Activity Feed routes
    Route::get('/activity', [\App\Http\Controllers\ActivityController::class, 'index'])->name('activity.index');
    Route::get('/activity/following', [\App\Http\Controllers\ActivityController::class, 'following'])->name('activity.following');

    // Messaging routes
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [\App\Http\Controllers\MessageController::class, 'conversation'])->name('messages.conversation');
    Route::post('/messages/{user}', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::post('/messages/{message}/read', [\App\Http\Controllers\MessageController::class, 'markAsRead'])->name('messages.read');

    // Recently Viewed Notes routes
    Route::get('/viewed-notes', [\App\Http\Controllers\NoteViewHistoryController::class, 'index'])->name('viewed-notes.index');

    // Webhook routes (for users to manage their webhooks) - KYC required
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WebhookController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\WebhookController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\WebhookController::class, 'store'])->name('store');
        Route::get('/{webhook}', [\App\Http\Controllers\WebhookController::class, 'show'])->name('show');
        Route::put('/{webhook}', [\App\Http\Controllers\WebhookController::class, 'update'])->name('update');
        Route::delete('/{webhook}', [\App\Http\Controllers\WebhookController::class, 'destroy'])->name('destroy');
        Route::post('/{webhook}/test', [\App\Http\Controllers\WebhookController::class, 'test'])->name('test');
    });

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

});

// Profile routes - No KYC required (user needs to access profile to upload KTP/selfie)
Route::middleware(['auth', 'verified', 'username.setup'])->group(function () {
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
    Route::get('/users/pending-verification', [UserController::class, 'pendingVerification'])->name('users.pending-verification');
    Route::post('/users/{user}/verify-approve', [UserController::class, 'approveVerification'])->name('users.verify.approve');
    Route::post('/users/{user}/verify-reject', [UserController::class, 'rejectVerification'])->name('users.verify.reject');
    Route::get('/users/{user}/download-doc/{type}', [UserController::class, 'downloadDocument'])->name('users.download-doc');
    Route::resource('commission-tiers', AdminCommissionTierController::class)->except(['show']);
    Route::resource('faqs', AdminFaqController::class);
    Route::resource('cms-pages', AdminCmsPageController::class);
    Route::resource('tutorials', \App\Http\Controllers\Admin\TutorialController::class);
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
    Route::post('/notes/{note}/approve-monetization', [AdminNoteController::class, 'approveMonetization'])->name('notes.approve-monetization');
    Route::post('/notes/{note}/reject-monetization', [AdminNoteController::class, 'rejectMonetization'])->name('notes.reject-monetization');
    Route::get('/workspaces', [\App\Http\Controllers\Admin\WorkspaceController::class, 'index'])->name('workspaces.index');
    Route::get('/workspaces/{workspace}', [\App\Http\Controllers\Admin\WorkspaceController::class, 'show'])->name('workspaces.show');
    Route::get('/view-history', [\App\Http\Controllers\Admin\ViewHistoryController::class, 'index'])->name('view-history.index');
    Route::get('/view-history/export', [\App\Http\Controllers\Admin\ViewHistoryController::class, 'export'])->name('view-history.export');
    Route::get('/view-history/{viewRevenue}', [\App\Http\Controllers\Admin\ViewHistoryController::class, 'show'])->name('view-history.show');
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
    Route::get('/system-health', [\App\Http\Controllers\Admin\SystemHealthController::class, 'index'])->name('system-health.index');
    Route::post('/system-health/test-broadcaster', [\App\Http\Controllers\Admin\SystemHealthController::class, 'testBroadcaster'])->name('system-health.test-broadcaster');
    Route::post('/tax-rules', [AdminTaxRuleController::class, 'store'])->name('tax-rules.store');
    Route::put('/tax-rules/{taxRule}', [AdminTaxRuleController::class, 'update'])->name('tax-rules.update');
    Route::delete('/tax-rules/{taxRule}', [AdminTaxRuleController::class, 'destroy'])->name('tax-rules.destroy');
    Route::post('/price-rules', [AdminPriceRuleController::class, 'store'])->name('price-rules.store');
    Route::put('/price-rules/{tagSlug}', [AdminPriceRuleController::class, 'update'])->name('price-rules.update');
    Route::delete('/price-rules/{tagSlug}', [AdminPriceRuleController::class, 'destroy'])->name('price-rules.destroy');

    // Vendors admin
    Route::get('/vendors', [\App\Http\Controllers\Admin\VendorController::class, 'index'])->name('vendors.index');
    Route::post('/vendors/assign', [\App\Http\Controllers\Admin\VendorController::class, 'assign'])->name('vendors.assign');
    Route::post('/vendors/bulk-assign', [\App\Http\Controllers\Admin\VendorController::class, 'bulkAssign'])->name('vendors.bulk-assign');

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

    // Admin Refund routes
    Route::get('/refunds', [\App\Http\Controllers\Admin\RefundController::class, 'index'])->name('refunds.index');
    Route::get('/refunds/{refund}', [\App\Http\Controllers\Admin\RefundController::class, 'show'])->name('refunds.show');
    Route::post('/refunds/{refund}/approve', [\App\Http\Controllers\Admin\RefundController::class, 'approve'])->name('refunds.approve');
    Route::post('/refunds/{refund}/reject', [\App\Http\Controllers\Admin\RefundController::class, 'reject'])->name('refunds.reject');

    // Admin User Verification routes
    Route::post('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::post('/users/{user}/unverify', [UserController::class, 'unverify'])->name('users.unverify');

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
