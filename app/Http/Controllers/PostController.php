<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Services\FeedService;
use App\Services\HashtagService;
use App\Services\MentionService;
use App\Services\PollService;
use App\Services\PostEditService;
use App\Services\TrendingService;
use App\Services\PostAnalyticsService;
use App\Services\PostSeriesService;
use App\Services\SupplierRecommendationService;
use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private FeedService $feedService,
        private HashtagService $hashtagService,
        private MentionService $mentionService,
        private PollService $pollService,
        private PostEditService $postEditService,
        private TrendingService $trendingService,
        private PostAnalyticsService $analyticsService,
        private PostSeriesService $seriesService,
        private SupplierRecommendationService $supplierRecommendationService,
        private \App\Services\PostRankingService $rankingService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Query regular posts
        $query = Post::with(['user', 'media', 'hashtags', 'poll.options'])
            ->where('status', 'active');

        if ($request->has('purpose_type') && $request->purpose_type !== 'all') {
            $query->where('purpose_type', $request->purpose_type);
        }

        $posts = $query->latest()->get();

        // Fetch and transform reposts
        $repostPosts = $this->fetchAndTransformReposts($request);

        // Merge regular posts and repost posts, then sort by created_at
        $allPosts = $posts->concat($repostPosts)
            ->sortByDesc('created_at')
            ->values();

        // Privacy filter: respect author's posts_visibility setting
        $viewer = $request->user();
        $allPosts = $this->filterVisiblePosts($allPosts, $viewer);

        // Paginate manually since we have a collection
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $items = $allPosts->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Create paginator manually
        $paginatedPosts = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allPosts->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get user vote, bookmarks, reposts, and poll votes for each post if authenticated
        $interactions = $this->getUserInteractions($paginatedPosts, $viewer);

        // Check if this is home route or posts.index route
        if ($request->routeIs('home')) {
            $trending = $this->feedService->getTrendingTopics(7, 5);
            $suggestedUsers = $this->feedService->getSuggestedUsers(30, 5);
            $quickStats = $this->feedService->getQuickStats($request->user());
            $shareDraft = $this->getShareDraft($request);

            return Inertia::render('Home', array_merge([
                'posts' => $paginatedPosts,
                'filters' => $request->only(['purpose_type']),
                'trending' => $trending,
                'suggestedUsers' => $suggestedUsers,
                'quickStats' => $quickStats,
                'shareDraft' => $shareDraft,
            ], $interactions));
        }

        return Inertia::render('Posts/Index', array_merge([
            'posts' => $paginatedPosts,
            'filters' => $request->only(['purpose_type']),
        ], $interactions));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $businessTypes = $this->supplierRecommendationService->getBusinessTypes();

        return Inertia::render('Posts/Create', [
            'businessTypes' => $businessTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Extract link preview data
        $linkPreviewData = [];
        if (isset($validated['link_url'])) {
            $linkPreviewData = [
                'link_url' => $validated['link_url'],
                'link_preview_title' => $validated['link_preview_title'] ?? null,
                'link_preview_description' => $validated['link_preview_description'] ?? null,
                'link_preview_image' => $validated['link_preview_image'] ?? null,
                'link_preview_site_name' => $validated['link_preview_site_name'] ?? null,
            ];
            unset(
                $validated['link_url'],
                $validated['link_preview_title'],
                $validated['link_preview_description'],
                $validated['link_preview_image'],
                $validated['link_preview_site_name']
            );
        }

        // Remove images from validated data (we'll handle separately)
        $images = $validated['images'] ?? [];
        unset($validated['images']);

        // Merge link preview data
        $postData = array_merge($validated, $linkPreviewData);

        // Set publish_status based on scheduled_at
        if (isset($validated['scheduled_at']) && $validated['scheduled_at']) {
            $postData['publish_status'] = 'scheduled';
        } else {
            $postData['publish_status'] = 'published';
        }

        // Create post
        $post = $request->user()->posts()->create($postData);

        // Handle image uploads
        if (!empty($images)) {
            $this->storePostImages($post, $images);
        }

        // Process hashtags
        $content = $validated['content'] ?? '';
        $hashtagNames = $this->hashtagService->extractHashtags($content);
        if (!empty($hashtagNames)) {
            $this->hashtagService->syncHashtags($post, $hashtagNames);
        }

        // Process mentions
        $mentionUsernames = $this->mentionService->extractMentions($content);
        if (!empty($mentionUsernames)) {
            $this->mentionService->processPostMentions($post, $mentionUsernames);
        }

        // Handle poll creation if provided
        if ($request->has('poll') && is_array($request->poll)) {
            $pollData = $request->poll;
            if (isset($pollData['question']) && isset($pollData['options']) && is_array($pollData['options'])) {
                $endsAt = isset($pollData['ends_at']) ? new \DateTime($pollData['ends_at']) : null;
                $this->pollService->createPoll($post, $pollData['question'], $pollData['options'], $endsAt);
            }
        }

        app(\App\Services\GamificationService::class)->awardAction($request->user(), 'post_create', [
            'post_id' => $post->id,
        ]);

        // Redirect to home feed after creating post
        // Use route helper to ensure proper route resolution
        // Don't redirect to the newly created post - stay on home feed
        return redirect()->route('home')->with('success', 'Post created successfully.');
    }

    /**
     * Store images for a post.
     */
    private function storePostImages(Post $post, array $images): void
    {
        $order = 0;
        foreach ($images as $image) {
            $extension = $image->getClientOriginalExtension();
            $fileName = Str::uuid() . '_' . time() . '.' . $extension;
            $filePath = 'posts/images/' . $post->id . '/' . $fileName;

            // Store image
            $image->storeAs('posts/images/' . $post->id, $fileName, 'public');

            // Create PostMedia record
            PostMedia::create([
                'post_id' => $post->id,
                'file_path' => $filePath,
                'file_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize(),
                'order' => $order++,
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): Response
    {
        // Extra safety check: ensure post ID is valid UUID
        // This should not happen with UUID constraint on route, but adding extra safety
        if (!Str::isUuid($post->id)) {
            abort(404, 'Post not found.');
        }

        // Ensure post is active (unless user is the owner)
        if ($post->status !== 'active' && (!$request->user() || $post->user_id !== $request->user()->id)) {
            abort(404, 'Post not found.');
        }

        // Enforce post visibility per author's settings
        $author = $post->user;
        $viewer = $request->user();
        $vis = $author->settings?->privacy_settings['posts_visibility'] ?? 'public';
        if ($vis === 'private' && (!$viewer || $viewer->id !== $author->id)) {
            abort(403, 'This post is private.');
        }
        if ($vis === 'followers' && (!$viewer || ($viewer->id !== $author->id && !$viewer->isFollowing($author)))) {
            abort(403, 'This post is only visible to followers.');
        }

        // Track view (only for non-owners)
        if ($request->user() && $post->user_id !== $request->user()->id) {
            $this->analyticsService->trackView($post);
        }

        $post->load([
            'user',
            'media',
            'hashtags',
            'mentions.user',
            'poll.options',
            'editHistory.user',
            'seriesRoot',
            'seriesPosts.user',
            'collaborators.user',
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->with([
                        'user',
                        'replies.user',
                        'replies.media',
                        'mentions.user',
                        'media',
                        'reactions',
                        'editHistory.user'
                    ])
                    ->orderBy('is_pinned', 'desc')
                    ->orderBy('is_best_answer', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('upvotes_count', 'desc');
            },
        ]);

        // Get series navigation if post is in a series
        $seriesNavigation = null;
        if ($post->isInSeries()) {
            $seriesNavigation = $this->seriesService->getSeriesNavigation($post);
        }

        // Get user's poll vote if exists
        $userPollVote = null;
        if ($request->user() && $post->poll) {
            $userPollVote = $this->pollService->getUserVote($post->poll->id, $request->user()->id);
        }

        $userVote = null;
        $isBookmarked = false;
        $isReposted = false;
        if ($request->user()) {
            $vote = $post->votes()->where('user_id', $request->user()->id)->first();
            $userVote = $vote ? $vote->vote_type : null;

            $bookmark = $post->bookmarkedBy()->where('user_id', $request->user()->id)->first();
            $isBookmarked = $bookmark !== null;

            $repost = $post->reposts()->where('user_id', $request->user()->id)->first();
            $isReposted = $repost !== null;
        }

        $validationStats = null;
        $userValidation = null;
        if ($post->purpose_type === 'validate_idea') {
            $validationController = new \App\Http\Controllers\IdeaValidationController();
            $validationStats = $validationController->getStats($post);

            if ($request->user()) {
                $userValidation = \App\Models\IdeaValidation::where('post_id', $post->id)
                    ->where('user_id', $request->user()->id)
                    ->first();
            }
        }

        // Get supplier recommendations if applicable
        $supplierRecommendations = null;
        $detectedBusinessType = null;
        if (in_array($post->purpose_type, ['idea_business', 'validate_idea', 'find_tools'])) {
            // Use business_type from post if set, otherwise try to detect
            $businessType = $post->business_type;
            if (!$businessType) {
                $businessType = $this->supplierRecommendationService->detectBusinessTypeFromPost($post);
                $detectedBusinessType = $businessType;
            }

            if ($businessType) {
                $location = $request->user()?->location ?? null; // Adjust based on user model structure
                $supplierRecommendations = $this->supplierRecommendationService->getRecommendedSuppliers(
                    $businessType,
                    $location,
                    5
                );
            }
        }

        // Permissions for UI reinforcement
        $commentPolicy = $author->settings?->privacy_settings['comments_permission'] ?? 'everyone';
        $canComment = true;
        if ($request->user() && $request->user()->id !== $author->id) {
            if ($commentPolicy === 'none') {
                $canComment = false;
            } elseif ($commentPolicy === 'followers' && !$request->user()->isFollowing($author)) {
                $canComment = false;
            }
        }
        if (!$request->user()) {
            // guests cannot comment if not everyone
            $canComment = ($commentPolicy === 'everyone');
        }
        // vote/repost/bookmark permissions aligned with post visibility
        $canVote = $request->user() && $request->user()->id !== $author->id;
        $canRepost = (bool) $request->user();
        $canBookmark = (bool) $request->user();
        $isOwner = $request->user() && $request->user()->id === $author->id;
        $isFollower = $request->user() ? $request->user()->isFollowing($author) : false;
        if ($vis === 'followers') {
            $isAllowedViewer = $request->user() && ($isOwner || $isFollower);
            $canVote = $canVote && $isAllowedViewer;
            $canRepost = $canRepost && $isAllowedViewer;
            $canBookmark = $canBookmark && $isAllowedViewer;
        } elseif ($vis === 'private') {
            $canVote = false;
            $canRepost = $isOwner; // allow owner workflow only
            $canBookmark = $isOwner;
        }
        // Reasons for disabled actions
        $voteReason = null;
        if (!$canVote) {
            if ($isOwner) $voteReason = 'Tidak dapat vote pada post sendiri';
            elseif ($vis === 'private') $voteReason = 'Post privat: vote tidak tersedia';
            elseif ($vis === 'followers' && !$isFollower) $voteReason = 'Hanya followers yang dapat vote';
            else $voteReason = 'Interaksi tidak diizinkan';
        }
        $repostReason = null;
        if (!$canRepost) {
            if (!$request->user()) $repostReason = 'Masuk untuk repost';
            elseif ($vis === 'private' && !$isOwner) $repostReason = 'Post privat: tidak dapat repost';
            elseif ($vis === 'followers' && !$isFollower) $repostReason = 'Hanya followers yang dapat repost';
            else $repostReason = 'Interaksi tidak diizinkan';
        }
        $bookmarkReason = null;
        if (!$canBookmark) {
            if (!$request->user()) $bookmarkReason = 'Masuk untuk bookmark';
            elseif ($vis === 'private' && !$isOwner) $bookmarkReason = 'Post privat: tidak dapat bookmark';
            elseif ($vis === 'followers' && !$isFollower) $bookmarkReason = 'Hanya followers yang dapat bookmark';
            else $bookmarkReason = 'Interaksi tidak diizinkan';
        }

        return Inertia::render('Posts/Show', [
            'post' => $post,
            'userVote' => $userVote,
            'isBookmarked' => $isBookmarked,
            'isReposted' => $isReposted,
            'validationStats' => $validationStats,
            'userValidation' => $userValidation,
            'userPollVote' => $userPollVote,
            'seriesNavigation' => $seriesNavigation,
            'collaborators' => $post->collaborators,
            'supplierRecommendations' => $supplierRecommendations,
            'businessType' => $post->business_type ?? $detectedBusinessType ?? null,
            'permissions' => [
                'can_comment' => $canComment,
                'comments_policy' => $commentPolicy,
                'posts_visibility' => $vis,
                'can_vote' => $canVote,
                'can_repost' => $canRepost,
                'can_bookmark' => $canBookmark,
            ],
            'restrictions' => [
                'vote' => $voteReason,
                'repost' => $repostReason,
                'bookmark' => $bookmarkReason,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Post $post): Response
    {
        // Check authorization
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $post->load(['hashtags', 'poll.options']);

        $businessTypes = $this->supplierRecommendationService->getBusinessTypes();

        return Inertia::render('Posts/Edit', [
            'post' => $post,
            'businessTypes' => $businessTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        // Check authorization (owner or collaborator with edit permission)
        if (!$post->canUserEdit($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'content' => ['required', 'string', 'min:50'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'publish_status' => ['nullable', 'in:draft,scheduled,published'],
            'series_id' => ['nullable', 'exists:posts,id'],
        ]);

        // Edit post and save history
        $post = $this->postEditService->editPost($post, $validated, $request->user()->id);

        // Process hashtags
        $hashtagNames = $this->hashtagService->extractHashtags($validated['content']);
        if (!empty($hashtagNames)) {
            $this->hashtagService->syncHashtags($post, $hashtagNames);
        }

        // Process mentions
        $mentionUsernames = $this->mentionService->extractMentions($validated['content']);
        if (!empty($mentionUsernames)) {
            $this->mentionService->processPostMentions($post, $mentionUsernames);
        }

        return redirect()->route('posts.show', $post)->with('success', 'Post updated successfully.');
    }

    /**
     * Get edit history for a post.
     */
    public function history(Request $request, Post $post): Response
    {
        $history = $this->postEditService->getEditHistory($post);

        return Inertia::render('Posts/History', [
            'post' => $post,
            'history' => $history,
        ]);
    }

    /**
     * Display trending posts.
     */
    public function trending(Request $request): Response
    {
        $limit = $request->input('limit', 20);
        $posts = $this->trendingService->getTrendingPosts($limit);
        // Privacy filter
        $viewer = $request->user();
        $posts = $posts->filter(function ($post) use ($viewer) {
            $author = $post->user;
            if (!$author) return false;
            $vis = $author->settings?->privacy_settings['posts_visibility'] ?? 'public';
            if ($vis === 'public') return true;
            if ($viewer && $viewer->id === $author->id) return true;
            if ($vis === 'followers') {
                return $viewer ? $viewer->isFollowing($author) : false;
            }
            if ($vis === 'private') {
                return false;
            }
            return true;
        })->values();

        // Get user votes, bookmarks, reposts, and poll votes for each post if authenticated
        $userVotes = [];
        $userBookmarks = [];
        $userReposts = [];
        $userPollVotes = [];

        if ($request->user()) {
            $postIds = $posts->pluck('id')->toArray();

            $votes = \App\Models\PostVote::where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->get()
                ->keyBy('post_id');

            $bookmarks = \App\Models\Bookmark::where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->pluck('post_id')
                ->toArray();

            $userRepostRecords = \App\Models\Repost::where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->pluck('post_id')
                ->toArray();

            $polls = \App\Models\Poll::whereIn('post_id', $postIds)->pluck('id');
            if ($polls->isNotEmpty()) {
                $pollVotes = \App\Models\PollVote::where('user_id', $request->user()->id)
                    ->whereIn('poll_id', $polls)
                    ->get()
                    ->keyBy('poll_id');
            }

            foreach ($postIds as $postId) {
                $userVotes[$postId] = $votes[$postId] ?? null;
                $userBookmarks[$postId] = in_array($postId, $bookmarks);
                $userReposts[$postId] = in_array($postId, $userRepostRecords);

                $post = $posts->firstWhere('id', $postId);
                if ($post && $post->poll) {
                    $userPollVotes[$postId] = $pollVotes[$post->poll->id] ?? null;
                } else {
                    $userPollVotes[$postId] = null;
                }
            }
        }

        return Inertia::render('Posts/Trending', [
            'posts' => $posts,
            'userVotes' => $userVotes,
            'userBookmarks' => $userBookmarks,
            'userReposts' => $userReposts,
            'userPollVotes' => $userPollVotes,
        ]);
    }

    public function top(Request $request): Response
    {
        $period = $request->input('period', 'week');
        $metric = $request->input('metric', 'engagement');
        $perPage = 15;
        $purposeType = $request->input('purpose_type', 'all');
        $posts = $this->rankingService->getTopPosts($period, $metric, $perPage, $purposeType);

        $viewer = $request->user();

        // Filter visible posts using collection to avoid undefined method errors on Paginator contract
        $filteredItems = collect($posts->items())->map(function ($post) use ($viewer) {
            $author = $post->user;
            if (!$author) return null;
            $vis = $author->settings?->privacy_settings['posts_visibility'] ?? 'public';
            if ($vis === 'public') return $post;
            if ($viewer && $viewer->id === $author->id) return $post;
            if ($vis === 'followers' && $viewer && $viewer->isFollowing($author)) return $post;
            return null;
        })->filter()->values();

        // Update the paginator's collection if it supports it
        if ($posts instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $posts->setCollection($filteredItems);
        }

        $userVotes = [];
        $userBookmarks = [];
        if ($request->user()) {
            $postIds = $filteredItems->pluck('id')->toArray();
            $votes = \App\Models\PostVote::where('user_id', $request->user()->id)->whereIn('post_id', $postIds)->get()->keyBy('post_id');
            $bookmarks = \App\Models\Bookmark::where('user_id', $request->user()->id)->whereIn('post_id', $postIds)->pluck('post_id')->toArray();
            foreach ($postIds as $postId) {
                $userVotes[$postId] = $votes[$postId]->vote_type ?? null;
                $userBookmarks[$postId] = in_array($postId, $bookmarks);
            }
        }

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
            'filters' => array_merge($request->only(['purpose_type']), ['sort' => 'top', 'period' => $period, 'metric' => $metric]),
            'userVotes' => $userVotes,
            'userBookmarks' => $userBookmarks,
            'userReposts' => [],
        ]);
    }

    /**
     * Pin a post.
     */
    public function pin(Request $request, Post $post): RedirectResponse
    {
        // Only post owner can pin
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $post->update([
            'is_pinned' => true,
            'pinned_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Post pinned successfully.');
    }

    /**
     * Unpin a post.
     */
    public function unpin(Request $request, Post $post): RedirectResponse
    {
        // Only post owner can unpin
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $post->update([
            'is_pinned' => false,
            'pinned_at' => null,
        ]);

        return redirect()->back()->with('success', 'Post unpinned successfully.');
    }

    /**
     * Get series posts.
     */
    public function series(Request $request, Post $post): Response
    {
        $post->load(['seriesRoot', 'seriesPosts.user']);

        if ($post->is_series_root) {
            $seriesPosts = $this->seriesService->getSeriesPosts($post);
        } elseif ($post->series_id) {
            $seriesRoot = $post->seriesRoot;
            $seriesPosts = $this->seriesService->getSeriesPosts($seriesRoot);
        } else {
            $seriesPosts = collect([]);
        }

        return Inertia::render('Posts/Series', [
            'post' => $post,
            'seriesPosts' => $seriesPosts,
        ]);
    }

    /**
     * Create or add to series.
     */
    public function createSeries(Request $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:create,add',
            'series_id' => 'required_if:action,add|uuid|exists:posts,id',
        ]);

        if ($request->action === 'create') {
            $this->seriesService->createSeries($post);
            $message = 'Series created successfully.';
        } else {
            $seriesRoot = Post::findOrFail($request->series_id);
            if ($seriesRoot->user_id !== $request->user()->id) {
                abort(403);
            }
            $this->seriesService->addToSeries($post, $seriesRoot);
            $message = 'Post added to series successfully.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update series order.
     */
    public function updateSeriesOrder(Request $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'order' => 'required|integer|min:1',
        ]);

        $this->seriesService->updateOrder($post, $request->order);

        return redirect()->back()->with('success', 'Series order updated successfully.');
    }

    /**
     * Upload image for inline insertion in rich text editor.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:2048', 'mimes:jpeg,jpg,png,gif,webp'],
        ]);

        $image = $request->file('image');
        $fileName = Str::uuid() . '_' . time() . '.' . $image->getClientOriginalExtension();
        $filePath = 'posts/images/temp/' . $fileName;

        $image->storeAs('posts/images/temp', $fileName, 'public');

        return response()->json([
            'url' => Storage::url($filePath),
            'path' => $filePath,
        ]);
    }

    private function fetchAndTransformReposts(Request $request): \Illuminate\Support\Collection
    {
        // Query reposts with original post relationship loaded
        $repostsQuery = \App\Models\Repost::with([
            'post' => function ($query) {
                $query->with(['user', 'media', 'hashtags', 'poll.options'])->where('status', 'active');
            },
            'user'
        ])->whereHas('post', function ($query) use ($request) {
            $query->where('status', 'active');
            if ($request->has('purpose_type') && $request->purpose_type !== 'all') {
                $query->where('purpose_type', $request->purpose_type);
            }
        })->latest();

        $reposts = $repostsQuery->get();

        // Transform reposts into post-like objects with original post data
        return $reposts->map(function ($repost) {
            if (!$repost->post) {
                return null;
            }

            // Create a post-like object using original post data
            $originalPost = $repost->post;

            // Convert to array to ensure all relationships and attributes are properly serialized
            $repostPostArray = $originalPost->toArray();

            // Use repost ID as unique identifier to avoid conflicts with original post
            $repostPostArray['id'] = 'repost_' . $repost->id;

            // Set repost-specific data (ensure these are included in serialization)
            $repostPostArray['is_repost'] = true;
            $repostPostArray['repost_id'] = $repost->id;
            $repostPostArray['repost_user'] = $repost->user ? $repost->user->toArray() : null;
            $repostPostArray['repost_created_at'] = $repost->created_at?->toDateTimeString();
            $repostPostArray['original_post_id'] = $originalPost->id; // Store original post ID for reference
            $repostPostArray['created_at'] = $repost->created_at?->toDateTimeString(); // Use repost timestamp for sorting

            // Add quote repost data if applicable
            if ($repost->is_quote_repost) {
                $repostPostArray['is_quote_repost'] = true;
                $repostPostArray['quote_content'] = $repost->quote_content;
                $repostPostArray['quote_display_mode'] = $repost->display_mode;
                if ($repost->quote_post_id) {
                    $quotePost = Post::with(['user', 'media', 'hashtags'])->find($repost->quote_post_id);
                    if ($quotePost) {
                        $repostPostArray['quote_post'] = $quotePost->toArray();
                    }
                }
            }

            // Add comment if exists
            if ($repost->comment) {
                $repostPostArray['repost_comment'] = $repost->comment;
            }

            // Convert back to object for consistency with regular posts
            return (object) $repostPostArray;
        })->filter();
    }

    private function filterVisiblePosts(\Illuminate\Support\Collection $posts, ?\App\Models\User $viewer): \Illuminate\Support\Collection
    {
        return $posts->filter(function ($item) use ($viewer) {
            $author = is_object($item) ? ($item->user ?? null) : ($item['user'] ?? null);
            if (!$author) return false;
            // normalize to model
            if (is_array($author)) {
                $authorModel = \App\Models\User::find($author['id'] ?? null);
            } else {
                $authorModel = $author;
            }
            if (!$authorModel) return false;
            $vis = $authorModel->settings?->privacy_settings['posts_visibility'] ?? 'public';
            if ($vis === 'public') return true;
            if ($viewer && $viewer->id === $authorModel->id) return true;
            if ($vis === 'followers') {
                return $viewer ? $viewer->isFollowing($authorModel) : false;
            }
            if ($vis === 'private') {
                return false;
            }
            return true;
        })->values();
    }

    private function getUserInteractions($paginatedPosts, ?\App\Models\User $user): array
    {
        $userVotes = [];
        $userBookmarks = [];
        $userReposts = [];
        $userPollVotes = [];

        if ($user) {
            $originalPostIds = [];

            // Collect original post IDs from both regular posts and reposts
            foreach ($paginatedPosts as $post) {
                // Check if post is a repost (can be object with is_repost property or array)
                $isRepost = is_object($post)
                    ? (property_exists($post, 'is_repost') && $post->is_repost)
                    : (isset($post['is_repost']) && $post['is_repost']);

                if ($isRepost) {
                    // Get original post ID from repost
                    $originalPostId = is_object($post)
                        ? ($post->original_post_id ?? ($post->original_post->id ?? null))
                        : ($post['original_post_id'] ?? ($post['original_post']['id'] ?? null));

                    if ($originalPostId) {
                        $originalPostIds[] = $originalPostId;
                    }
                } else {
                    $postId = is_object($post) ? $post->id : $post['id'];
                    $originalPostIds[] = $postId;
                }
            }
            $originalPostIds = array_unique($originalPostIds);

            // Get votes for original posts
            $votes = \App\Models\PostVote::where('user_id', $user->id)
                ->whereIn('post_id', $originalPostIds)
                ->get()
                ->keyBy('post_id');

            // Get bookmarks
            $bookmarks = \App\Models\Bookmark::where('user_id', $user->id)
                ->whereIn('post_id', $originalPostIds)
                ->pluck('post_id')
                ->toArray();

            // Get reposts (for original posts)
            $userRepostRecords = \App\Models\Repost::where('user_id', $user->id)
                ->whereIn('post_id', $originalPostIds)
                ->pluck('post_id')
                ->toArray();

            // Get poll votes
            $polls = \App\Models\Poll::whereIn('post_id', $originalPostIds)->pluck('id');
            if ($polls->isNotEmpty()) {
                $pollVotes = \App\Models\PollVote::where('user_id', $user->id)
                    ->whereIn('poll_id', $polls)
                    ->get()
                    ->keyBy('poll_id');
            } else {
                $pollVotes = collect();
            }

            foreach ($paginatedPosts as $post) {
                // Handle both object and array formats
                $postId = is_object($post) ? $post->id : $post['id'];

                // Check if post is a repost
                $isRepost = is_object($post)
                    ? (property_exists($post, 'is_repost') && $post->is_repost)
                    : (isset($post['is_repost']) && $post['is_repost']);

                // For reposts, use original post ID; for regular posts, use post ID
                if ($isRepost) {
                    $originalPostId = is_object($post)
                        ? ($post->original_post_id ?? ($post->original_post->id ?? $postId))
                        : ($post['original_post_id'] ?? ($post['original_post']['id'] ?? $postId));
                } else {
                    $originalPostId = $postId;
                }

                // Votes and bookmarks are based on original post
                $vote = $votes->get($originalPostId);
                $userVotes[$postId] = $vote ? $vote->vote_type : null;

                $userBookmarks[$postId] = in_array($originalPostId, $bookmarks);

                // Reposts are based on original post
                $userReposts[$postId] = in_array($originalPostId, $userRepostRecords);

                // Poll votes
                $postModel = is_object($post) && isset($post->id)
                    ? Post::find($originalPostId)
                    : Post::find($originalPostId);
                if ($postModel && $postModel->poll) {
                    $pollVote = $pollVotes->get($postModel->poll->id);
                    $userPollVotes[$postId] = $pollVote ? $pollVote->toArray() : null;
                } else {
                    $userPollVotes[$postId] = null;
                }
            }
        }

        return [
            'userVotes' => $userVotes,
            'userBookmarks' => $userBookmarks,
            'userReposts' => $userReposts,
            'userPollVotes' => $userPollVotes,
        ];
    }

    private function getShareDraft(Request $request): ?array
    {
        return null;
    }
}
