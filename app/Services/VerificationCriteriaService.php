<?php

namespace App\Services;

use App\Models\VerificationRequest;
use App\Models\Follow;
use App\Models\Post;
use App\Models\PostAnalytics;
use Illuminate\Support\Carbon;

class VerificationCriteriaService
{
    public function getCriteria(): array
    {
        $path = base_path('resources/verification_criteria.json');
        if (!file_exists($path)) return [];
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    public function evaluate(VerificationRequest $request): array
    {
        $criteria = $this->getCriteria();
        $slug = $request->type?->slug ?? null;
        if (!$slug || !isset($criteria[$slug])) return ['recommendation' => false, 'checks' => []];
        $cfg = $criteria[$slug];
        $windowDays = (int)($cfg['window_days'] ?? 30);
        $from = Carbon::now()->subDays($windowDays)->startOfDay();
        $to = Carbon::now()->endOfDay();
        $userId = $request->user_id;
        $followers = Follow::where('following_id', $userId)->count();
        $posts = Post::where('user_id', $userId)->pluck('id');
        $pa = PostAnalytics::whereIn('post_id', $posts)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get();
        $views = (int)$pa->sum('views_count');
        $engagement = (int)($pa->sum('upvotes_count') + $pa->sum('comments_count') + $pa->sum('reposts_count'));
        $er = $views > 0 ? round(($engagement / $views) * 100, 2) : 0.0;
        $checks = [];
        if (isset($cfg['min_followers'])) {
            $checks[] = [
                'key' => 'min_followers',
                'value' => $followers,
                'threshold' => (int)$cfg['min_followers'],
                'pass' => $followers >= (int)$cfg['min_followers'],
                'reason' => $followers >= (int)$cfg['min_followers'] ? 'meets' : 'below',
            ];
        }
        if (isset($cfg['min_engagement_rate'])) {
            $checks[] = [
                'key' => 'min_engagement_rate',
                'value' => $er,
                'threshold' => (float)$cfg['min_engagement_rate'],
                'pass' => $er >= (float)$cfg['min_engagement_rate'],
                'reason' => $er >= (float)$cfg['min_engagement_rate'] ? 'meets' : 'below',
            ];
        }
        $recommend = !in_array(false, array_map(fn($c) => $c['pass'], $checks), true);
        return ['recommendation' => $recommend, 'checks' => $checks, 'window_days' => $windowDays];
    }
}
