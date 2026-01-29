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
        ->middleware('throttle:90,5') // 30 votes per 5 minutes
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

    // Cross-post routes
    Route::post('/posts/{post}/cross-post', [App\Http\Controllers\PostController::class, 'crossPost'])->name('posts.cross-post');
    Route::get('/posts/{post}/cross-posts', [App\Http\Controllers\PostController::class, 'getCrossPosts'])->name('posts.cross-posts');
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
    // Messaging Voice Upload
    Route::post('/api/messaging/conversations/{conversation}/messages/voice', [App\Http\Controllers\Messaging\MessageController::class, 'storeVoice'])
        ->middleware('throttle:10,1')
        ->name('messaging.messages.voice.store');
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

    // Secure conversation key API (Sanctum/session)
    Route::middleware(['throttle:10,1'])->group(function () {
        Route::get('/api/secure/conversations/{conversation}/key', [App\Http\Controllers\Messaging\ConversationKeyController::class, 'fetch'])
            ->name('secure.conversations.key.fetch');
        Route::post('/api/secure/conversations/{conversation}/key/rotate', [App\Http\Controllers\Messaging\ConversationKeyController::class, 'rotate'])
            ->name('secure.conversations.key.rotate');
    });

    // Calls API
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::post('/api/messaging/conversations/{conversation}/calls/start', [App\Http\Controllers\Messaging\CallController::class, 'start'])
            ->name('calls.start');
        Route::post('/api/messaging/conversations/{conversation}/calls/{session}/join', [App\Http\Controllers\Messaging\CallController::class, 'join'])
            ->name('calls.join');
        Route::post('/api/messaging/conversations/{conversation}/calls/{session}/leave', [App\Http\Controllers\Messaging\CallController::class, 'leave'])
            ->name('calls.leave');
        Route::post('/api/messaging/conversations/{conversation}/calls/signal', [App\Http\Controllers\Messaging\CallController::class, 'signal'])
            ->name('calls.signal');
        Route::get('/api/messaging/conversations/{conversation}/calls/active', [App\Http\Controllers\Messaging\CallController::class, 'active'])
            ->name('calls.active');
        Route::post('/api/messaging/conversations/{conversation}/calls/{session}/mute-all', [App\Http\Controllers\Messaging\CallController::class, 'muteAll'])
            ->name('calls.mute_all');
        Route::post('/api/messaging/conversations/{conversation}/calls/{session}/kick/{userId}', [App\Http\Controllers\Messaging\CallController::class, 'kick'])
            ->name('calls.kick');
        Route::post('/api/messaging/conversations/{conversation}/calls/{session}/metrics', [App\Http\Controllers\Messaging\CallController::class, 'metrics'])
            ->name('calls.metrics');
        Route::get('/api/messaging/conversations/{conversation}/calls/{session}/metrics', [App\Http\Controllers\Messaging\CallController::class, 'listMetrics'])
            ->name('calls.metrics.list');
        Route::post('/api/messaging/conversations/{conversation}/calls/{session}/permissions/{userId}', [App\Http\Controllers\Messaging\CallController::class, 'setPermission'])
            ->name('calls.permissions');
        Route::post('/api/messaging/conversations/{conversation}/calls/{session}/recordings', [App\Http\Controllers\Messaging\CallController::class, 'uploadRecording'])
            ->name('calls.recordings');
    });

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

// Marketplace Routes
Route::get('/marketplace', [App\Http\Controllers\Marketplace\ProductController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/search', [App\Http\Controllers\Marketplace\SearchController::class, 'search'])->name('marketplace.search');

Route::middleware('auth')->group(function () {
    // Products
    Route::get('marketplace/products', [App\Http\Controllers\Marketplace\ProductController::class, 'index'])->name('marketplace.products.index');
    Route::get('marketplace/my-products', [App\Http\Controllers\Marketplace\ProductController::class, 'myProducts'])->name('marketplace.products.my-products');
    Route::get('marketplace/products/create', [App\Http\Controllers\Marketplace\ProductController::class, 'create'])->name('marketplace.products.create');
    Route::post('marketplace/products', [App\Http\Controllers\Marketplace\ProductController::class, 'store'])
        ->middleware('throttle:3,60') // 3 products per hour
        ->name('marketplace.products.store');
    Route::get('marketplace/products/{product}', [App\Http\Controllers\Marketplace\ProductController::class, 'show'])->name('marketplace.products.show');
    Route::post('marketplace/products/{product}/share', [App\Http\Controllers\Marketplace\ProductController::class, 'trackShare'])->name('marketplace.products.share');
    Route::get('marketplace/products/{product}/edit', [App\Http\Controllers\Marketplace\ProductController::class, 'edit'])->name('marketplace.products.edit');
    Route::put('marketplace/products/{product}', [App\Http\Controllers\Marketplace\ProductController::class, 'update'])
        ->middleware('throttle:10,60') // 10 updates per hour
        ->name('marketplace.products.update');
    Route::delete('marketplace/products/{product}', [App\Http\Controllers\Marketplace\ProductController::class, 'destroy'])->name('marketplace.products.destroy');

    // Orders
    Route::get('marketplace/orders', [App\Http\Controllers\Marketplace\OrderController::class, 'index'])->name('marketplace.orders.index');
    Route::get('marketplace/orders/{order}', [App\Http\Controllers\Marketplace\OrderController::class, 'show'])->name('marketplace.orders.show');
    Route::get('marketplace/orders/{order}/invoice', [App\Http\Controllers\Marketplace\OrderController::class, 'downloadInvoice'])
        ->middleware('throttle:20,5') // 20 downloads per 5 minutes
        ->name('marketplace.orders.invoice');
    Route::post('marketplace/orders/{order}/reorder', [App\Http\Controllers\Marketplace\OrderController::class, 'reorder'])
        ->middleware('throttle:10,60')
        ->name('marketplace.orders.reorder');
    Route::post('marketplace/orders', [App\Http\Controllers\Marketplace\OrderController::class, 'store'])
        ->middleware('throttle:10,1') // 10 orders per minute
        ->name('marketplace.orders.store');
    Route::post('/marketplace/orders/{order}/cancel', [App\Http\Controllers\Marketplace\OrderController::class, 'cancel'])
        ->middleware('throttle:5,60') // 5 cancellations per hour
        ->name('marketplace.orders.cancel');

    // Order tracking
    Route::get('/marketplace/orders/{order}/track', [App\Http\Controllers\Marketplace\OrderController::class, 'track'])
        ->middleware('throttle:30,1') // 30 requests per minute
        ->name('marketplace.orders.track');
    Route::get('/marketplace/orders/{order}/tracking', [App\Http\Controllers\Marketplace\OrderController::class, 'tracking'])
        ->middleware('throttle:30,1')
        ->name('marketplace.orders.tracking');
    Route::get('/marketplace/orders/{order}/tracking/poll', [App\Http\Controllers\Marketplace\OrderController::class, 'poll'])
        ->middleware('throttle:60,1') // 60 polls per minute (for real-time updates)
        ->name('marketplace.orders.tracking.poll');

    // Order modification
    Route::put('/marketplace/orders/{order}/modify', [App\Http\Controllers\Marketplace\OrderController::class, 'modify'])
        ->middleware(['throttle:10,60']) // 10 modifications per hour
        ->name('marketplace.orders.modify');

    // Bulk orders
    Route::post('/marketplace/orders/bulk', [App\Http\Controllers\Marketplace\OrderController::class, 'createBulkOrder'])
        ->middleware(['throttle:5,60']) // 5 bulk orders per hour
        ->name('marketplace.orders.bulk.create');

    // Order export
    Route::get('/marketplace/orders/export', [App\Http\Controllers\Marketplace\OrderController::class, 'exportHistory'])
        ->middleware(['throttle:10,60']) // 10 exports per hour
        ->name('marketplace.orders.export');

    // Cart
    Route::get('/marketplace/cart', [App\Http\Controllers\Marketplace\CartController::class, 'index'])->name('marketplace.cart');
    Route::post('/marketplace/cart', [App\Http\Controllers\Marketplace\CartController::class, 'store'])
        ->middleware('throttle:30,5') // 30 additions per 5 minutes
        ->name('marketplace.cart.store');
    Route::put('/marketplace/cart/{cartItem}', [App\Http\Controllers\Marketplace\CartController::class, 'update'])
        ->middleware('throttle:30,5') // 30 updates per 5 minutes
        ->name('marketplace.cart.update');
    Route::delete('/marketplace/cart/{cartItem}', [App\Http\Controllers\Marketplace\CartController::class, 'destroy'])
        ->middleware('throttle:30,5') // 30 deletions per 5 minutes
        ->name('marketplace.cart.destroy');
    Route::post('/marketplace/cart/checkout', [App\Http\Controllers\Marketplace\OrderController::class, 'storeFromCart'])
        ->middleware(['verified', 'throttle:10,1']) // 10 checkouts per minute
        ->name('marketplace.cart.checkout');

    // Purchases
    Route::get('/marketplace/purchases', [App\Http\Controllers\Marketplace\PurchasesController::class, 'index'])->name('marketplace.purchases.index');

    // Product Reviews
    Route::get('/marketplace/products/{product}/reviews', [App\Http\Controllers\Marketplace\ProductReviewController::class, 'index'])
        ->name('marketplace.products.reviews.index');
    Route::get('/marketplace/reviews/{productReview}', [App\Http\Controllers\Marketplace\ProductReviewController::class, 'show'])
        ->name('marketplace.reviews.show');
    Route::post('/marketplace/products/{product}/reviews', [App\Http\Controllers\Marketplace\ProductReviewController::class, 'store'])
        ->middleware('throttle:10,60') // 10 reviews per hour
        ->name('marketplace.products.reviews.store');
    Route::put('/marketplace/reviews/{productReview}', [App\Http\Controllers\Marketplace\ProductReviewController::class, 'update'])
        ->middleware('throttle:10,60')
        ->name('marketplace.reviews.update');
    Route::delete('/marketplace/reviews/{productReview}', [App\Http\Controllers\Marketplace\ProductReviewController::class, 'destroy'])
        ->middleware('throttle:10,60')
        ->name('marketplace.reviews.destroy');

    // Review voting
    Route::post('/marketplace/reviews/{productReview}/vote', [App\Http\Controllers\Marketplace\ProductReviewController::class, 'voteHelpful'])
        ->middleware(['auth', 'throttle:30,1']) // 30 votes per minute
        ->name('marketplace.reviews.vote');
    Route::delete('/marketplace/reviews/{productReview}/vote', [App\Http\Controllers\Marketplace\ProductReviewController::class, 'removeVote'])
        ->middleware(['auth', 'throttle:30,1'])
        ->name('marketplace.reviews.remove-vote');

    // Review media
    Route::post('/marketplace/reviews/{productReview}/media', [App\Http\Controllers\Marketplace\ProductReviewController::class, 'uploadMedia'])
        ->middleware(['auth', 'throttle:10,1']) // 10 uploads per minute
        ->name('marketplace.reviews.media.store');

    // Seller replies
    Route::post('/marketplace/reviews/{productReview}/reply', [App\Http\Controllers\Marketplace\ProductReviewReplyController::class, 'store'])
        ->middleware(['auth', 'throttle:10,60']) // 10 replies per hour
        ->name('marketplace.reviews.reply.store');
    Route::put('/marketplace/reviews/replies/{reply}', [App\Http\Controllers\Marketplace\ProductReviewReplyController::class, 'update'])
        ->middleware(['auth', 'throttle:10,60'])
        ->name('marketplace.reviews.reply.update');
    Route::delete('/marketplace/reviews/replies/{reply}', [App\Http\Controllers\Marketplace\ProductReviewReplyController::class, 'destroy'])
        ->middleware(['auth', 'throttle:10,60'])
        ->name('marketplace.reviews.reply.destroy');

    // Downloads
    Route::get('/marketplace/products/{product}/download', [App\Http\Controllers\Marketplace\DownloadController::class, 'download'])
        ->middleware('throttle:20,5') // 20 downloads per 5 minutes
        ->name('marketplace.products.download');

    // Product variants
    Route::get('/marketplace/products/{product}/variants', [App\Http\Controllers\Marketplace\ProductController::class, 'variants'])
        ->name('marketplace.products.variants');

    // Product bundles
    Route::post('/marketplace/products/bundles', [App\Http\Controllers\Marketplace\ProductController::class, 'storeBundle'])
        ->middleware('throttle:3,60') // 3 bundles per hour
        ->name('marketplace.products.bundles.store');

    // Product comparison
    Route::post('/marketplace/products/{product}/compare', [App\Http\Controllers\Marketplace\ProductController::class, 'addToComparison'])
        ->middleware('throttle:30,1')
        ->name('marketplace.products.compare.add');
    Route::delete('/marketplace/products/{product}/compare', [App\Http\Controllers\Marketplace\ProductController::class, 'removeFromComparison'])
        ->middleware('throttle:30,1')
        ->name('marketplace.products.compare.remove');
    Route::get('/marketplace/products/compare', [App\Http\Controllers\Marketplace\ProductController::class, 'getComparison'])
        ->name('marketplace.products.compare.index');
    Route::post('/marketplace/products/compare', [App\Http\Controllers\Marketplace\ProductController::class, 'compare'])
        ->name('marketplace.products.compare');

    // Waitlist
    Route::post('/marketplace/products/{product}/waitlist', [App\Http\Controllers\Marketplace\ProductWaitlistController::class, 'store'])
        ->middleware(['auth', 'throttle:10,60'])
        ->name('marketplace.products.waitlist.store');
    Route::delete('/marketplace/products/{product}/waitlist', [App\Http\Controllers\Marketplace\ProductWaitlistController::class, 'destroy'])
        ->middleware(['auth', 'throttle:10,60'])
        ->name('marketplace.products.waitlist.destroy');
    Route::get('/marketplace/waitlist', [App\Http\Controllers\Marketplace\ProductWaitlistController::class, 'index'])
        ->middleware('auth')
        ->name('marketplace.waitlist.index');

    // Coupons
    Route::post('/marketplace/coupons/validate', [App\Http\Controllers\Marketplace\CouponController::class, 'validate'])
        ->middleware(['auth', 'throttle:30,1'])
        ->name('marketplace.coupons.validate');
    Route::post('/marketplace/orders/{order}/coupon', [App\Http\Controllers\Marketplace\CouponController::class, 'apply'])
        ->middleware(['auth', 'throttle:10,60'])
        ->name('marketplace.orders.coupon.apply');

    // Subscriptions
    Route::get('/marketplace/subscriptions', [App\Http\Controllers\Marketplace\SubscriptionController::class, 'index'])
        ->middleware('auth')
        ->name('marketplace.subscriptions.index');
    Route::get('/marketplace/subscriptions/{subscription}', [App\Http\Controllers\Marketplace\SubscriptionController::class, 'show'])
        ->middleware('auth')
        ->name('marketplace.subscriptions.show');
    Route::post('/marketplace/subscriptions/{subscription}/cancel', [App\Http\Controllers\Marketplace\SubscriptionController::class, 'cancel'])
        ->middleware(['auth', 'throttle:5,60'])
        ->name('marketplace.subscriptions.cancel');
    Route::post('/marketplace/subscriptions/{subscription}/pause', [App\Http\Controllers\Marketplace\SubscriptionController::class, 'pause'])
        ->middleware(['auth', 'throttle:5,60'])
        ->name('marketplace.subscriptions.pause');
    Route::post('/marketplace/subscriptions/{subscription}/resume', [App\Http\Controllers\Marketplace\SubscriptionController::class, 'resume'])
        ->middleware(['auth', 'throttle:5,60'])
        ->name('marketplace.subscriptions.resume');

    // Wallet
    Route::get('marketplace/wallet', [App\Http\Controllers\Marketplace\WalletController::class, 'index'])->name('marketplace.wallet.index');
    Route::get('marketplace/wallet/transactions', [App\Http\Controllers\Marketplace\WalletController::class, 'transactions'])->name('marketplace.wallet.transactions');
    Route::get('marketplace/wallet/sales', [App\Http\Controllers\Marketplace\WalletController::class, 'sales'])->name('marketplace.wallet.sales');

    // Transaction Receipts
    Route::get('transactions/{transaction}/receipt', [App\Http\Controllers\Transactions\ReceiptController::class, 'downloadTransactionReceipt'])->name('transactions.receipt.download');
    Route::get('transactions/{transaction}/receipt/view', [App\Http\Controllers\Transactions\ReceiptController::class, 'viewTransactionReceipt'])->name('transactions.receipt.view');
    Route::get('transactions/{transaction}/timeline', [App\Http\Controllers\Transactions\ReceiptController::class, 'getTransactionTimeline'])->name('transactions.timeline');
    Route::get('transactions/export', [App\Http\Controllers\Transactions\ReceiptController::class, 'exportTransactions'])->name('transactions.export');

    // Order Receipts
    Route::get('marketplace/orders/{order}/receipt', [App\Http\Controllers\Transactions\ReceiptController::class, 'downloadOrderReceipt'])->name('marketplace.orders.receipt.download');
    Route::get('marketplace/orders/{order}/timeline', [App\Http\Controllers\Transactions\ReceiptController::class, 'getOrderTimeline'])->name('marketplace.orders.timeline');

    // Withdrawals
    Route::get('marketplace/withdrawals', [App\Http\Controllers\Marketplace\WithdrawalController::class, 'index'])->name('marketplace.withdrawals.index');
    Route::get('marketplace/withdrawals/create', [App\Http\Controllers\Marketplace\WithdrawalController::class, 'create'])->name('marketplace.withdrawals.create');
    Route::post('marketplace/withdrawals', [App\Http\Controllers\Marketplace\WithdrawalController::class, 'store'])
        ->middleware('throttle:3,1440') // 3 requests per 24 hours (1440 minutes)
        ->name('marketplace.withdrawals.store');
    Route::get('marketplace/withdrawals/{withdrawal}', [App\Http\Controllers\Marketplace\WithdrawalController::class, 'show'])->name('marketplace.withdrawals.show');

    // Sales Analytics
    Route::get('/marketplace/sales/analytics', [App\Http\Controllers\Marketplace\SalesAnalyticsController::class, 'index'])->name('marketplace.sales.analytics');

    // Seller Dashboard
    Route::prefix('marketplace/seller')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Marketplace\SellerDashboardController::class, 'index'])
            ->name('marketplace.seller.dashboard');
        Route::get('/dashboard/sales', [App\Http\Controllers\Marketplace\SellerDashboardController::class, 'sales'])
            ->name('marketplace.seller.dashboard.sales');
        Route::get('/dashboard/inventory', [App\Http\Controllers\Marketplace\SellerDashboardController::class, 'inventory'])
            ->name('marketplace.seller.dashboard.inventory');
        Route::get('/dashboard/performance', [App\Http\Controllers\Marketplace\SellerDashboardController::class, 'performance'])
            ->name('marketplace.seller.dashboard.performance');

        // Inventory Management
        Route::resource('inventory', App\Http\Controllers\Marketplace\InventoryManagementController::class)
            ->names('marketplace.seller.inventory');
        Route::get('/inventory/{product}/history', [App\Http\Controllers\Marketplace\InventoryManagementController::class, 'getStockHistory'])
            ->name('marketplace.seller.inventory.history');
        Route::put('/inventory/{product}/stock', [App\Http\Controllers\Marketplace\InventoryManagementController::class, 'updateStock'])
            ->name('marketplace.seller.inventory.stock.update');
        Route::post('/inventory/{product}/restock', [App\Http\Controllers\Marketplace\InventoryManagementController::class, 'restock'])
            ->name('marketplace.seller.inventory.restock');
        Route::put('/inventory/alert-settings', [App\Http\Controllers\Marketplace\InventoryManagementController::class, 'updateAlertSettings'])
            ->name('marketplace.seller.inventory.alert-settings');
        Route::get('/inventory/alerts/low-stock', [App\Http\Controllers\Marketplace\InventoryManagementController::class, 'getLowStockAlerts'])
            ->name('marketplace.seller.inventory.low-stock');

        // Dynamic Pricing
        Route::resource('pricing-rules', App\Http\Controllers\Marketplace\DynamicPricingController::class)
            ->names('marketplace.seller.pricing-rules');
        Route::put('/pricing-rules/{rule}/toggle', [App\Http\Controllers\Marketplace\DynamicPricingController::class, 'toggle'])
            ->name('marketplace.seller.pricing-rules.toggle');
        Route::get('/products/{product}/effective-price', [App\Http\Controllers\Marketplace\ProductController::class, 'getEffectivePrice'])
            ->name('marketplace.products.effective-price');
        Route::post('/products/{product}/pricing-preview', [App\Http\Controllers\Marketplace\DynamicPricingController::class, 'previewPrice'])
            ->name('marketplace.seller.pricing-preview');

        // Seller Verification
        Route::get('/verification', [App\Http\Controllers\Marketplace\SellerVerificationController::class, 'show'])
            ->name('marketplace.seller.verification');
        Route::post('/verification/apply', [App\Http\Controllers\Marketplace\SellerVerificationController::class, 'apply'])
            ->name('marketplace.seller.verification.apply');
        Route::get('/verification/status', [App\Http\Controllers\Marketplace\SellerVerificationController::class, 'status'])
            ->name('marketplace.seller.verification.status');

        // Seller Orders
        Route::get('/orders', [App\Http\Controllers\Marketplace\SellerOrderController::class, 'index'])->name('marketplace.seller.orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\Marketplace\SellerOrderController::class, 'show'])->name('marketplace.seller.orders.show');
        Route::put('/orders/{order}/status', [App\Http\Controllers\Marketplace\SellerOrderController::class, 'updateStatus'])
            ->middleware('throttle:10,60') // 10 updates per hour
            ->name('marketplace.seller.orders.update-status');
        Route::get('/orders/{order}/invoice', [App\Http\Controllers\Marketplace\SellerOrderController::class, 'downloadInvoice'])
            ->middleware('throttle:20,5') // 20 downloads per 5 minutes
            ->name('marketplace.seller.orders.invoice');
    });

    // Seller Ratings (buyers can rate sellers)
    Route::get('/sellers/{seller}/rating', [App\Http\Controllers\Marketplace\SellerRatingController::class, 'show'])
        ->name('marketplace.sellers.rating');
    Route::get('/orders/{order}/seller-rating/create', [App\Http\Controllers\Marketplace\SellerRatingController::class, 'create'])
        ->name('marketplace.seller-rating.create');
    Route::post('/orders/{order}/seller-rating', [App\Http\Controllers\Marketplace\SellerRatingController::class, 'store'])
        ->name('marketplace.seller-rating.store');
    Route::put('/seller-rating/{rating}', [App\Http\Controllers\Marketplace\SellerRatingController::class, 'update'])
        ->name('marketplace.seller-rating.update');

    // Clipper Routes
    Route::prefix('clipper')->name('clipper.')->middleware(['auth'])->group(function () {
        // Registration routes (accessible to all authenticated users)
        Route::get('brand-registration/create', [App\Http\Controllers\Clipper\BrandRegistrationController::class, 'create'])->name('brand-registration.create');
        Route::post('brand-registration', [App\Http\Controllers\Clipper\BrandRegistrationController::class, 'store'])
            ->middleware('throttle:3,60') // 3 registrations per hour
            ->name('brand-registration.store');
        Route::get('brand-registration', [App\Http\Controllers\Clipper\BrandRegistrationController::class, 'show'])->name('brand-registration.show');
        Route::get('brand-registration/edit', [App\Http\Controllers\Clipper\BrandRegistrationController::class, 'edit'])->name('brand-registration.edit');
        Route::put('brand-registration', [App\Http\Controllers\Clipper\BrandRegistrationController::class, 'update'])
            ->middleware('throttle:5,60') // 5 updates per hour
            ->name('brand-registration.update');

        Route::get('profile/create', [App\Http\Controllers\Clipper\ClipperProfileController::class, 'create'])->name('profile.create');
        Route::post('profile', [App\Http\Controllers\Clipper\ClipperProfileController::class, 'store'])
            ->middleware('throttle:3,60') // 3 profiles per hour
            ->name('profile.store');

        // Protected routes (require clipper or brand role)
        Route::middleware('clipper')->group(function () {
            // Dashboard
            Route::get('dashboard', [App\Http\Controllers\Clipper\ClipperDashboardController::class, 'index'])->name('dashboard');
            Route::get('/', function () {
                return redirect()->route('clipper.dashboard');
            });

            Route::get('profile', [App\Http\Controllers\Clipper\ClipperProfileController::class, 'show'])->name('profile.show');
            Route::get('profile/edit', [App\Http\Controllers\Clipper\ClipperProfileController::class, 'edit'])->name('profile.edit');
            Route::put('profile', [App\Http\Controllers\Clipper\ClipperProfileController::class, 'update'])
                ->middleware('throttle:5,60') // 5 updates per hour
                ->name('profile.update');

            // Top Up
            Route::get('top-ups', [App\Http\Controllers\Clipper\TopUpController::class, 'index'])->name('top-ups.index');
            Route::get('top-ups/create', [App\Http\Controllers\Clipper\TopUpController::class, 'create'])->name('top-ups.create');
            Route::post('top-ups', [App\Http\Controllers\Clipper\TopUpController::class, 'store'])
                ->middleware('throttle:5,60') // 5 requests per hour
                ->name('top-ups.store');
            Route::get('top-ups/{topUp}', [App\Http\Controllers\Clipper\TopUpController::class, 'show'])->name('top-ups.show');

            // Campaigns (Creator)
            Route::get('campaigns', [App\Http\Controllers\Clipper\CampaignController::class, 'index'])->name('campaigns.index');
            Route::get('campaigns/create', [App\Http\Controllers\Clipper\CampaignController::class, 'create'])->name('campaigns.create');
            Route::post('campaigns', [App\Http\Controllers\Clipper\CampaignController::class, 'store'])
                ->middleware('throttle:3,60') // 3 campaigns per hour
                ->name('campaigns.store');

            // Campaign Analytics (must be before campaigns/{campaign} to avoid route conflict)
            Route::get('campaigns/analytics', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'index'])->name('campaigns.analytics');

            // Available Campaigns (must be before campaigns/{campaign} to avoid route conflict)
            Route::get('campaigns/available', [App\Http\Controllers\Clipper\ClipController::class, 'availableCampaigns'])->name('campaigns.available');

            Route::get('campaigns/{campaign}', [App\Http\Controllers\Clipper\CampaignController::class, 'show'])->name('campaigns.show');
            Route::get('campaigns/{campaign}/edit', [App\Http\Controllers\Clipper\CampaignController::class, 'edit'])->name('campaigns.edit');
            Route::put('campaigns/{campaign}', [App\Http\Controllers\Clipper\CampaignController::class, 'update'])
                ->middleware('throttle:10,60') // 10 updates per hour
                ->name('campaigns.update');
            Route::delete('campaigns/{campaign}', [App\Http\Controllers\Clipper\CampaignController::class, 'destroy'])->name('campaigns.destroy');
            Route::post('campaigns/{campaign}/activate', [App\Http\Controllers\Clipper\CampaignController::class, 'activate'])->name('campaigns.activate');
            Route::post('campaigns/{campaign}/pause', [App\Http\Controllers\Clipper\CampaignController::class, 'pause'])->name('campaigns.pause');
            Route::post('campaigns/{campaign}/resume', [App\Http\Controllers\Clipper\CampaignController::class, 'resume'])->name('campaigns.resume');
            Route::post('campaigns/{campaign}/cancel', [App\Http\Controllers\Clipper\CampaignController::class, 'cancel'])->name('campaigns.cancel');
            Route::post('campaigns/{campaign}/complete', [App\Http\Controllers\Clipper\CampaignController::class, 'complete'])->name('campaigns.complete');
            Route::post('campaigns/{campaign}/share', [App\Http\Controllers\Clipper\CampaignController::class, 'shareAsPost'])->name('campaigns.share');
            Route::post('campaigns/{campaign}/clips/{clip}/approve', [App\Http\Controllers\Clipper\CampaignController::class, 'approveClip'])->name('campaigns.clips.approve');
            Route::post('campaigns/{campaign}/clips/{clip}/reject', [App\Http\Controllers\Clipper\CampaignController::class, 'rejectClip'])->name('campaigns.clips.reject');

            // Campaign Templates
            Route::resource('campaign-templates', App\Http\Controllers\Clipper\CampaignTemplateController::class)->names('clipper.campaign-templates');
            Route::post('campaign-templates/{campaignTemplate}/duplicate', [App\Http\Controllers\Clipper\CampaignTemplateController::class, 'duplicate'])->name('clipper.campaign-templates.duplicate');

            // Campaign Collaboration
            Route::post('campaigns/{campaign}/collaborators/invite', [App\Http\Controllers\Clipper\CampaignCollaborationController::class, 'invite'])->name('campaigns.collaborators.invite');
            Route::post('campaigns/collaborators/{collaboration}/accept', [App\Http\Controllers\Clipper\CampaignCollaborationController::class, 'accept'])->name('campaigns.collaborators.accept');
            Route::post('campaigns/collaborators/{collaboration}/reject', [App\Http\Controllers\Clipper\CampaignCollaborationController::class, 'reject'])->name('campaigns.collaborators.reject');
            Route::delete('campaigns/{campaign}/collaborators/{user}', [App\Http\Controllers\Clipper\CampaignCollaborationController::class, 'remove'])->name('campaigns.collaborators.remove');
            Route::put('campaigns/collaborators/{collaboration}/permissions', [App\Http\Controllers\Clipper\CampaignCollaborationController::class, 'updatePermissions'])->name('campaigns.collaborators.update-permissions');

            // Campaign A/B Testing
            Route::post('campaigns/{campaign}/ab-test/setup', [App\Http\Controllers\Clipper\CampaignController::class, 'setupABTest'])->name('campaigns.ab-test.setup');
            Route::get('campaigns/{campaign}/ab-test/results', [App\Http\Controllers\Clipper\CampaignController::class, 'getABTestResults'])->name('campaigns.ab-test.results');
            Route::post('campaigns/{campaign}/ab-test/select-winner', [App\Http\Controllers\Clipper\CampaignController::class, 'selectWinner'])->name('campaigns.ab-test.select-winner');

            // Clips (Clipper)
            Route::get('clips', [App\Http\Controllers\Clipper\ClipController::class, 'index'])->name('clips.index');
            Route::get('clips/create/{campaign}', [App\Http\Controllers\Clipper\ClipController::class, 'create'])->name('clips.create');
            Route::post('clips', [App\Http\Controllers\Clipper\ClipController::class, 'store'])
                ->middleware('throttle:10,5') // 10 clips per 5 minutes
                ->name('clips.store');
            Route::get('clips/{clip}', [App\Http\Controllers\Clipper\ClipController::class, 'show'])->name('clips.show');
            Route::get('clips/{clip}/status', [App\Http\Controllers\Clipper\ClipController::class, 'status'])->name('clips.status');
            Route::get('clips/{clip}/views/live', [App\Http\Controllers\Clipper\ClipController::class, 'getLiveViews'])->name('clips.views.live');
            Route::get('clips/{clip}/validation', [App\Http\Controllers\Clipper\ClipController::class, 'getValidationStatus'])->name('clips.validation');
            Route::get('clips/{clip}/validation/history', [App\Http\Controllers\Clipper\ClipController::class, 'getValidationStatus'])->name('clips.validation.history');
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

            // Campaign Analytics - Individual Campaign (Brand Dashboard)
            Route::get('campaigns/{campaign}/analytics', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'show'])->name('campaigns.analytics.show');
            Route::get('campaigns/{campaign}/analytics/views-chart', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'getViewsChart'])->name('campaigns.analytics.views-chart');
            Route::get('campaigns/{campaign}/analytics/roi', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'getROI'])->name('campaigns.analytics.roi');
            Route::get('campaigns/{campaign}/analytics/live', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'getLiveViews'])->name('campaigns.analytics.live');
            Route::get('campaigns/{campaign}/analytics/validation', [App\Http\Controllers\Clipper\CampaignAnalyticsController::class, 'getValidationDetails'])->name('campaigns.analytics.validation');

            // Clipper Withdrawals (require email verification for financial operations)
            Route::get('withdrawals', [App\Http\Controllers\Clipper\ClipperWithdrawalController::class, 'index'])->middleware('verified')->name('withdrawals.index');
            Route::get('withdrawals/create', [App\Http\Controllers\Clipper\ClipperWithdrawalController::class, 'create'])->middleware('verified')->name('withdrawals.create');
            Route::post('withdrawals', [App\Http\Controllers\Clipper\ClipperWithdrawalController::class, 'store'])
                ->middleware(['verified', 'throttle:3,1440']) // 3 requests per 24 hours (very strict, financial operation)
                ->name('withdrawals.store');

            // Creator Withdrawals (for brand/creator wallet - remaining budget withdrawal)
            // Must be defined before withdrawals/{withdrawal} to avoid route conflict
            Route::get('withdrawals/creator', [App\Http\Controllers\Clipper\CreatorWithdrawalController::class, 'index'])->middleware('verified')->name('withdrawals.creator.index');
            Route::get('withdrawals/creator/create', [App\Http\Controllers\Clipper\CreatorWithdrawalController::class, 'create'])->middleware('verified')->name('withdrawals.creator.create');
            Route::post('withdrawals/creator', [App\Http\Controllers\Clipper\CreatorWithdrawalController::class, 'store'])
                ->middleware(['verified', 'throttle:3,1440']) // 3 requests per 24 hours
                ->name('withdrawals.creator.store');
            Route::get('withdrawals/creator/{withdrawal}', [App\Http\Controllers\Clipper\CreatorWithdrawalController::class, 'show'])->middleware('verified')->name('withdrawals.creator.show');

            // Clipper withdrawal show - must be after creator routes
            Route::get('withdrawals/{withdrawal}', [App\Http\Controllers\Clipper\ClipperWithdrawalController::class, 'show'])->middleware('verified')->name('withdrawals.show');
        });
    });

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/search-analytics', [App\Http\Controllers\Admin\SearchAnalyticsController::class, 'index'])->name('search.analytics');
        Route::get('/search-analytics/export', [App\Http\Controllers\Admin\SearchAnalyticsController::class, 'export'])->name('search.analytics.export');
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

        // Refunds
        Route::get('refunds', [App\Http\Controllers\Admin\AdminRefundController::class, 'index'])->name('refunds.index');
        Route::get('refunds/create', [App\Http\Controllers\Admin\AdminRefundController::class, 'create'])->name('refunds.create');
        Route::post('refunds', [App\Http\Controllers\Admin\AdminRefundController::class, 'store'])
            ->middleware('throttle:10,1') // 10 requests per minute
            ->name('refunds.store');
        Route::get('refunds/{refund}', [App\Http\Controllers\Admin\AdminRefundController::class, 'show'])->name('refunds.show');

        // User Search API (for refund forms)
        Route::get('api/users/search', [App\Http\Controllers\Admin\AdminRefundController::class, 'searchUsers'])
            ->middleware('throttle:60,1')
            ->name('api.users.search'); // Route name will be prefixed with 'admin.' = 'admin.api.users.search'

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
        Route::post('campaigns/{campaign}/suspend', [App\Http\Controllers\Admin\AdminCampaignController::class, 'suspend'])->name('campaigns.suspend');
        Route::get('campaigns/{campaign}/analytics', [App\Http\Controllers\Admin\AdminCampaignController::class, 'viewAnalytics'])->name('campaigns.analytics');

        // Clips routes - fraud-alerts must come BEFORE resource route to avoid route conflict
        Route::get('clips/fraud-alerts', [App\Http\Controllers\Admin\AdminClipController::class, 'getFraudAlerts'])->name('clips.fraud-alerts');
        Route::resource('clips', App\Http\Controllers\Admin\AdminClipController::class)->only(['index', 'show']);
        Route::post('clips/{clip}/approve', [App\Http\Controllers\Admin\AdminClipController::class, 'approve'])->name('clips.approve');
        Route::post('clips/{clip}/reject', [App\Http\Controllers\Admin\AdminClipController::class, 'reject'])->name('clips.reject');
        Route::post('clips/{clip}/adjust-reward', [App\Http\Controllers\Admin\AdminClipController::class, 'adjustReward'])->name('clips.adjust-reward');
        Route::post('clips/{clip}/validate', [App\Http\Controllers\Admin\AdminClipController::class, 'manualValidate'])->name('clips.validate');
        Route::post('clips/{clip}/override-validation', [App\Http\Controllers\Admin\AdminClipController::class, 'overrideValidation'])->name('clips.override-validation');

        // Brand Approvals
        Route::get('brand-approvals', [App\Http\Controllers\Admin\AdminBrandApprovalController::class, 'index'])->name('brand-approvals.index');
        Route::post('brand-approvals/{registration}/approve', [App\Http\Controllers\Admin\AdminBrandApprovalController::class, 'approve'])->name('brand-approvals.approve');
        Route::post('brand-approvals/{registration}/reject', [App\Http\Controllers\Admin\AdminBrandApprovalController::class, 'reject'])->name('brand-approvals.reject');
        // Redirect GET requests to approve/reject routes back to show page (must be before show route)
        Route::get('brand-approvals/{registration}/approve', function ($registration) {
            return redirect()->route('admin.brand-approvals.show', $registration);
        });
        Route::get('brand-approvals/{registration}/reject', function ($registration) {
            return redirect()->route('admin.brand-approvals.show', $registration);
        });
        Route::get('brand-approvals/{registration}', [App\Http\Controllers\Admin\AdminBrandApprovalController::class, 'show'])->name('brand-approvals.show');

        // Clipper Approvals
        Route::get('clipper-approvals', [App\Http\Controllers\Admin\ClipperApprovalController::class, 'index'])->name('clipper-approvals.index');
        Route::post('clipper-approvals/{registration}/approve', [App\Http\Controllers\Admin\ClipperApprovalController::class, 'approve'])->name('clipper-approvals.approve');
        Route::post('clipper-approvals/{registration}/reject', [App\Http\Controllers\Admin\ClipperApprovalController::class, 'reject'])->name('clipper-approvals.reject');
        // Redirect GET requests to approve/reject routes back to show page (must be before show route)
        Route::get('clipper-approvals/{registration}/approve', function ($registration) {
            return redirect()->route('admin.clipper-approvals.show', $registration);
        });
        Route::get('clipper-approvals/{registration}/reject', function ($registration) {
            return redirect()->route('admin.clipper-approvals.show', $registration);
        });
        Route::get('clipper-approvals/{registration}', [App\Http\Controllers\Admin\ClipperApprovalController::class, 'show'])->name('clipper-approvals.show');

        Route::prefix('wallets')->name('wallets.')->group(function () {
            Route::get('ledger', [App\Http\Controllers\Admin\AdminWalletController::class, 'viewLedger'])->name('ledger');
            Route::get('audit-log', [App\Http\Controllers\Admin\AdminWalletController::class, 'viewAuditLog'])->name('audit-log');
        });

        // Marketplace Settings
        Route::get('marketplace/settings', [App\Http\Controllers\Admin\AdminMarketplaceSettingsController::class, 'index'])
            ->name('marketplace.settings');
        Route::put('marketplace/settings', [App\Http\Controllers\Admin\AdminMarketplaceSettingsController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('marketplace.settings.update');

        // Clipper Settings
        Route::get('clipper/settings', [App\Http\Controllers\Admin\AdminClipperSettingsController::class, 'index'])
            ->name('clipper.settings');
        Route::put('clipper/settings', [App\Http\Controllers\Admin\AdminClipperSettingsController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('clipper.settings.update');

        // User Management
        Route::resource('users', App\Http\Controllers\Admin\UserManagementController::class)->only(['index', 'show', 'edit', 'update']);
        Route::post('users/{user}/ban', [App\Http\Controllers\Admin\UserManagementController::class, 'ban'])->name('users.ban');
        Route::post('users/{user}/unban', [App\Http\Controllers\Admin\UserManagementController::class, 'unban'])->name('users.unban');
        Route::post('users/{user}/remove-clipper-role', [App\Http\Controllers\Admin\UserManagementController::class, 'removeClipperRole'])
            ->middleware('throttle:10,60') // 10 actions per hour
            ->name('users.remove-clipper-role');
        Route::post('users/{user}/grant-clipper-role', [App\Http\Controllers\Admin\UserManagementController::class, 'grantClipperRole'])
            ->middleware('throttle:10,60')
            ->name('users.grant-clipper-role');
        Route::post('users/{user}/grant-brand-role', [App\Http\Controllers\Admin\UserManagementController::class, 'grantBrandRole'])
            ->middleware('throttle:10,60')
            ->name('users.grant-brand-role');

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

        // Seller Verifications
        Route::resource('seller-verifications', App\Http\Controllers\Admin\AdminSellerVerificationController::class)
            ->only(['index', 'show'])
            ->names('seller-verifications');
        Route::post('seller-verifications/{verification}/approve', [App\Http\Controllers\Admin\AdminSellerVerificationController::class, 'approve'])
            ->middleware('throttle:20,1')
            ->name('seller-verifications.approve');
        Route::post('seller-verifications/{verification}/reject', [App\Http\Controllers\Admin\AdminSellerVerificationController::class, 'reject'])
            ->middleware('throttle:20,1')
            ->name('seller-verifications.reject');
        Route::post('sellers/{seller}/verification/revoke', [App\Http\Controllers\Admin\AdminSellerVerificationController::class, 'revoke'])
            ->middleware('throttle:20,1')
            ->name('sellers.verification.revoke');
    });
});

// Payment Webhooks (no auth required, CSRF excluded)
// Single webhook endpoint handles both marketplace orders and top-ups
Route::post('/payment/webhook', [App\Http\Controllers\PaymentController::class, 'webhook'])
    ->middleware('verify.midtrans')
    ->name('payment.webhook');
Route::post('/payment/recurring', [App\Http\Controllers\PaymentController::class, 'recurring'])
    ->middleware('verify.midtrans')
    ->name('payment.recurring');
Route::post('/payment/pay-account', [App\Http\Controllers\PaymentController::class, 'payAccount'])
    ->middleware('verify.midtrans')
    ->name('payment.pay-account');

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

// Stock data
Route::get('/stocks/dashboard', [App\Http\Controllers\StockController::class, 'dashboard'])->middleware('auth')->name('stocks.dashboard');
Route::get('/stocks', [App\Http\Controllers\StockController::class, 'index'])->name('stocks.index');

// Stock screening - MUST be before /stocks/{stock} route to avoid route conflict
Route::get('/stocks/screening', [App\Http\Controllers\StockScreeningController::class, 'show'])->middleware('auth')->name('stocks.screening');

// Watchlist - MUST be before /stocks/{stock} route to avoid route conflict
Route::get('/stocks/watchlist', [App\Http\Controllers\StockWatchlistController::class, 'index'])
    ->middleware('auth')
    ->name('stocks.watchlist.index');
Route::post('/stocks/watchlist', [App\Http\Controllers\StockWatchlistController::class, 'store'])
    ->middleware('auth')
    ->name('stocks.watchlist.store');
Route::put('/stocks/watchlist/{watchlist}', [App\Http\Controllers\StockWatchlistController::class, 'update'])
    ->middleware('auth')
    ->name('stocks.watchlist.update');
Route::delete('/stocks/watchlist/{watchlist}', [App\Http\Controllers\StockWatchlistController::class, 'destroy'])
    ->middleware('auth')
    ->name('stocks.watchlist.destroy');

// Stock detail routes - must be after specific routes
Route::get('/stocks/{stock}', [App\Http\Controllers\StockController::class, 'show'])->name('stocks.show');
Route::get('/stocks/{stock}/prices', [App\Http\Controllers\StockController::class, 'prices'])->name('stocks.prices');
Route::get('/stocks/{stock}/indicators', [App\Http\Controllers\StockController::class, 'indicators'])->name('stocks.indicators');
Route::get('/stocks/{stock}/predictions', [App\Http\Controllers\StockController::class, 'predictions'])
    ->middleware('auth')
    ->name('stocks.predictions');
Route::get('/stocks/{stock}/signals', [App\Http\Controllers\StockController::class, 'signals'])->name('stocks.signals');

// Stock screening API routes
Route::post('/stocks/screen', [App\Http\Controllers\StockScreeningController::class, 'screen'])
    ->middleware('throttle:30,1') // 30 screenings per minute
    ->name('stocks.screen');
Route::post('/stocks/screenings', [App\Http\Controllers\StockScreeningController::class, 'save'])
    ->middleware(['auth', 'throttle:10,1']) // 10 saves per minute
    ->name('stocks.screenings.save');
Route::get('/stocks/screenings', [App\Http\Controllers\StockScreeningController::class, 'saved'])
    ->middleware(['auth', 'throttle:60,1']) // 60 requests per minute
    ->name('stocks.screenings.index');
Route::get('/stocks/screenings/{screening}', [App\Http\Controllers\StockScreeningController::class, 'results'])
    ->middleware(['auth', 'throttle:60,1']) // 60 requests per minute
    ->name('stocks.screenings.results');
Route::delete('/stocks/screenings/{screening}', [App\Http\Controllers\StockScreeningController::class, 'delete'])
    ->middleware(['auth', 'throttle:10,1']) // 10 deletes per minute
    ->name('stocks.screenings.delete');

// Portfolio recommendations (premium)
Route::get('/portfolio/generate', [App\Http\Controllers\PortfolioRecommendationController::class, 'create'])
    ->middleware('auth')
    ->name('portfolio.generate');
Route::post('/portfolio/generate', [App\Http\Controllers\PortfolioRecommendationController::class, 'generate'])
    ->middleware('auth')
    ->name('portfolio.generate.store');
Route::get('/portfolio/recommendations', [App\Http\Controllers\PortfolioRecommendationController::class, 'index'])
    ->middleware('auth')
    ->name('portfolio.index');
Route::get('/portfolio/recommendations/{recommendation}', [App\Http\Controllers\PortfolioRecommendationController::class, 'show'])
    ->middleware('auth')
    ->name('portfolio.show');

// Predictions (tiered access)
Route::post('/stocks/predict', [App\Http\Controllers\StockPredictionController::class, 'predict'])
    ->middleware('auth')
    ->name('stocks.predict');
Route::get('/stocks/{stock}/prediction-accuracy', [App\Http\Controllers\StockPredictionController::class, 'accuracy'])
    ->middleware('auth')
    ->name('stocks.prediction-accuracy');

// ML Model Management
Route::middleware('auth')->prefix('api/ml/models')->group(function () {
    Route::get('/', [App\Http\Controllers\MLModelController::class, 'index'])->name('ml.models.index');
    Route::get('/{model}', [App\Http\Controllers\MLModelController::class, 'show'])->name('ml.models.show');
    Route::put('/{model}/activate', [App\Http\Controllers\MLModelController::class, 'activate'])->name('ml.models.activate');
    Route::put('/{model}/deactivate', [App\Http\Controllers\MLModelController::class, 'deactivate'])->name('ml.models.deactivate');
    Route::delete('/{model}', [App\Http\Controllers\MLModelController::class, 'destroy'])->name('ml.models.destroy');
    Route::get('/stock/{stock}', [App\Http\Controllers\MLModelController::class, 'getStockModels'])->name('ml.models.stock');
    Route::get('/stock/{stock}/compare', [App\Http\Controllers\MLModelController::class, 'compare'])->name('ml.models.compare');
});

// ML Dashboard
Route::middleware('auth')->prefix('api/ml/dashboard')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\MLDashboardController::class, 'metrics'])->name('ml.dashboard.metrics');
    Route::get('/accuracy-over-time', [App\Http\Controllers\MLDashboardController::class, 'accuracyOverTime'])->name('ml.dashboard.accuracy');
    Route::get('/model-comparison', [App\Http\Controllers\MLDashboardController::class, 'modelComparison'])->name('ml.dashboard.comparison');
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
    // Clipper Campaign manual completion API (brand/admin only)
    Route::post('/api/campaigns/{campaign}/complete', [App\Http\Controllers\Clipper\CampaignController::class, 'completeApi'])
        ->middleware('clipper')
        ->name('api.campaigns.complete');
});

// Messaging Routes
Route::middleware('auth')->prefix('messaging')->name('messaging.')->group(function () {
    // Conversations
    Route::get('/', [App\Http\Controllers\Messaging\ConversationController::class, 'index'])->name('index');
    Route::get('/conversations/create', [App\Http\Controllers\Messaging\ConversationController::class, 'create'])->name('conversations.create');
    Route::get('/conversations', [App\Http\Controllers\Messaging\ConversationController::class, 'index'])->name('conversations.index');
    Route::post('/conversations', [App\Http\Controllers\Messaging\ConversationController::class, 'store'])
        ->middleware('throttle:10,1') // 10 conversations per minute
        ->name('conversations.store');
    Route::get('/conversations/{conversation}', [App\Http\Controllers\Messaging\ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{conversation}/participants', [App\Http\Controllers\Messaging\ConversationController::class, 'addParticipant'])
        ->middleware('throttle:10,1')
        ->name('conversations.participants.add');
    Route::delete('/conversations/{conversation}/participants/{user}', [App\Http\Controllers\Messaging\ConversationController::class, 'removeParticipant'])
        ->middleware('throttle:10,1')
        ->name('conversations.participants.remove');
    Route::post('/conversations/{conversation}/archive', [App\Http\Controllers\Messaging\ConversationController::class, 'archive'])
        ->middleware('throttle:10,1')
        ->name('conversations.archive');
    Route::post('/conversations/{conversation}/unarchive', [App\Http\Controllers\Messaging\ConversationController::class, 'unarchive'])
        ->middleware('throttle:10,1')
        ->name('conversations.unarchive');
    Route::post('/conversations/{conversation}/mute', [App\Http\Controllers\Messaging\ConversationController::class, 'mute'])
        ->middleware('throttle:10,1')
        ->name('conversations.mute');
    Route::post('/conversations/{conversation}/unmute', [App\Http\Controllers\Messaging\ConversationController::class, 'unmute'])
        ->middleware('throttle:10,1')
        ->name('conversations.unmute');

    // UX Prototype
    Route::get('/prototype', function () {
        return Inertia::render('Messaging/UXPrototype');
    })->name('prototype');

    // Messages
    Route::get('/conversations/{conversation}/messages', [App\Http\Controllers\Messaging\MessageController::class, 'index'])
        ->middleware('throttle:60,1') // 60 requests per minute
        ->name('messages.index');
    Route::post('/conversations/{conversation}/messages', [App\Http\Controllers\Messaging\MessageController::class, 'store'])
        ->middleware('throttle:30,1') // 30 messages per minute
        ->name('messages.store');
    Route::put('/messages/{message}', [App\Http\Controllers\Messaging\MessageController::class, 'update'])
        ->middleware('throttle:30,1')
        ->name('messages.update');
    Route::delete('/messages/{message}', [App\Http\Controllers\Messaging\MessageController::class, 'destroy'])
        ->middleware('throttle:30,1')
        ->name('messages.destroy');
    Route::post('/messages/{message}/read', [App\Http\Controllers\Messaging\MessageController::class, 'markAsRead'])
        ->middleware('throttle:60,1')
        ->name('messages.read');
    Route::post('/conversations/{conversation}/read', [App\Http\Controllers\Messaging\MessageController::class, 'markConversationAsRead'])
        ->middleware('throttle:60,1')
        ->name('conversations.read');
    Route::get('/conversations/{conversation}/search', [App\Http\Controllers\Messaging\MessageController::class, 'search'])
        ->middleware('throttle:30,1') // 30 searches per minute
        ->name('messages.search');

    // Typing Indicators
    Route::post('/conversations/{conversation}/typing', [App\Http\Controllers\Messaging\TypingIndicatorController::class, 'typing'])
        ->middleware('throttle:60,1') // 60 typing indicators per minute
        ->name('typing.start');
    Route::post('/conversations/{conversation}/typing/stop', [App\Http\Controllers\Messaging\TypingIndicatorController::class, 'stopTyping'])
        ->middleware('throttle:60,1')
        ->name('typing.stop');

    // Block Users
    Route::post('/users/{user}/block', [App\Http\Controllers\Messaging\BlockController::class, 'store'])
        ->middleware('throttle:10,60') // 10 blocks per hour
        ->name('users.block');
    Route::delete('/users/{user}/block', [App\Http\Controllers\Messaging\BlockController::class, 'destroy'])
        ->middleware('throttle:10,60')
        ->name('users.unblock');
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
