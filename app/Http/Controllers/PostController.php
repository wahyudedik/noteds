<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Post::with('user')
            ->where('status', 'active')
            ->latest();

        if ($request->has('purpose_type') && $request->purpose_type !== 'all') {
            $query->where('purpose_type', $request->purpose_type);
        }

        $posts = $query->paginate(15);

        // Get user vote for each post if authenticated
        $userVotes = [];
        if ($request->user()) {
            $postIds = $posts->pluck('id');
            $votes = \App\Models\PostVote::where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->get()
                ->keyBy('post_id');
            
            foreach ($posts as $post) {
                $vote = $votes->get($post->id);
                $userVotes[$post->id] = $vote ? $vote->vote_type : null;
            }
        }

        // Trending topics (purpose types by count)
        $purposeTypeLabels = [
            'idea_business' => '💡 Ide Bisnis',
            'ask_question' => '❓ Tanya Masalah Bisnis',
            'share_experience' => '📈 Sharing Pengalaman',
            'find_partner' => '🤝 Cari Partner',
            'find_tools' => '🛠 Cari Tools / Resource',
            'validate_idea' => '🧪 Validasi Ide',
        ];

        $trending = Post::where('status', 'active')
            ->where('created_at', '>=', now()->subDays(7))
            ->select('purpose_type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('purpose_type')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($purposeTypeLabels) {
                return [
                    'id' => $item->purpose_type,
                    'name' => $purposeTypeLabels[$item->purpose_type] ?? $item->purpose_type,
                    'count' => $item->count,
                ];
            });

        // Suggested users (users with most posts in last 30 days)
        $suggestedUsers = \App\Models\User::whereHas('posts', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })
            ->withCount(['posts' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }])
            ->orderByDesc('posts_count')
            ->limit(5)
            ->get();

        // Check if this is home route or posts.index route
        if ($request->routeIs('home')) {
            return Inertia::render('Home', [
                'posts' => $posts,
                'filters' => $request->only(['purpose_type']),
                'trending' => $trending,
                'suggestedUsers' => $suggestedUsers,
                'userVotes' => $userVotes,
            ]);
        }

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['purpose_type']),
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
        $post = $request->user()->posts()->create($request->validated());

        // Redirect to home feed after creating post
        return redirect()->route('home')->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): Response
    {
        $post->load([
            'user',
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['user', 'replies.user'])
                    ->orderBy('is_best_answer', 'desc')
                    ->orderBy('upvotes_count', 'desc')
                    ->orderBy('created_at', 'asc');
            },
        ]);

        $userVote = null;
        if ($request->user()) {
            $vote = $post->votes()->where('user_id', $request->user()->id)->first();
            $userVote = $vote ? $vote->vote_type : null;
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
            'validationStats' => $validationStats,
            'userValidation' => $userValidation,
        ]);
    }
}
