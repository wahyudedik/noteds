<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostReportController extends Controller
{
    /**
     * Report a post.
     */
    public function store(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        
        // Prevent users from reporting their own posts
        if ($post->user_id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot report your own post.',
                ], 422);
            }
            return redirect()->back()->with('error', 'You cannot report your own post.');
        }

        if (!$post->canBeViewedBy($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This post is not available.',
                ], 403);
            }

            return redirect()->back()->with('error', 'This post is not available.');
        }

        // Check if user has already reported this post
        $existingReport = PostReport::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReport) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reported this post.',
                ], 422);
            }
            return redirect()->back()->with('error', 'You have already reported this post.');
        }

        $validated = $request->validate([
            'reason' => 'required|in:spam,harassment,inappropriate,copyright,other',
            'description' => 'nullable|string|max:1000',
        ]);

        PostReport::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post reported successfully. Our team will review it shortly.',
            ]);
        }

        return redirect()->back()->with('success', 'Post reported successfully. Our team will review it shortly.');
    }
}

