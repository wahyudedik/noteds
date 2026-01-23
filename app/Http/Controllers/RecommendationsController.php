<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecommendationsController extends Controller
{
    public function feed(Request $request)
    {
        $limit = (int) $request->get('limit', 6);
        $posts = \App\Models\Post::with('user')
            ->where('status', 'active')
            ->orderBy('trending_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'excerpt' => \Illuminate\Support\Str::limit($p->content, 120),
                    'user' => [
                        'id' => $p->user?->id,
                        'name' => $p->user?->business_name ?? $p->user?->name,
                        'avatar_url' => $p->user?->avatar_url,
                    ],
                ];
            });
        return response()->json(['data' => $posts]);
    }

    public function similarUsers(Request $request)
    {
        $limit = (int) $request->get('limit', 6);
        $users = \App\Models\User::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->business_name ?? $u->name,
                    'avatar_url' => $u->avatar_url,
                ];
            });
        return response()->json(['data' => $users]);
    }
}
