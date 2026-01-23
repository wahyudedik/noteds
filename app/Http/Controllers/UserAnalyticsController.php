<?php

namespace App\Http\Controllers;

use App\Models\CompetitorAccount;
use App\Models\Follow;
use App\Models\Post;
use App\Models\PostAnalytics;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UserAnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        return Inertia::render('Analytics/UserDashboard');
    }

    public function overview(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'metric' => 'nullable|string',
        ]);
        $from = Carbon::parse($request->input('from'))->startOfDay();
        $to = Carbon::parse($request->input('to'))->endOfDay();
        $cacheKey = "user_analytics_overview:{$user->id}:{$from->toDateString()}:{$to->toDateString()}";
        $data = Cache::remember($cacheKey, 600, function () use ($user, $from, $to, $request) {
            $posts = Post::where('user_id', $user->id)->pluck('id');
            $pa = PostAnalytics::whereIn('post_id', $posts)
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->get();
            $byDate = $pa->groupBy('date')->map(function ($g) {
                return [
                    'views' => (int) $g->sum('views_count'),
                    'upvotes' => (int) $g->sum('upvotes_count'),
                    'downvotes' => (int) $g->sum('downvotes_count'),
                    'comments' => (int) $g->sum('comments_count'),
                    'reposts' => (int) $g->sum('reposts_count'),
                ];
            })->sortKeys();
            $engRate = [];
            foreach ($byDate as $d => $m) {
                $engRate[$d] = $m['views'] > 0 ? round((($m['upvotes'] + $m['comments'] + $m['reposts']) / $m['views']) * 100, 2) : 0;
            }
            $followerGrowth = Follow::where('following_id', $user->id)
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
                ->groupBy('d')
                ->orderBy('d', 'asc')
                ->get();
            $bestMetric = $request->input('metric', 'engagement');
            $perPost = $pa->groupBy('post_id')->map(function ($g) {
                return [
                    'views' => (int) $g->sum('views_count'),
                    'engagement' => (int) ($g->sum('upvotes_count') + $g->sum('comments_count') + $g->sum('reposts_count')),
                ];
            });
            $best = $perPost->sortByDesc($bestMetric)->take(10)->map(function ($m, $pid) {
                return ['post_id' => $pid, 'views' => $m['views'], 'engagement' => $m['engagement']];
            })->values();
            return [
                'by_date' => $byDate,
                'engagement_rate' => $engRate,
                'follower_growth' => $followerGrowth,
                'best' => $best,
                'audience' => [
                    'demographics' => [],
                    'locations' => [],
                    'active_hours' => [],
                ],
            ];
        });
        return response()->json(['data' => $data]);
    }

    public function competitors(Request $request)
    {
        $user = $request->user();
        $list = CompetitorAccount::where('user_id', $user->id)->with('competitor')->get();
        $from = Carbon::parse($request->input('from', now()->subDays(30)))->startOfDay();
        $to = Carbon::parse($request->input('to', now()))->endOfDay();
        $compare = [];
        foreach ($list as $c) {
            $growth = Follow::where('following_id', $c->competitor_user_id)
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
                ->groupBy('d')
                ->orderBy('d', 'asc')
                ->get();
            $compare[] = [
                'user_id' => $c->competitor_user_id,
                'name' => $c->competitor?->name,
                'growth' => $growth,
            ];
        }
        return response()->json(['data' => $compare]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'format' => 'nullable|in:csv',
        ]);
        $from = Carbon::parse($request->input('from'))->startOfDay();
        $to = Carbon::parse($request->input('to'))->endOfDay();
        $posts = Post::where('user_id', $user->id)->pluck('id');
        $pa = PostAnalytics::whereIn('post_id', $posts)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date', 'asc')
            ->get();
        $csv = "date,views,upvotes,downvotes,comments,reposts\n";
        foreach ($pa as $row) {
            $csv .= "{$row->date},{$row->views_count},{$row->upvotes_count},{$row->downvotes_count},{$row->comments_count},{$row->reposts_count}\n";
        }
        $filename = 'analytics_' . now()->format('Ymd_His') . '.csv';
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
