<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
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
    Route::any('/calendar/{any?}', function () {
        return response()->json(['message' => 'Calendar feature has been removed'], 410);
    })->where('any', '.*')->name('calendar.index');
    Route::any('/api/calendar/{any?}', function () {
        return response()->json(['message' => 'Calendar feature has been removed'], 410);
    })->where('any', '.*')->name('calendar.api.removed');

    Route::get('/api/gamification/leaderboard', [App\Http\Controllers\GamificationController::class, 'leaderboard'])->name('gamification.leaderboard');
    Route::get('/api/gamification/me', [App\Http\Controllers\GamificationController::class, 'me'])->name('gamification.me');
    Route::get('/gamification', [App\Http\Controllers\GamificationController::class, 'overview'])->name('gamification.overview');

    // Scheduling
    Route::any('/api/scheduling/calendar', function () {
        return response()->json(['message' => 'Calendar feature has been removed'], 410);
    })->name('scheduling.calendar');
    Route::put('/api/scheduling/posts/{post}/schedule', [App\Http\Controllers\SchedulingController::class, 'updatePostSchedule'])->name('scheduling.posts.update');
    Route::post('/api/scheduling/bulk', [App\Http\Controllers\SchedulingController::class, 'bulk'])->name('scheduling.bulk');
    Route::get('/api/scheduling/{type}/{id}/recurrence', [App\Http\Controllers\SchedulingController::class, 'getRecurrence'])->name('scheduling.recurrence.get');
    Route::post('/api/scheduling/{type}/{id}/recurrence', [App\Http\Controllers\SchedulingController::class, 'saveRecurrence'])->name('scheduling.recurrence.save');
    Route::get('/scheduling/recurrence', function () {
        return Inertia::render('Scheduling/RecurrenceEditorPage');
    })->name('scheduling.recurrence.page');

    // User Analytics Dashboard - removed
    Route::any('/analytics', function () {
        return response()->json(['message' => 'Analytics feature has been removed'], 410);
    })->name('analytics.dashboard');
    Route::any('/api/analytics/overview', function () {
        return response()->json(['message' => 'Analytics feature has been removed'], 410);
    })->name('analytics.overview');
    Route::any('/api/analytics/competitors', function () {
        return response()->json(['message' => 'Analytics feature has been removed'], 410);
    })->name('analytics.competitors');
    Route::any('/api/analytics/export', function () {
        return response()->json(['message' => 'Analytics feature has been removed'], 410);
    })->name('analytics.export');

    // User Verification
    Route::get('/verification', [App\Http\Controllers\VerificationController::class, 'requestPage'])->name('verification.request');
    Route::post('/api/verification/requests', [App\Http\Controllers\VerificationController::class, 'submit'])->name('verification.submit');
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/verification', [App\Http\Controllers\Admin\VerificationAdminController::class, 'dashboard'])->name('admin.verification.dashboard');
        Route::post('/admin/verification/{verificationRequest}/approve', [App\Http\Controllers\Admin\VerificationAdminController::class, 'approve'])->name('admin.verification.approve');
        Route::post('/admin/verification/{verificationRequest}/reject', [App\Http\Controllers\Admin\VerificationAdminController::class, 'reject'])->name('admin.verification.reject');
    });

    // Recommendations
    Route::get('/api/recommendations/feed', [App\Http\Controllers\RecommendationController::class, 'feed'])->name('recommendations.feed');
    Route::get('/api/recommendations/posts/{post}/related', [App\Http\Controllers\RecommendationController::class, 'related'])->name('recommendations.related');
    Route::get('/api/recommendations/users/similar', [App\Http\Controllers\RecommendationController::class, 'similarUsers'])->name('recommendations.similar_users');
    Route::get('/api/recommendations/trending', [App\Http\Controllers\RecommendationController::class, 'trending'])->name('recommendations.trending');

    // Share Analytics
    Route::post('/api/share/{type}/{id}/track', [App\Http\Controllers\ShareAnalyticsController::class, 'track'])->name('share.track');

    // Live Streams
    Route::any('/streams/{any?}', function ($any = null) {
        return response()->json(['message' => 'Streams feature has been removed'], 410);
    })->where('any', '.*')->name('streams.index');
    Route::middleware('auth')->group(function () {
        Route::post('/streams', function () {
            return response()->json(['message' => 'Streams feature has been removed'], 410);
        })->name('streams.store');
        Route::post('/streams/{liveStream}/start', function ($liveStream) {
            return response()->json(['message' => 'Streams feature has been removed'], 410);
        })->name('streams.start');
        Route::post('/streams/{liveStream}/end', function ($liveStream) {
            return response()->json(['message' => 'Streams feature has been removed'], 410);
        })->name('streams.end');
        Route::post('/api/streams/{liveStream}/chat', function ($liveStream) {
            return response()->json(['message' => 'Streams feature has been removed'], 410);
        })->name('streams.chat.store');

        Route::get('/admin/streaming/providers', [App\Http\Controllers\Admin\StreamingProviderController::class, 'index'])->name('admin.streaming.providers.index');
        Route::post('/admin/streaming/providers', [App\Http\Controllers\Admin\StreamingProviderController::class, 'store'])->name('admin.streaming.providers.store');
        Route::put('/admin/streaming/providers/{streamingProvider}', [App\Http\Controllers\Admin\StreamingProviderController::class, 'update'])->name('admin.streaming.providers.update');
        Route::delete('/admin/streaming/providers/{streamingProvider}', [App\Http\Controllers\Admin\StreamingProviderController::class, 'destroy'])->name('admin.streaming.providers.destroy');
        Route::post('/api/users/{user}/block', [App\Http\Controllers\UserBlockController::class, 'block'])->name('user.block');
        Route::delete('/api/users/{user}/block', [App\Http\Controllers\UserBlockController::class, 'unblock'])->name('user.unblock');
        Route::get('/api/users/blocked', [App\Http\Controllers\UserBlockController::class, 'list'])->name('user.blocked.list');
    });

    // Admin Gamification
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/gamification', [App\Http\Controllers\Admin\GamificationAdminController::class, 'dashboard'])->name('admin.gamification.dashboard');
        Route::get('/admin/gamification/configs', [App\Http\Controllers\Admin\GamificationAdminController::class, 'listConfigs'])->name('admin.gamification.configs');
        Route::put('/admin/gamification/configs/{key}', [App\Http\Controllers\Admin\GamificationAdminController::class, 'updateConfig'])->name('admin.gamification.configs.update');
        Route::get('/admin/gamification/export', [App\Http\Controllers\Admin\GamificationAdminController::class, 'export'])->name('admin.gamification.export');
        Route::get('/admin/rate-limit/metrics', [App\Http\Controllers\Admin\RateLimitDashboardController::class, 'metrics'])->name('admin.rate-limit.metrics');
        Route::get('/admin/rate-limit', function () {
            return Inertia::render('Admin/RateLimitDashboard');
        })->name('admin.rate-limit.dashboard');
        // Plugin Management
        Route::get('/admin/plugins', [App\Http\Controllers\Admin\PluginController::class, 'index'])->name('admin.plugins.index');
        Route::post('/admin/plugins/upload', [App\Http\Controllers\Admin\PluginController::class, 'upload'])->name('admin.plugins.upload');
        Route::post('/admin/plugins/install', [App\Http\Controllers\Admin\PluginController::class, 'install'])->name('admin.plugins.install');
        Route::post('/admin/plugins/{plugin}/activate', [App\Http\Controllers\Admin\PluginController::class, 'activate'])->name('admin.plugins.activate');
        Route::post('/admin/plugins/{plugin}/deactivate', [App\Http\Controllers\Admin\PluginController::class, 'deactivate'])->name('admin.plugins.deactivate');
        Route::get('/admin/plugins/{plugin}', [App\Http\Controllers\Admin\PluginController::class, 'show'])->name('admin.plugins.show');
        Route::post('/admin/plugins/{plugin}/rollback', [App\Http\Controllers\Admin\PluginController::class, 'rollback'])->name('admin.plugins.rollback');
        Route::put('/admin/plugins/{plugin}/config', [App\Http\Controllers\Admin\PluginController::class, 'updateConfig'])->name('admin.plugins.config.update');
    });
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('search.index'); // 30 searches per minute
    Route::get('/search/suggestions', [App\Http\Controllers\SearchController::class, 'suggestions'])
        ->middleware('throttle:search')
        ->name('search.suggestions'); // 60 suggestions per minute
    Route::post('/search/saved', [App\Http\Controllers\SearchController::class, 'saved'])
        ->middleware('throttle:30,1')
        ->name('search.saved.create');
    Route::get('/search/saved', [App\Http\Controllers\SearchController::class, 'listSaved'])
        ->middleware('throttle:60,1')
        ->name('search.saved.list');
    Route::delete('/search/saved/{savedSearch}', [App\Http\Controllers\SearchController::class, 'deleteSaved'])
        ->middleware('throttle:60,1')
        ->name('search.saved.delete');
    Route::get('/search/history', [App\Http\Controllers\SearchController::class, 'history'])
        ->middleware('throttle:60,1')
        ->name('search.history');
    Route::delete('/search/history/{historyItem}', [App\Http\Controllers\SearchController::class, 'deleteHistoryItem'])
        ->middleware('throttle:60,1')
        ->name('search.history.delete');

    // Plugins catalog for users
    Route::get('/plugins', [App\Http\Controllers\PluginsController::class, 'index'])
        ->middleware(['auth'])
        ->name('plugins.index');

    // Events
    Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('events.index');
    Route::post('/events', [App\Http\Controllers\EventController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('events.store');
    Route::get('/events/{event}', [App\Http\Controllers\EventController::class, 'show'])
        ->where('event', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('events.show');
    Route::put('/events/{event}', [App\Http\Controllers\EventController::class, 'update'])
        ->where('event', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->middleware('throttle:30,1')
        ->name('events.update');
    Route::delete('/events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])
        ->where('event', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->middleware('throttle:30,1')
        ->name('events.destroy');
    Route::post('/events/{event}/invite', [App\Http\Controllers\EventController::class, 'invite'])
        ->middleware('throttle:60,1')
        ->name('events.invite');
    Route::post('/events/{event}/rsvp', [App\Http\Controllers\EventController::class, 'rsvp'])
        ->middleware('throttle:60,1')
        ->name('events.rsvp');
    Route::any('/events/calendar', function () {
        return response()->json(['message' => 'Calendar feature has been removed'], 410);
    })->middleware('throttle:60,1')->name('events.calendar');
    Route::get('/events/search', [App\Http\Controllers\EventController::class, 'search'])
        ->middleware('throttle:60,1')
        ->name('events.search');
});

// Public share
Route::get('/events/share/{token}', [App\Http\Controllers\EventController::class, 'share'])
    ->name('events.share');
// Link Preview API
Route::middleware('auth')->group(function () {
    Route::post('/api/link-preview', [App\Http\Controllers\LinkPreviewController::class, 'generate'])
        ->middleware('throttle:30,1')
        ->name('link-preview.generate'); // 30 requests per minute
});

// Image Upload API for Rich Text Editor
Route::middleware('auth')->group(function () {
    Route::post('/api/posts/upload-image', [App\Http\Controllers\PostController::class, 'uploadImage'])
        ->middleware('throttle:10,1')
        ->name('posts.upload-image'); // 10 uploads per minute
});

// Dashboard for analytics
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes - MUST be defined before /posts/{post} to prevent route model binding issues
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:10,60') // 10 updates per hour
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:3,1440') // 3 requests per 24 hours
        ->name('profile.destroy');
    // Profile show route with UUID constraint to prevent conflict with /posts/{post}
    Route::get('/profile/{user}', [ProfileController::class, 'show'])
        ->middleware(App\Http\Middleware\PreventBlockedProfileAccess::class)
        ->where('user', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('profile.show');
});

// Explore Trending
Route::middleware(['auth'])->group(function () {
    Route::get('/trending', [App\Http\Controllers\TrendingController::class, 'index'])->name('explore.trending');
    Route::get('/api/recommendations/feed', [App\Http\Controllers\RecommendationsController::class, 'feed'])
        ->name('recommendations.feed');
    Route::get('/api/recommendations/users/similar', [App\Http\Controllers\RecommendationsController::class, 'similarUsers'])
        ->name('recommendations.similar_users');
});
// Keep posts route for backward compatibility, but redirect to home for authenticated users
Route::get('/posts', [App\Http\Controllers\PostController::class, 'index'])->name('posts.index');
Route::get('/posts/trending', [App\Http\Controllers\PostController::class, 'trending'])->middleware(['auth'])->name('posts.trending');
Route::middleware(['auth'])->group(function () {
    Route::get('/stories', [App\Http\Controllers\StoryController::class, 'index'])->name('stories.index');
    Route::post('/stories', [App\Http\Controllers\StoryController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('stories.store');
    Route::get('/stories/{story}', [App\Http\Controllers\StoryController::class, 'show'])
        ->where('story', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('stories.show');
    Route::post('/stories/{story}/view', [App\Http\Controllers\StoryController::class, 'trackView'])
        ->middleware('throttle:60,1')
        ->name('stories.view');
    Route::post('/stories/{story}/react', [App\Http\Controllers\StoryController::class, 'react'])
        ->middleware('throttle:60,1')
        ->name('stories.react');
    Route::get('/stories/following', [App\Http\Controllers\StoryController::class, 'following'])
        ->middleware('throttle:60,1')
        ->name('stories.following');
    Route::post('/stories/highlights', [App\Http\Controllers\StoryController::class, 'createHighlight'])
        ->middleware('throttle:10,1')
        ->name('stories.highlights.create');
    Route::post('/stories/{story}/highlights/{highlight}', [App\Http\Controllers\StoryController::class, 'addToHighlight'])
        ->middleware('throttle:30,5')
        ->name('stories.highlights.add');
    Route::get('/stories/analytics', [App\Http\Controllers\StoryController::class, 'analytics'])
        ->name('stories.analytics');
    Route::get('/posts/create', [App\Http\Controllers\PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [App\Http\Controllers\PostController::class, 'store'])
        ->middleware('throttle:10,5') // 10 posts per 5 minutes (more reasonable limit)
        ->name('posts.store');
    Route::get('/posts/{post}', [App\Http\Controllers\PostController::class, 'show'])
        ->where('post', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('posts.show');
    Route::post('/posts/{post}/vote', [App\Http\Controllers\VoteController::class, 'votePost'])
        ->middleware('throttle:30,5') // 30 votes per 5 minutes
        ->name('votes.post');
    // Repost routes
    Route::post('/posts/{post}/repost', [App\Http\Controllers\RepostController::class, 'store'])
        ->middleware('throttle:30,5') // 30 reposts per 5 minutes
        ->name('posts.repost');
    Route::delete('/posts/{post}/repost', [App\Http\Controllers\RepostController::class, 'destroy'])
        ->middleware('throttle:30,5') // 30 unreposts per 5 minutes
        ->name('posts.unrepost');

    // Repost comment routes
    Route::put('/reposts/{repost}/comment', [App\Http\Controllers\RepostController::class, 'updateComment'])
        ->name('reposts.comment.update');
    Route::delete('/reposts/{repost}/comment', [App\Http\Controllers\RepostController::class, 'removeComment'])
        ->name('reposts.comment.remove');

    // Quote repost routes
    Route::post('/posts/{post}/quote-repost', [App\Http\Controllers\RepostController::class, 'storeQuote'])
        ->name('posts.quote-repost');
    Route::put('/reposts/{repost}/quote', [App\Http\Controllers\RepostController::class, 'updateQuote'])
        ->name('reposts.quote.update');
    Route::post('/reposts/{repost}/toggle-display', [App\Http\Controllers\RepostController::class, 'toggleDisplayMode'])
        ->name('reposts.toggle-display');

    // Repost analytics routes (author only)
    Route::get('/posts/{post}/reposts/analytics', [App\Http\Controllers\RepostAnalyticsController::class, 'show'])
        ->name('reposts.analytics');
    Route::get('/posts/{post}/reposts/breakdown', [App\Http\Controllers\RepostAnalyticsController::class, 'breakdown'])
        ->name('reposts.breakdown');
    Route::get('/posts/{post}/reposts/timeline', [App\Http\Controllers\RepostAnalyticsController::class, 'timeline'])
        ->name('reposts.timeline');
    Route::get('/posts/{post}/reposts/reposters', [App\Http\Controllers\RepostAnalyticsController::class, 'reposters'])
        ->name('reposts.reposters');
    Route::get('/posts/{post}/reposts/engagement', [App\Http\Controllers\RepostAnalyticsController::class, 'engagement'])
        ->name('reposts.engagement');
    Route::get('/posts/{post}/reposts/export', [App\Http\Controllers\RepostAnalyticsController::class, 'export'])
        ->name('reposts.export');
    Route::post('/posts/{post}/comments', [App\Http\Controllers\CommentController::class, 'store'])
        ->middleware('throttle:30,5') // 10 comments per 5 minutes
        ->name('comments.store');
    Route::post('/comments/{comment}/best-answer', [App\Http\Controllers\CommentController::class, 'markBestAnswer'])
        ->middleware('throttle:20,5') // 10 per 5 minutes
        ->name('comments.best-answer');

    // Comment media upload
    Route::post('/api/comments/upload-image', [App\Http\Controllers\CommentController::class, 'uploadImage'])
        ->middleware('throttle:10,1')
        ->name('comments.upload-image');

    // Comment editing
    Route::put('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'update'])
        ->middleware('throttle:30,5')
        ->name('comments.update');
    Route::get('/comments/{comment}/history', [App\Http\Controllers\CommentController::class, 'history'])
        ->middleware('auth')
        ->name('comments.history');

    // Comment pinning
    Route::post('/comments/{comment}/pin', [App\Http\Controllers\CommentController::class, 'pin'])
        ->middleware('throttle:20,5')
        ->name('comments.pin');
    Route::post('/comments/{comment}/unpin', [App\Http\Controllers\CommentController::class, 'unpin'])
        ->middleware('throttle:20,5')
        ->name('comments.unpin');

    // Comment deletion
    Route::delete('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])
        ->middleware('throttle:20,5')
        ->name('comments.destroy');

    // Comment reactions
    Route::post('/comments/{comment}/reactions', [App\Http\Controllers\CommentReactionController::class, 'react'])
        ->middleware('throttle:30,5')
        ->name('comments.reactions.react');
    Route::post('/comments/{comment}/vote', [App\Http\Controllers\VoteController::class, 'voteComment'])
        ->middleware('throttle:30,5') // 30 votes per 5 minutes
        ->name('votes.comment');

    // Vote Analytics routes (author only)
    Route::get('/posts/{post}/votes/analytics', [App\Http\Controllers\VoteAnalyticsController::class, 'showPostVotes'])
        ->name('votes.post.analytics');
    Route::get('/comments/{comment}/votes/analytics', [App\Http\Controllers\VoteAnalyticsController::class, 'showCommentVotes'])
        ->name('votes.comment.analytics');
    Route::get('/posts/{post}/votes/voters', [App\Http\Controllers\VoteAnalyticsController::class, 'getPostVoters'])
        ->name('votes.post.voters');
    Route::get('/comments/{comment}/votes/voters', [App\Http\Controllers\VoteAnalyticsController::class, 'getCommentVoters'])
        ->name('votes.comment.voters');

    Route::post('/posts/{post}/validate', [App\Http\Controllers\IdeaValidationController::class, 'store'])
        ->middleware('throttle:5,30') // 5 validations per 30 minutes
        ->name('idea-validations.store');

    // Post editing routes
    Route::get('/posts/{post}/edit', [App\Http\Controllers\PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [App\Http\Controllers\PostController::class, 'update'])
        ->middleware('throttle:10,5')
        ->name('posts.update');
    Route::get('/posts/{post}/history', [App\Http\Controllers\PostController::class, 'history'])->name('posts.history');

    // Post collaboration routes
    Route::post('/posts/{post}/collaborators/invite', [App\Http\Controllers\PostCollaborationController::class, 'invite'])
        ->middleware('throttle:10,5')
        ->name('posts.collaborators.invite');
    Route::post('/posts/collaborators/{collaboration}/accept', [App\Http\Controllers\PostCollaborationController::class, 'accept'])
        ->middleware('throttle:10,5')
        ->name('posts.collaborators.accept');
    Route::post('/posts/collaborators/{collaboration}/reject', [App\Http\Controllers\PostCollaborationController::class, 'reject'])
        ->middleware('throttle:10,5')
        ->name('posts.collaborators.reject');
    Route::delete('/posts/{post}/collaborators/{user}', [App\Http\Controllers\PostCollaborationController::class, 'remove'])
        ->middleware('throttle:10,5')
        ->name('posts.collaborators.remove');
    Route::put('/posts/collaborators/{collaboration}/permissions', [App\Http\Controllers\PostCollaborationController::class, 'updatePermissions'])
        ->middleware('throttle:10,5')
        ->name('posts.collaborators.update-permissions');

    // Draft routes
    Route::get('/drafts', [App\Http\Controllers\PostDraftController::class, 'index'])->name('drafts.index');
    Route::post('/drafts', [App\Http\Controllers\PostDraftController::class, 'store'])->name('drafts.store');
    Route::put('/drafts/{draft}', [App\Http\Controllers\PostDraftController::class, 'update'])->name('drafts.update');
    Route::delete('/drafts/{draft}', [App\Http\Controllers\PostDraftController::class, 'destroy'])->name('drafts.destroy');
    Route::post('/drafts/{draft}/publish', [App\Http\Controllers\PostDraftController::class, 'publish'])->name('drafts.publish');

    // Post Template routes
    Route::get('/post-templates', [App\Http\Controllers\PostTemplateController::class, 'index'])->name('post-templates.index');
    Route::post('/post-templates', [App\Http\Controllers\PostTemplateController::class, 'store'])->name('post-templates.store');
    Route::get('/post-templates/{postTemplate}', [App\Http\Controllers\PostTemplateController::class, 'show'])->name('post-templates.show');
    Route::put('/post-templates/{postTemplate}', [App\Http\Controllers\PostTemplateController::class, 'update'])->name('post-templates.update');
    Route::delete('/post-templates/{postTemplate}', [App\Http\Controllers\PostTemplateController::class, 'destroy'])->name('post-templates.destroy');

    // Poll routes
    Route::post('/posts/{post}/polls/{poll}/vote', [App\Http\Controllers\PollController::class, 'vote'])
        ->middleware('throttle:30,5')
        ->name('polls.vote');
    Route::get('/posts/{post}/polls/{poll}/results', [App\Http\Controllers\PollController::class, 'results'])
        ->name('polls.results');

    // Post Analytics routes
    Route::get('/posts/{post}/analytics', [App\Http\Controllers\PostAnalyticsController::class, 'show'])->name('posts.analytics');
    Route::get('/posts/{post}/analytics/export', [App\Http\Controllers\PostAnalyticsController::class, 'export'])->name('posts.analytics.export');

    // Post Pin routes
    Route::post('/posts/{post}/pin', [App\Http\Controllers\PostController::class, 'pin'])->name('posts.pin');
    Route::delete('/posts/{post}/pin', [App\Http\Controllers\PostController::class, 'unpin'])->name('posts.unpin');

    // Post Series routes
    Route::get('/posts/{post}/series', [App\Http\Controllers\PostController::class, 'series'])->name('posts.series');
    Route::post('/posts/{post}/series', [App\Http\Controllers\PostController::class, 'createSeries'])->name('posts.series.create');
    Route::put('/posts/{post}/series/order', [App\Http\Controllers\PostController::class, 'updateSeriesOrder'])->name('posts.series.order');
    Route::get('/posts/top', [App\Http\Controllers\PostController::class, 'top'])->name('posts.top');
});

// Hashtag routes (public)
Route::get('/hashtags/{hashtag}', [App\Http\Controllers\HashtagController::class, 'show'])->name('hashtags.show');

// Hashtag API routes
Route::middleware('auth')->group(function () {
    Route::get('/api/hashtags/suggestions', [App\Http\Controllers\HashtagController::class, 'suggestions'])
        ->middleware('throttle:60,1')
        ->name('hashtags.suggestions');
    Route::get('/api/user/preferences/trending-period', [App\Http\Controllers\UserPreferenceController::class, 'getTrendingPeriod'])->name('user.preferences.trending.get');
    Route::post('/api/user/preferences/trending-period', [App\Http\Controllers\UserPreferenceController::class, 'saveTrendingPeriod'])->name('user.preferences.trending.save');
});

Route::middleware('auth')->group(function () {
    // Messaging Voice Upload - Removed
    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])
        ->middleware('throttle:60,1')
        ->name('notifications.read'); // 60 actions per minute
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])
        ->middleware('throttle:10,1')
        ->name('notifications.read-all'); // 10 actions per minute
    Route::get('/docs/api', function () {
        return view('docs.api');
    })->name('docs.api');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'destroy'])
        ->middleware('throttle:60,1')
        ->name('notifications.destroy'); // 60 deletions per minute
    Route::post('/api/analytics/events', [App\Http\Controllers\AnalyticsController::class, 'store'])->middleware('throttle:analytics')->name('analytics.events.store');
    Route::get('/analytics/dashboard', [App\Http\Controllers\AnalyticsController::class, 'dashboard'])->name('analytics.events.dashboard');
    Route::get('/analytics/events/export', [App\Http\Controllers\AnalyticsController::class, 'export'])->middleware('block.viewer.export')->name('analytics.events.export');
    Route::get('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'readSigned'])
        ->middleware(['auth'])
        ->name('notifications.read.get');
    Route::get('/benchmarks', [App\Http\Controllers\BenchmarkController::class, 'index'])->name('benchmarks.index');
    Route::get('/api/benchmarks/top', [App\Http\Controllers\BenchmarkController::class, 'data'])->name('benchmarks.top.data');
    Route::get('/benchmarks/compare', fn() => Inertia::render('Benchmarks/Compare'))->name('benchmarks.compare');

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
    Route::post('/settings/playback', [App\Http\Controllers\SettingsController::class, 'updatePlayback'])
        ->middleware('throttle:10,60')
        ->name('settings.playback');

    // Secure conversation key API - Removed

    // Calls API - Removed
    // Route::middleware(['throttle:60,1'])->group(function () { ... });

    Route::get('/api/rtc/ice', [App\Http\Controllers\RtcController::class, 'ice'])
        ->middleware('throttle:20,1')
        ->name('rtc.ice');
    Route::post('/api/logs', [App\Http\Controllers\LogsController::class, 'store'])
        ->middleware('throttle:120,1')
        ->name('logs.store');
    Route::get('/api/logs', [App\Http\Controllers\LogsController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('logs.index');

    // SFU config & tokens
    Route::get('/api/rtc/sfu/config', [App\Http\Controllers\SfuController::class, 'config'])
        ->middleware('throttle:20,1')
        ->name('rtc.sfu.config');
    Route::post('/api/rtc/sfu/token', [App\Http\Controllers\SfuController::class, 'token'])
        ->middleware('throttle:60,1')
        ->name('rtc.sfu.token');
    Route::post('/api/rtc/sfu/record/start', [App\Http\Controllers\SfuController::class, 'recordingStart'])
        ->middleware('throttle:60,1')
        ->name('rtc.sfu.record.start');
    Route::post('/api/rtc/sfu/record/stop', [App\Http\Controllers\SfuController::class, 'recordingStop'])
        ->middleware('throttle:60,1')
        ->name('rtc.sfu.record.stop');
    Route::post('/settings/security', [App\Http\Controllers\SettingsController::class, 'updateSecurity'])
        ->middleware('throttle:10,60')
        ->name('settings.security'); // 10 updates per hour
    Route::get('/settings/activity-log', [App\Http\Controllers\SettingsController::class, 'activityLog'])
        ->name('settings.activity-log');
    Route::get('/settings/activity-log/export', [App\Http\Controllers\SettingsController::class, 'exportActivityLog'])
        ->middleware('throttle:5,60') // 5 exports per hour
        ->name('settings.activity-log.export');

    // Two-Factor Authentication
    Route::get('/settings/two-factor', [App\Http\Controllers\Settings\TwoFactorController::class, 'index'])
        ->name('settings.two-factor');
    Route::post('/settings/two-factor/enable', [App\Http\Controllers\Settings\TwoFactorController::class, 'enable'])
        ->middleware('throttle:5,60') // 5 attempts per hour
        ->name('settings.two-factor.enable');
    Route::post('/settings/two-factor/disable', [App\Http\Controllers\Settings\TwoFactorController::class, 'disable'])
        ->middleware('throttle:5,60') // 5 attempts per hour
        ->name('settings.two-factor.disable');
    Route::post('/settings/two-factor/recovery-codes', [App\Http\Controllers\Settings\TwoFactorController::class, 'regenerateRecoveryCodes'])
        ->middleware('throttle:3,60') // 3 regenerations per hour
        ->name('settings.two-factor.recovery-codes');

    // Follow System
    Route::post('/users/{user}/follow', [App\Http\Controllers\FollowController::class, 'follow'])
        ->middleware('throttle:20,5')
        ->name('users.follow'); // 20 actions per 5 minutes
    Route::delete('/users/{user}/unfollow', [App\Http\Controllers\FollowController::class, 'unfollow'])
        ->middleware('throttle:20,5')
        ->name('users.unfollow'); // 20 actions per 5 minutes
    Route::get('/users/{user}/followers', [App\Http\Controllers\FollowController::class, 'followers'])->name('users.followers');
    Route::get('/users/{user}/following', [App\Http\Controllers\FollowController::class, 'following'])->name('users.following');

    // User Categories
    Route::get('/user/categories', [App\Http\Controllers\UserCategoryController::class, 'index'])
        ->name('user.categories.index');
    Route::post('/user/categories', [App\Http\Controllers\UserCategoryController::class, 'store'])
        ->name('user.categories.store');
    Route::delete('/user/categories/{category}', [App\Http\Controllers\UserCategoryController::class, 'destroy'])
        ->name('user.categories.destroy');
    Route::post('/user/categories/sync', [App\Http\Controllers\UserCategoryController::class, 'sync'])
        ->name('user.categories.sync');
    Route::post('/user/categories/refresh', [App\Http\Controllers\UserCategoryController::class, 'refresh'])
        ->name('user.categories.refresh');

    // Follow Suggestions
    Route::get('/follow/suggestions', [App\Http\Controllers\FollowSuggestionController::class, 'index'])
        ->name('follow.suggestions');
    Route::post('/follow/suggestions/refresh', [App\Http\Controllers\FollowSuggestionController::class, 'refresh'])
        ->name('follow.suggestions.refresh');

    Route::get('/newsletter/subscribe', function () {
        return Inertia::render('Newsletter/Subscribe');
    })->name('newsletter.subscribe.page');
    Route::get('/admin/newsletter/subscribers', [App\Http\Controllers\NewsletterAdminController::class, 'subscribersPage'])
        ->middleware('admin')
        ->name('admin.newsletter.subscribers.page');
    Route::get('/admin/newsletter/editor', function () {
        return Inertia::render('Newsletter/TemplateEditor');
    })->middleware('admin')->name('admin.newsletter.editor.page');
    Route::get('/admin/newsletter/analytics', function () {
        return Inertia::render('Newsletter/AdminAnalytics');
    })->middleware('admin')->name('admin.newsletter.analytics.page');
    Route::get('/admin/newsletter/suppression', function () {
        return Inertia::render('Newsletter/SuppressionAdmin');
    })->middleware('admin')->name('admin.newsletter.suppression.page');
    Route::get('/admin/newsletter/segmentation', function () {
        return Inertia::render('Newsletter/Segmentation');
    })->middleware('admin')->name('admin.newsletter.segmentation.page');
    Route::get('/admin/newsletter/providers/status', function () {
        return Inertia::render('Newsletter/ProviderStatus');
    })->middleware('admin')->name('admin.newsletter.providers.status.page');
    Route::get('/admin/newsletter/clients', function () {
        return Inertia::render('Newsletter/AdminClients');
    })->middleware('admin')->name('admin.newsletter.clients.page');
    Route::get('/admin/newsletter/dashboard', function () {
        return Inertia::render('Newsletter/AdminDashboard');
    })->middleware('admin')->name('admin.newsletter.dashboard.page');
    Route::get('/admin/a11y/reports', function () {
        return Inertia::render('Admin/A11yReports');
    })->middleware('admin')->name('admin.a11y.reports.page');
    Route::get('/api/admin/newsletter/subscribers', [App\Http\Controllers\NewsletterAdminController::class, 'listSubscribers'])
        ->middleware('admin')
        ->name('admin.newsletter.subscribers.index');
    Route::post('/api/admin/newsletter/templates', [App\Http\Controllers\NewsletterAdminController::class, 'saveTemplate'])
        ->middleware('admin')
        ->name('admin.newsletter.templates.store');
    Route::get('/api/admin/newsletter/categories', [App\Http\Controllers\NewsletterAdminController::class, 'categories'])
        ->middleware('admin')
        ->name('admin.newsletter.categories.index');
    Route::post('/api/admin/newsletter/categories', [App\Http\Controllers\NewsletterAdminController::class, 'saveCategory'])
        ->middleware('admin')
        ->name('admin.newsletter.categories.store');
    Route::get('/api/admin/newsletter/clients', [App\Http\Controllers\NewsletterAdminController::class, 'clientsIndex'])
        ->middleware('admin')
        ->name('admin.newsletter.clients.index');
    Route::post('/api/admin/newsletter/clients', [App\Http\Controllers\NewsletterAdminController::class, 'clientsSave'])
        ->middleware('admin')
        ->name('admin.newsletter.clients.save');
    Route::post('/api/admin/newsletter/campaigns', [App\Http\Controllers\NewsletterAdminController::class, 'createCampaign'])
        ->middleware('admin')
        ->name('admin.newsletter.campaigns.store');
    Route::post('/api/admin/newsletter/campaigns/{campaign}/send', [App\Http\Controllers\NewsletterAdminController::class, 'sendCampaign'])
        ->middleware('admin')
        ->name('admin.newsletter.campaigns.send');
    Route::get('/api/admin/newsletter/suppression', [App\Http\Controllers\NewsletterAdminController::class, 'suppressionIndex'])
        ->middleware('admin')
        ->name('admin.newsletter.suppression.index');
    Route::post('/api/admin/newsletter/suppression', [App\Http\Controllers\NewsletterAdminController::class, 'suppressionStore'])
        ->middleware('admin')
        ->name('admin.newsletter.suppression.store');
    Route::delete('/api/admin/newsletter/suppression/{id}', [App\Http\Controllers\NewsletterAdminController::class, 'suppressionDelete'])
        ->middleware('admin')
        ->name('admin.newsletter.suppression.delete');
    Route::get('/api/admin/newsletter/analytics/overview', [App\Http\Controllers\NewsletterAnalyticsController::class, 'overview'])
        ->middleware('admin')
        ->name('admin.newsletter.analytics.overview');
    Route::get('/api/admin/newsletter/analytics/export/csv', [App\Http\Controllers\NewsletterAnalyticsController::class, 'exportCsv'])
        ->middleware('admin')
        ->name('admin.newsletter.analytics.export.csv');
    Route::get('/api/admin/newsletter/analytics/export/pdf', [App\Http\Controllers\NewsletterAnalyticsController::class, 'exportPdf'])
        ->middleware('admin')
        ->name('admin.newsletter.analytics.export.pdf');

    Route::get('/api/admin/newsletter/providers', [App\Http\Controllers\NewsletterProviderController::class, 'config'])
        ->middleware('admin')
        ->name('admin.newsletter.providers.index');
    Route::post('/api/admin/newsletter/providers', [App\Http\Controllers\NewsletterProviderController::class, 'save'])
        ->middleware('admin')
        ->name('admin.newsletter.providers.save');
    Route::get('/api/admin/newsletter/providers/test', [App\Http\Controllers\NewsletterProviderController::class, 'test'])
        ->middleware('admin')
        ->name('admin.newsletter.providers.test');
    Route::get('/api/admin/newsletter/providers/status', [App\Http\Controllers\NewsletterProviderController::class, 'status'])
        ->middleware('admin')
        ->name('admin.newsletter.providers.status');
    Route::get('/api/admin/newsletter/webhooks/logs', [App\Http\Controllers\NewsletterProviderController::class, 'logs'])
        ->middleware('admin')
        ->name('admin.newsletter.webhooks.logs');
    Route::post('/api/admin/newsletter/providers/resync', [App\Http\Controllers\NewsletterProviderController::class, 'resync'])
        ->middleware('admin')
        ->name('admin.newsletter.providers.resync');

    Route::middleware('auth')->group(function () {
        Route::get('/api/user/a11y/preferences', [App\Http\Controllers\AccessibilityPreferencesController::class, 'get'])->name('user.a11y.preferences.get');
        Route::post('/api/user/a11y/preferences', [App\Http\Controllers\AccessibilityPreferencesController::class, 'save'])->name('user.a11y.preferences.save');
        Route::get('/api/gdpr/export', [App\Http\Controllers\GDPRController::class, 'export'])->name('gdpr.export');
        Route::post('/api/gdpr/delete', [App\Http\Controllers\GDPRController::class, 'deleteAccount'])->name('gdpr.delete');
        Route::post('/api/gdpr/consent', [App\Http\Controllers\GDPRController::class, 'saveConsent'])->name('gdpr.consent');
        Route::get('/api/gdpr/consent', [App\Http\Controllers\GDPRController::class, 'getConsent'])->name('gdpr.consent.get');
    });
    Route::post('/api/a11y/report', [App\Http\Controllers\AccessibilityPreferencesController::class, 'report'])
        ->middleware('throttle:30,1')
        ->name('a11y.report');
    Route::get('/api/admin/a11y/reports', [App\Http\Controllers\AccessibilityPreferencesController::class, 'adminReports'])
        ->middleware('admin')
        ->name('admin.a11y.reports.index');
    Route::get('/api/admin/a11y/summary', [App\Http\Controllers\AccessibilityPreferencesController::class, 'adminSummary'])
        ->middleware('admin')
        ->name('admin.a11y.summary');
    Route::post('/api/admin/newsletter/segmentation/estimate', [App\Http\Controllers\NewsletterSegmentationController::class, 'estimate'])
        ->middleware('admin')
        ->name('admin.newsletter.segmentation.estimate');

    Route::post('/webhooks/sendgrid', [App\Http\Controllers\NewsletterProviderController::class, 'webhookSendgrid'])->name('webhooks.sendgrid');
    Route::post('/webhooks/mailgun', [App\Http\Controllers\NewsletterProviderController::class, 'webhookMailgun'])->name('webhooks.mailgun');
    Route::post('/webhooks/ses', [App\Http\Controllers\NewsletterProviderController::class, 'webhookSes'])->name('webhooks.ses');
    Route::post('/api/newsletter/subscribe', [App\Http\Controllers\NewsletterController::class, 'subscribe'])
        ->middleware('throttle:10,1')
        ->name('newsletter.subscribe');
    Route::get('/newsletter/confirm', [App\Http\Controllers\NewsletterController::class, 'confirm'])
        ->name('newsletter.confirm');
    Route::get('/newsletter/unsubscribe', [App\Http\Controllers\NewsletterController::class, 'unsubscribe'])
        ->name('newsletter.unsubscribe');
    Route::post('/api/newsletter/preferences', [App\Http\Controllers\NewsletterController::class, 'preferences'])
        ->name('newsletter.preferences');
    Route::get('/newsletter/pixel', [App\Http\Controllers\NewsletterController::class, 'pixel'])
        ->name('newsletter.pixel');
    Route::get('/newsletter/click', [App\Http\Controllers\NewsletterController::class, 'click'])
        ->name('newsletter.click');

    // Mutual Connections
    Route::get('/users/{user}/mutual-connections', [App\Http\Controllers\MutualConnectionController::class, 'index'])
        ->name('users.mutual-connections');

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

    // Bookmark Notes
    Route::put('/bookmarks/{bookmark}/notes', [App\Http\Controllers\BookmarkController::class, 'updateNotes'])
        ->name('bookmarks.notes.update');
    Route::get('/bookmarks/{bookmark}/notes', [App\Http\Controllers\BookmarkController::class, 'getNotes'])
        ->name('bookmarks.notes.show');

    // Bookmark Collections
    Route::get('/bookmarks/collections', [App\Http\Controllers\BookmarkCollectionController::class, 'index'])
        ->name('bookmarks.collections.index');
    Route::post('/bookmarks/collections', [App\Http\Controllers\BookmarkCollectionController::class, 'store'])
        ->name('bookmarks.collections.store');
    Route::put('/bookmarks/collections/{collection}', [App\Http\Controllers\BookmarkCollectionController::class, 'update'])
        ->name('bookmarks.collections.update');
    Route::delete('/bookmarks/collections/{collection}', [App\Http\Controllers\BookmarkCollectionController::class, 'destroy'])
        ->name('bookmarks.collections.destroy');
    Route::post('/bookmarks/collections/{collection}/reorder', [App\Http\Controllers\BookmarkCollectionController::class, 'reorder'])
        ->name('bookmarks.collections.reorder');
    Route::post('/bookmarks/collections/{collection}/move', [App\Http\Controllers\BookmarkCollectionController::class, 'move'])
        ->name('bookmarks.collections.move');

    // Collection Sharing
    Route::post('/bookmarks/collections/{collection}/toggle-public', [App\Http\Controllers\BookmarkCollectionShareController::class, 'togglePublic'])
        ->name('bookmarks.collections.toggle-public');
    Route::post('/bookmarks/collections/{collection}/generate-link', [App\Http\Controllers\BookmarkCollectionShareController::class, 'generatePublicLink'])
        ->name('bookmarks.collections.generate-link');
    Route::post('/bookmarks/collections/{collection}/invite', [App\Http\Controllers\BookmarkCollectionShareController::class, 'invite'])
        ->name('bookmarks.collections.invite');
    Route::post('/bookmarks/collections/{collection}/accept', [App\Http\Controllers\BookmarkCollectionShareController::class, 'accept'])
        ->name('bookmarks.collections.accept');
    Route::post('/bookmarks/collections/{collection}/reject', [App\Http\Controllers\BookmarkCollectionShareController::class, 'reject'])
        ->name('bookmarks.collections.reject');
    Route::delete('/bookmarks/collections/{collection}/revoke/{user}', [App\Http\Controllers\BookmarkCollectionShareController::class, 'revoke'])
        ->name('bookmarks.collections.revoke');
    Route::put('/bookmarks/collections/{collection}/permission/{user}', [App\Http\Controllers\BookmarkCollectionShareController::class, 'updatePermission'])
        ->name('bookmarks.collections.update-permission');

    // Shared Collections
    Route::get('/bookmarks/shared', [App\Http\Controllers\BookmarkCollectionShareController::class, 'sharedWithMe'])
        ->name('bookmarks.shared');

    // Bookmark Tags
    Route::get('/bookmarks/tags', [App\Http\Controllers\BookmarkTagController::class, 'index'])
        ->name('bookmarks.tags.index');
    Route::post('/bookmarks/tags', [App\Http\Controllers\BookmarkTagController::class, 'store'])
        ->name('bookmarks.tags.store');
    Route::put('/bookmarks/tags/{tag}', [App\Http\Controllers\BookmarkTagController::class, 'update'])
        ->name('bookmarks.tags.update');
    Route::delete('/bookmarks/tags/{tag}', [App\Http\Controllers\BookmarkTagController::class, 'destroy'])
        ->name('bookmarks.tags.destroy');
    Route::post('/bookmarks/tags/{tag}/toggle-global', [App\Http\Controllers\BookmarkTagController::class, 'toggleGlobal'])
        ->name('bookmarks.tags.toggle-global');
    Route::get('/bookmarks/tags/suggestions', [App\Http\Controllers\BookmarkTagController::class, 'suggestions'])
        ->name('bookmarks.tags.suggestions');
    Route::get('/bookmarks/tags/{tag}', [App\Http\Controllers\BookmarkTagController::class, 'show'])
        ->name('bookmarks.tags.show');
});

// Public Collections (no auth required)
Route::get('/bookmarks/public/{slug}', [App\Http\Controllers\BookmarkCollectionPublicController::class, 'show'])
    ->name('bookmarks.collections.public');
Route::get('/bookmarks/public', [App\Http\Controllers\BookmarkCollectionPublicController::class, 'index'])
    ->name('bookmarks.collections.public.index');

Route::middleware('auth')->group(function () {
    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/search-analytics', [App\Http\Controllers\Admin\SearchAnalyticsController::class, 'index'])->name('search.analytics');
        Route::get('/search-analytics/export', [App\Http\Controllers\Admin\SearchAnalyticsController::class, 'export'])->name('search.analytics.export');

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

        // User Management
        Route::resource('users', App\Http\Controllers\Admin\UserManagementController::class)->only(['index', 'show', 'edit', 'update']);
        Route::post('users/{user}/ban', [App\Http\Controllers\Admin\UserManagementController::class, 'ban'])->name('users.ban');
        Route::post('users/{user}/unban', [App\Http\Controllers\Admin\UserManagementController::class, 'unban'])->name('users.unban');

        // Reports Management
        Route::resource('reports', App\Http\Controllers\Admin\ReportController::class)->only(['index', 'show', 'update']);
        Route::post('reports/{report}/resolve', [App\Http\Controllers\Admin\ReportController::class, 'resolve'])->name('reports.resolve');
        Route::post('reports/{report}/dismiss', [App\Http\Controllers\Admin\ReportController::class, 'dismiss'])->name('reports.dismiss');

        // Post Moderation
        Route::resource('posts', App\Http\Controllers\Admin\AdminPostController::class)->only(['index', 'show']);
        Route::post('posts/{post}/moderate', [App\Http\Controllers\Admin\AdminPostController::class, 'moderate'])
            ->middleware('throttle:20,1')
            ->name('posts.moderate');
        Route::post('posts/{post}/restore', [App\Http\Controllers\Admin\AdminPostController::class, 'restore'])
            ->middleware('throttle:20,1')
            ->name('posts.restore');
        Route::post('posts/bulk-moderate', [App\Http\Controllers\Admin\AdminPostController::class, 'bulkModerate'])
            ->middleware('throttle:10,1')
            ->name('posts.bulk-moderate');

        // Support Tickets
        Route::resource('support-tickets', App\Http\Controllers\Admin\SupportTicketController::class)
            ->only(['index', 'show', 'destroy'])
            ->parameters(['support-tickets' => 'ticket']);
        Route::post('support-tickets/{ticket}/assign', [App\Http\Controllers\Admin\SupportTicketController::class, 'assign'])
            ->middleware('throttle:20,1')
            ->name('support-tickets.assign');
        Route::post('support-tickets/{ticket}/status', [App\Http\Controllers\Admin\SupportTicketController::class, 'updateStatus'])
            ->middleware('throttle:20,1')
            ->name('support-tickets.status');
        Route::post('support-tickets/{ticket}/priority', [App\Http\Controllers\Admin\SupportTicketController::class, 'updatePriority'])
            ->middleware('throttle:20,1')
            ->name('support-tickets.priority');
        Route::post('support-tickets/{ticket}/response', [App\Http\Controllers\Admin\SupportTicketController::class, 'addResponse'])
            ->middleware('throttle:20,1')
            ->name('support-tickets.response');
        // Redirect GET requests to response route back to ticket show page
        Route::get('support-tickets/{ticket}/response', function ($ticket) {
            return redirect()->route('admin.support-tickets.show', $ticket);
        });
    });
});

// Explorer Routes
Route::middleware('auth')->group(function () {
    Route::get('/explorer', [App\Http\Controllers\ExplorerController::class, 'index'])->name('explorer.index');
    Route::get('/explorer/livescore', [App\Http\Controllers\LiveScoreController::class, 'index'])->name('explorer.livescore');
    Route::get('/explorer/livescore/data', [App\Http\Controllers\LiveScoreController::class, 'getLiveScores'])->name('explorer.livescore.data');
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
Route::get('/documentation/theme-guide', function () {
    return Inertia::render('Documentations/ThemeGuide');
})->name('documentations.theme-guide');
Route::get('/documentation/accessibility-guide', function () {
    return Inertia::render('Documentations/AccessibilityGuide');
})->name('documentations.accessibility-guide');

// Support Tickets (User-facing)
Route::middleware('auth')->prefix('support')->name('support.')->group(function () {
    Route::get('/help-center', [App\Http\Controllers\Support\TicketController::class, 'helpCenter'])->name('help-center');
    Route::get('/knowledge-base/search', [App\Http\Controllers\Support\TicketController::class, 'searchKnowledgeBase'])
        ->middleware('throttle:30,1')
        ->name('knowledge-base.search');
    Route::resource('tickets', App\Http\Controllers\Support\TicketController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/tickets/{ticket}/response', [App\Http\Controllers\Support\TicketController::class, 'addResponse'])
        ->middleware('throttle:10,5')
        ->name('tickets.response');
    // Redirect GET requests to response route back to ticket show page
    Route::get('/tickets/{ticket}/response', function ($ticket) {
        return redirect()->route('support.tickets.show', $ticket);
    });
});

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

// Supplier routes
Route::middleware('auth')->group(function () {
    Route::get('/suppliers', [App\Http\Controllers\SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [App\Http\Controllers\SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [App\Http\Controllers\SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [App\Http\Controllers\SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [App\Http\Controllers\SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [App\Http\Controllers\SupplierController::class, 'update'])->name('suppliers.update');

    // Supplier reviews
    Route::post('/suppliers/{supplier}/reviews', [App\Http\Controllers\SupplierReviewController::class, 'store'])->name('suppliers.reviews.store');
    Route::put('/suppliers/{supplier}/reviews/{review}', [App\Http\Controllers\SupplierReviewController::class, 'update'])->name('suppliers.reviews.update');
    Route::delete('/suppliers/{supplier}/reviews/{review}', [App\Http\Controllers\SupplierReviewController::class, 'destroy'])->name('suppliers.reviews.destroy');
});

// API routes
Route::middleware('auth')->group(function () {
    Route::get('/api/supplier-recommendations', [App\Http\Controllers\SupplierRecommendationController::class, 'index'])->name('api.supplier-recommendations');
    Route::get('/api/suppliers/business-types', [App\Http\Controllers\SupplierController::class, 'businessTypes'])->name('api.suppliers.business-types');
});

// Messaging Routes deprecated: legacy redirect + gone
Route::middleware('auth')->prefix('messaging')->name('messaging.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('home');
    })->name('index');
    Route::any('/{any}', function () {
        abort(410, 'Messaging conversations feature has been removed');
    })->where('any', '.*');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/health.php';

// Groups / Communities
Route::middleware('auth')->prefix('groups')->name('groups.')->group(function () {
    Route::get('/', [App\Http\Controllers\Community\GroupController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\Community\GroupController::class, 'store'])->name('store');
    Route::get('/{slug}', [App\Http\Controllers\Community\GroupController::class, 'show'])->name('show');
    Route::post('/{slug}/join', [App\Http\Controllers\Community\GroupController::class, 'join'])->name('join');
    Route::post('/{slug}/leave', [App\Http\Controllers\Community\GroupController::class, 'leave'])->name('leave');
    Route::post('/{slug}/members/{member}/approve', [App\Http\Controllers\Community\GroupController::class, 'approve'])->name('members.approve');
    Route::put('/{slug}/members/{member}/role', [App\Http\Controllers\Community\GroupController::class, 'changeRole'])->name('members.role');

    Route::get('/{slug}/posts', [App\Http\Controllers\Community\GroupPostController::class, 'index'])->name('posts.index');
    Route::post('/{slug}/posts', [App\Http\Controllers\Community\GroupPostController::class, 'store'])->name('posts.store');
    Route::put('/{slug}/posts/{post}', [App\Http\Controllers\Community\GroupPostController::class, 'update'])->name('posts.update');
    Route::delete('/{slug}/posts/{post}', [App\Http\Controllers\Community\GroupPostController::class, 'destroy'])->name('posts.destroy');

    Route::get('/{slug}/invites', [App\Http\Controllers\Community\GroupInviteController::class, 'index'])->name('invites.index');
    Route::post('/{slug}/invites/email', [App\Http\Controllers\Community\GroupInviteController::class, 'createEmail'])->name('invites.email');
    Route::post('/{slug}/invites/link', [App\Http\Controllers\Community\GroupInviteController::class, 'createLink'])->name('invites.link');
    Route::get('/invites/{token}', [App\Http\Controllers\Community\GroupInviteController::class, 'show'])->name('invites.show');
    Route::post('/invites/{token}/accept', [App\Http\Controllers\Community\GroupInviteController::class, 'accept'])->name('invites.accept');
    Route::post('/invites/{token}/decline', [App\Http\Controllers\Community\GroupInviteController::class, 'decline'])->name('invites.decline');

    Route::get('/{slug}/events', [App\Http\Controllers\Community\GroupEventController::class, 'index'])->name('events.index');
    Route::post('/{slug}/events', [App\Http\Controllers\Community\GroupEventController::class, 'store'])->name('events.store');
    Route::get('/{slug}/events/{event}', [App\Http\Controllers\Community\GroupEventController::class, 'show'])
        ->where('event', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('events.show');
    Route::put('/{slug}/events/{event}', [App\Http\Controllers\Community\GroupEventController::class, 'update'])
        ->where('event', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('events.update');
    Route::delete('/{slug}/events/{event}', [App\Http\Controllers\Community\GroupEventController::class, 'destroy'])
        ->where('event', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('events.destroy');
    Route::post('/{slug}/events/{event}/rsvp', [App\Http\Controllers\Community\GroupEventController::class, 'rsvp'])->name('events.rsvp');
    Route::any('/{slug}/events/calendar', function ($slug) {
        return response()->json(['message' => 'Calendar feature has been removed'], 410);
    })->name('events.calendar');
    Route::get('/{slug}/analytics', [App\Http\Controllers\Community\GroupAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/{slug}/analytics/export/csv', [App\Http\Controllers\Community\GroupAnalyticsController::class, 'exportCsv'])->name('analytics.export.csv');
    Route::get('/{slug}/analytics/export/pdf', [App\Http\Controllers\Community\GroupAnalyticsController::class, 'exportPdf'])->name('analytics.export.pdf');
});

// Email tracking
Route::get('/email/open/{invite}', [App\Http\Controllers\EmailTrackingController::class, 'open'])->name('email.open');
Route::get('/email/click/{invite}', [App\Http\Controllers\EmailTrackingController::class, 'click'])->name('email.click');
