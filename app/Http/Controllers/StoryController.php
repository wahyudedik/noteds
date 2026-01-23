<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\StoryView;
use App\Models\StoryReaction;
use App\Models\StoryHighlight;
use App\Models\StoryHighlightItem;
use App\Services\HashtagService;
use App\Services\MentionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoryController extends Controller
{
    public function following(Request $request)
    {
        $user = $request->user();
        $perUsers = (int) ($request->input('per_users') ?? 20);
        $perStoriesPerUser = (int) ($request->input('per_stories_per_user') ?? 5);

        $follows = \App\Models\Follow::where('follower_id', $user->id)
            ->whereHas('following', function ($q) {
                $q->where('is_banned', false);
            })
            ->with('following')
            ->orderBy('created_at', 'desc')
            ->paginate($perUsers);

        $followingIds = collect($follows->items())->pluck('following_id')->all();

        $stories = Story::whereIn('user_id', $followingIds)
            ->where('expires_at', '>', now())
            ->orderBy('user_id')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'user_id', 'caption', 'media_path', 'media_type', 'created_at']);

        $grouped = [];
        foreach ($stories->groupBy('user_id') as $uid => $list) {
            $grouped[$uid] = $list->take($perStoriesPerUser)->values();
        }

        $groups = array_map(function ($follow) use ($grouped) {
            $uid = $follow->following_id;
            return [
                'user' => [
                    'id' => $follow->following->id,
                    'name' => $follow->following->name,
                ],
                'stories' => isset($grouped[$uid]) ? $grouped[$uid] : collect(),
            ];
        }, $follows->items());

        return response()->json([
            'data' => $groups,
            'meta' => [
                'current_page' => $follows->currentPage(),
                'last_page' => $follows->lastPage(),
                'per_users' => $perUsers,
                'per_stories_per_user' => $perStoriesPerUser,
                'total_users' => $follows->total(),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $stories = Story::where('user_id', $request->user()->id)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $stories,
        ]);
    }

    public function store(Request $request, HashtagService $hashtagService, MentionService $mentionService)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpeg,jpg,png,mp4|max:51200',
            'caption' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $media = $request->file('media');
        $ext = strtolower($media->getClientOriginalExtension());
        $mediaType = in_array($ext, ['mp4']) ? 'video' : 'image';
        $filename = (string) Str::uuid() . '.' . $ext;
        $path = $media->storeAs('stories/' . $user->id, $filename, 'public');

        $story = Story::create([
            'user_id' => $user->id,
            'caption' => $request->input('caption'),
            'media_path' => $path,
            'media_type' => $mediaType,
            'expires_at' => now()->addDay(),
        ]);

        $caption = $story->caption ?? '';
        $mentions = $mentionService->extractMentions($caption);
        if (!empty($mentions)) {
            $this->processStoryMentions($story, $mentions);
        }
        $hashtags = $hashtagService->extractHashtags($caption);
        if (!empty($hashtags)) {
            $this->syncStoryHashtags($story, $hashtags, $hashtagService);
        }

        return response()->json([
            'data' => $story,
        ], 201);
    }

    public function show(Story $story)
    {
        if ($story->isExpired()) {
            abort(404);
        }
        return response()->json(['data' => $story]);
    }

    public function trackView(Request $request, Story $story)
    {
        if ($story->isExpired()) {
            abort(404);
        }
        $viewerId = $request->user()->id;
        $exists = StoryView::where('story_id', $story->id)
            ->where('user_id', $viewerId)
            ->exists();
        if (!$exists) {
            StoryView::create([
                'story_id' => $story->id,
                'user_id' => $viewerId,
                'viewed_at' => now(),
            ]);
            Story::where('id', $story->id)->update([
                'views_count' => \DB::raw('views_count + 1'),
            ]);
        }
        return response()->json(['status' => 'ok']);
    }

    public function react(Request $request, Story $story)
    {
        if ($story->isExpired()) {
            abort(404);
        }
        $request->validate([
            'emoji' => 'required|string|max:32',
        ]);
        $userId = $request->user()->id;
        $reaction = StoryReaction::updateOrCreate(
            ['story_id' => $story->id, 'user_id' => $userId],
            ['emoji' => $request->input('emoji')]
        );
        Story::where('id', $story->id)->update([
            'reactions_count' => \DB::raw('reactions_count + 1'),
        ]);
        return response()->json(['data' => $reaction]);
    }

    public function createHighlight(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'cover_image' => 'nullable|file|mimes:jpeg,jpg,png|max:10240',
        ]);
        $user = $request->user();
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->storeAs(
                'stories/' . $user->id . '/highlights',
                (string) Str::uuid() . '.' . strtolower($request->file('cover_image')->getClientOriginalExtension()),
                'public'
            );
        }
        $highlight = StoryHighlight::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'cover_image' => $coverPath,
        ]);
        return response()->json(['data' => $highlight], 201);
    }

    public function addToHighlight(Request $request, Story $story, StoryHighlight $highlight)
    {
        if ($highlight->user_id !== $request->user()->id) {
            abort(403);
        }
        $exists = StoryHighlightItem::where('story_highlight_id', $highlight->id)
            ->where('story_id', $story->id)->exists();
        if (!$exists) {
            $order = ($highlight->items()->max('order') ?? 0) + 1;
            StoryHighlightItem::create([
                'story_highlight_id' => $highlight->id,
                'story_id' => $story->id,
                'order' => $order,
            ]);
        }
        return response()->json(['status' => 'ok']);
    }

    public function analytics(Request $request)
    {
        $userId = $request->user()->id;
        $stories = Story::where('user_id', $userId)->get();
        $summary = [
            'total_stories' => $stories->count(),
            'total_views' => $stories->sum('views_count'),
            'total_reactions' => $stories->sum('reactions_count'),
        ];
        return response()->json([
            'summary' => $summary,
            'stories' => $stories->map(function ($s) {
                return [
                    'id' => $s->id,
                    'views' => $s->views_count,
                    'reactions' => $s->reactions_count,
                    'created_at' => $s->created_at,
                ];
            }),
        ]);
    }

    protected function processStoryMentions(Story $story, array $mentionUsernames): void
    {
        foreach ($mentionUsernames as $username) {
            $user = \App\Models\User::where('name', $username)
                ->orWhere('email', $username)
                ->first();
            if ($user && $user->id !== $story->user_id) {
                \App\Models\StoryMention::create([
                    'story_id' => $story->id,
                    'user_id' => $user->id,
                ]);
                $user->notify(new \App\Notifications\MentionedInStoryNotification($story));
            }
        }
    }

    protected function syncStoryHashtags(Story $story, array $hashtagNames, HashtagService $hashtagService): void
    {
        $normalized = array_map([$hashtagService, 'normalizeHashtag'], $hashtagNames);
        $normalized = array_unique($normalized);
        $ids = [];
        foreach ($normalized as $name) {
            if (empty($name)) {
                continue;
            }
            $hashtag = \App\Models\Hashtag::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($name)],
                ['name' => $name]
            );
            $ids[] = $hashtag->id;
        }
        $story->hashtags()->sync($ids);
    }
}
