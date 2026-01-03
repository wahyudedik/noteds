<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Services\FeedService;
use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private FeedService $feedService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Post::with(['user', 'media'])
            ->where('status', 'active')
            ->latest();

        if ($request->has('purpose_type') && $request->purpose_type !== 'all') {
            $query->where('purpose_type', $request->purpose_type);
        }

        $posts = $query->paginate(15);

        // Get user vote and bookmarks for each post if authenticated
        $userVotes = [];
        $userBookmarks = [];
        if ($request->user()) {
            $postIds = $posts->pluck('id');
            
            // Get votes
            $votes = \App\Models\PostVote::where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->get()
                ->keyBy('post_id');

            foreach ($posts as $post) {
                $vote = $votes->get($post->id);
                $userVotes[$post->id] = $vote ? $vote->vote_type : null;
            }

            // Get bookmarks
            $bookmarks = \App\Models\Bookmark::where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->pluck('post_id')
                ->toArray();

            foreach ($posts as $post) {
                $userBookmarks[$post->id] = in_array($post->id, $bookmarks);
            }
        }

        // Check if this is home route or posts.index route
        if ($request->routeIs('home')) {
            $trending = $this->feedService->getTrendingTopics(7, 5);
            $suggestedUsers = $this->feedService->getSuggestedUsers(30, 5);
            $quickStats = $this->feedService->getQuickStats($request->user());

            return Inertia::render('Home', [
                'posts' => $posts,
                'filters' => $request->only(['purpose_type']),
                'trending' => $trending,
                'suggestedUsers' => $suggestedUsers,
                'quickStats' => $quickStats,
                'userVotes' => $userVotes,
                'userBookmarks' => $userBookmarks,
            ]);
        }

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['purpose_type']),
            'userVotes' => $userVotes,
            'userBookmarks' => $userBookmarks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Posts/Create');
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
            unset($validated['link_url'], $validated['link_preview_title'], 
                  $validated['link_preview_description'], $validated['link_preview_image'], 
                  $validated['link_preview_site_name']);
        }

        // Remove images from validated data (we'll handle separately)
        $images = $validated['images'] ?? [];
        unset($validated['images']);

        // Merge link preview data
        $postData = array_merge($validated, $linkPreviewData);

        // Create post
        $post = $request->user()->posts()->create($postData);

        // Handle image uploads
        if (!empty($images)) {
            $this->storePostImages($post, $images);
        }

        // Redirect to home feed after creating post
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
        $post->load([
            'user',
            'media',
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['user', 'replies.user'])
                    ->orderBy('is_best_answer', 'desc')
                    ->orderBy('upvotes_count', 'desc')
                    ->orderBy('created_at', 'asc');
            },
        ]);

        $userVote = null;
        $isBookmarked = false;
        if ($request->user()) {
            $vote = $post->votes()->where('user_id', $request->user()->id)->first();
            $userVote = $vote ? $vote->vote_type : null;
            
            $bookmark = $post->bookmarkedBy()->where('user_id', $request->user()->id)->first();
            $isBookmarked = $bookmark !== null;
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

        return Inertia::render('Posts/Show', [
            'post' => $post,
            'userVote' => $userVote,
            'isBookmarked' => $isBookmarked,
            'validationStats' => $validationStats,
            'userValidation' => $userValidation,
        ]);
    }
}
