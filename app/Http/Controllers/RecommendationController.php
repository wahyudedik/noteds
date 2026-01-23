<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function feed(Request $request, RecommendationService $service)
    {
        $user = $request->user();
        $limit = (int)($request->input('limit', 30));
        $data = $service->feed($user, $limit);
        return response()->json(['data' => $data]);
    }

    public function related(Request $request, Post $post, RecommendationService $service)
    {
        $limit = (int)($request->input('limit', 10));
        $data = $service->relatedPosts($post, $limit);
        return response()->json(['data' => $data]);
    }

    public function similarUsers(Request $request, RecommendationService $service)
    {
        $user = $request->user();
        $limit = (int)($request->input('limit', 10));
        $data = $service->similarUsers($user, $limit);
        return response()->json(['data' => $data]);
    }

    public function trending(Request $request, RecommendationService $service)
    {
        $limit = (int)($request->input('limit', 20));
        $data = $service->trending($limit);
        return response()->json(['data' => $data]);
    }
}
