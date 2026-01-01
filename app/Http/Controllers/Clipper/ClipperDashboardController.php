<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClipperDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isBrand = $user->isBrand();
        $isClipper = $user->isClipper();

        $stats = [];
        $recentActivity = [];

        if ($isBrand) {
            // Brand stats
            $campaigns = $user->campaigns();
            $creatorWallet = $user->creatorWallet;

            $stats = [
                'total_campaigns' => $campaigns->count(),
                'active_campaigns' => $campaigns->where('status', 'active')->count(),
                'total_spent' => $campaigns->sum('total_spent') ?? 0,
                'total_views' => $campaigns->sum('total_views') ?? 0,
                'available_balance' => $creatorWallet?->balance_available ?? 0,
                'locked_balance' => $creatorWallet?->balance_locked ?? 0,
            ];

            // Recent activity: recent campaigns and clips
            $recentCampaigns = $campaigns->latest()->limit(5)->get();
            $recentClips = \App\Models\Clip::whereHas('campaign', function ($query) use ($user) {
                $query->where('creator_id', $user->id);
            })->latest()->limit(5)->get();

            $recentActivity = collect()
                ->merge($recentCampaigns->map(function ($campaign) {
                    return [
                        'type' => 'campaign',
                        'id' => $campaign->id,
                        'title' => $campaign->title,
                        'status' => $campaign->status,
                        'created_at' => $campaign->created_at,
                    ];
                }))
                ->merge($recentClips->map(function ($clip) {
                    return [
                        'type' => 'clip',
                        'id' => $clip->id,
                        'title' => 'Clip #' . substr($clip->id, 0, 8),
                        'status' => $clip->status,
                        'campaign_title' => $clip->campaign?->title,
                        'created_at' => $clip->created_at,
                    ];
                }))
                ->sortByDesc('created_at')
                ->take(10)
                ->values();
        } elseif ($isClipper) {
            // Clipper stats
            $clips = $user->clips();

            $stats = [
                'total_clips' => $clips->count(),
                'pending_clips' => $clips->where('status', 'pending')->count(),
                'approved_clips' => $clips->where('status', 'approved')->count(),
                'paid_clips' => $clips->where('status', 'paid')->count(),
                'total_earnings' => $clips->where('status', 'paid')->sum('approved_reward') ?? 0,
                'pending_earnings' => $clips->where('status', 'approved')->sum('approved_reward') ?? 0,
                'total_views' => $clips->sum('valid_views') ?? 0,
            ];

            $clipperWallet = $user->clipperWallet;
            if ($clipperWallet) {
                $stats['wallet_balance'] = $clipperWallet->balance ?? 0;
            }

            // Recent activity: recent clips
            $recentClips = $clips->with('campaign')->latest()->limit(10)->get();
            $recentActivity = $recentClips->map(function ($clip) {
                return [
                    'type' => 'clip',
                    'id' => $clip->id,
                    'title' => 'Clip #' . substr($clip->id, 0, 8),
                    'status' => $clip->status,
                    'campaign_title' => $clip->campaign?->title,
                    'reward' => $clip->approved_reward ?? $clip->pending_reward ?? 0,
                    'views' => $clip->valid_views ?? 0,
                    'created_at' => $clip->created_at,
                ];
            });
        }

        return Inertia::render('Clipper/Dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'role' => $isBrand ? 'brand' : ($isClipper ? 'clipper' : null),
        ]);
    }
}

