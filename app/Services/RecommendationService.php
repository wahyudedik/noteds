<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Hashtag;
use App\Models\User;
use App\Models\Follow;
use App\Models\PostAnalytics;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function userInterestVector(User $user, int $days = 90): array
    {
        $cacheKey = "reco:interest:{$user->id}:{$days}";
        return Cache::remember($cacheKey, 600, function () use ($user, $days) {
            $from = Carbon::now()->subDays($days)->startOfDay();
            $posts = Post::where('user_id', $user->id)->pluck('id')->toArray();
            $engagedPostIds = \DB::table('votes')->where('user_id', $user->id)->pluck('post_id')->merge(
                \DB::table('comments')->where('user_id', $user->id)->pluck('post_id')
            )->merge(
                \DB::table('bookmarks')->where('user_id', $user->id)->pluck('post_id')
            )->filter()->unique()->values()->toArray();
            $ids = array_unique(array_merge($posts, $engagedPostIds));
            if (empty($ids)) return [];
            $rows = \DB::table('post_hashtag')->whereIn('post_id', $ids)->select('hashtag_id')->get();
            $counts = [];
            foreach ($rows as $r) {
                $counts[$r->hashtag_id] = ($counts[$r->hashtag_id] ?? 0) + 1;
            }
            arsort($counts);
            return $counts;
        });
    }

    public function feed(User $user, int $limit = 30): array
    {
        $interests = $this->userInterestVector($user);
        $interestIds = array_keys($interests);
        $candidatePosts = Post::query()
            ->where('user_id', '!=', $user->id)
            ->whereNotIn('user_id', Follow::where('user_id', $user->id)->pluck('following_id'))
            ->latest()
            ->limit(300)
            ->get();
        $scores = [];
        foreach ($candidatePosts as $post) {
            $postTags = \DB::table('post_hashtag')->where('post_id', $post->id)->pluck('hashtag_id')->toArray();
            $overlap = array_intersect($postTags, $interestIds);
            $tagScore = 0;
            foreach ($overlap as $hid) {
                $tagScore += ($interests[$hid] ?? 0);
            }
            $pa = PostAnalytics::where('post_id', $post->id)->orderBy('date', 'desc')->limit(7)->get();
            $views = (int)$pa->sum('views_count');
            $eng = (int)($pa->sum('upvotes_count') + $pa->sum('comments_count') + $pa->sum('reposts_count'));
            $engRate = $views > 0 ? ($eng / $views) : 0;
            $recencyBoost = max(0, 1 - Carbon::parse($post->created_at)->diffInDays(now()) / 30);
            $scores[$post->id] = $tagScore * 1.0 + $engRate * 10.0 + $recencyBoost * 5.0;
        }
        arsort($scores);
        $topIds = array_slice(array_keys($scores), 0, $limit);
        $ordered = $candidatePosts->whereIn('id', $topIds)->sortBy(function ($p) use ($scores) {
            return -$scores[$p->id];
        });
        return $ordered->values()->map(fn($p) => $this->serializePost($p))->toArray();
    }

    public function relatedPosts(Post $post, int $limit = 10): array
    {
        $tags = \DB::table('post_hashtag')->where('post_id', $post->id)->pluck('hashtag_id')->toArray();
        if (empty($tags)) {
            $candidates = Post::where('id', '!=', $post->id)->latest()->limit($limit)->get();
            return $candidates->map(fn($p) => $this->serializePost($p))->toArray();
        }
        $candidateIds = \DB::table('post_hashtag')->whereIn('hashtag_id', $tags)->pluck('post_id')->toArray();
        $candidateIds = array_unique(array_diff($candidateIds, [$post->id]));
        $candidates = Post::whereIn('id', $candidateIds)->latest()->limit(200)->get();
        $scores = [];
        foreach ($candidates as $p) {
            $ptags = \DB::table('post_hashtag')->where('post_id', $p->id)->pluck('hashtag_id')->toArray();
            $overlap = count(array_intersect($tags, $ptags));
            $pa = PostAnalytics::where('post_id', $p->id)->orderBy('date', 'desc')->limit(7)->get();
            $eng = (int)($pa->sum('upvotes_count') + $pa->sum('comments_count') + $pa->sum('reposts_count'));
            $scores[$p->id] = $overlap * 3 + $eng;
        }
        arsort($scores);
        $top = array_slice(array_keys($scores), 0, $limit);
        $ordered = $candidates->whereIn('id', $top)->sortBy(function ($p) use ($scores) {
            return -$scores[$p->id];
        });
        return $ordered->values()->map(fn($p) => $this->serializePost($p))->toArray();
    }

    public function similarUsers(User $user, int $limit = 10): array
    {
        $interests = array_keys($this->userInterestVector($user));
        if (empty($interests)) return [];
        $others = User::where('id', '!=', $user->id)->limit(500)->get();
        $scores = [];
        foreach ($others as $u) {
            $uTags = \DB::table('post_hashtag')->whereIn('post_id', Post::where('user_id', $u->id)->pluck('id'))->pluck('hashtag_id')->toArray();
            $overlap = count(array_intersect($interests, $uTags));
            $followers = Follow::where('following_id', $u->id)->count();
            $scores[$u->id] = $overlap * 2 + min($followers, 500) / 100.0;
        }
        arsort($scores);
        $topIds = array_slice(array_keys($scores), 0, $limit);
        $topUsers = $others->whereIn('id', $topIds)->sortBy(function ($u) use ($scores) {
            return -$scores[$u->id];
        });
        return $topUsers->values()->map(function ($u) {
            return ['id' => $u->id, 'name' => $u->business_name ?? $u->name, 'avatar_url' => $u->avatar_url];
        })->toArray();
    }

    public function trending(int $limit = 20, int $windowDays = 7): array
    {
        $windowDays = max(1, min($windowDays, 90));
        $cacheKey = "reco:trending:{$windowDays}";
        return Cache::remember($cacheKey, 120, function () use ($limit, $windowDays) {
            $from = Carbon::now()->subDays($windowDays)->toDateString();
            $pa = PostAnalytics::where('date', '>=', $from)->get()->groupBy('post_id');
            $scores = [];
            foreach ($pa as $pid => $rows) {
                $views = (int)collect($rows)->sum('views_count');
                $eng = (int)(collect($rows)->sum('upvotes_count') + collect($rows)->sum('comments_count') + collect($rows)->sum('reposts_count'));
                $scores[$pid] = $views * 0.5 + $eng * 1.5;
            }
            arsort($scores);
            $topIds = array_slice(array_keys($scores), 0, $limit);
            $posts = Post::whereIn('id', $topIds)->get()->sortBy(function ($p) use ($scores) {
                return -$scores[$p->id];
            });
            return $posts->values()->map(fn($p) => $this->serializePost($p))->toArray();
        });
    }

    protected function serializePost(Post $p): array
    {
        return [
            'id' => $p->id,
            'title' => $p->title,
            'excerpt' => \Str::limit(strip_tags($p->content ?? ''), 140),
            'author' => ['id' => $p->user_id],
            'created_at' => $p->created_at,
        ];
    }
}
