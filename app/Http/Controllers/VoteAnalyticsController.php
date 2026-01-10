<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\VoteAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VoteAnalyticsController extends Controller
{
    public function __construct(
        private VoteAnalyticsService $analyticsService
    ) {}

    /**
     * Show vote analytics for a post.
     */
    public function showPostVotes(Request $request, Post $post): Response|JsonResponse
    {
        // Authorization: Only post author can view
        if ($post->user_id !== $request->user()->id) {
            abort(403, 'You can only view analytics for your own posts.');
        }

        $breakdown = $this->analyticsService->getPostVoteBreakdown($post);
        $voters = $this->analyticsService->getPostVotersList($post);
        $summary = $this->analyticsService->getPostVoteSummary($post);

        if ($request->wantsJson()) {
            return response()->json([
                'post' => [
                    'id' => $post->id,
                    'title' => $post->title,
                ],
                'breakdown' => $breakdown,
                'voters' => $voters,
                'summary' => $summary,
            ]);
        }

        return Inertia::render('Votes/PostAnalytics', [
            'post' => $post->only(['id', 'title', 'upvotes_count', 'downvotes_count', 'weighted_upvotes_score', 'weighted_downvotes_score']),
            'breakdown' => $breakdown,
            'voters' => $voters,
            'summary' => $summary,
        ]);
    }

    /**
     * Show vote analytics for a comment.
     */
    public function showCommentVotes(Request $request, Comment $comment): Response|JsonResponse
    {
        // Authorization: Only comment author can view
        if ($comment->user_id !== $request->user()->id) {
            abort(403, 'You can only view analytics for your own comments.');
        }

        $breakdown = $this->analyticsService->getCommentVoteBreakdown($comment);
        $voters = $this->analyticsService->getCommentVotersList($comment);
        $summary = $this->analyticsService->getCommentVoteSummary($comment);

        if ($request->wantsJson()) {
            return response()->json([
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                ],
                'breakdown' => $breakdown,
                'voters' => $voters,
                'summary' => $summary,
            ]);
        }

        return Inertia::render('Votes/CommentAnalytics', [
            'comment' => $comment->only(['id', 'content', 'upvotes_count', 'downvotes_count', 'weighted_upvotes_score', 'weighted_downvotes_score']),
            'post' => $comment->post->only(['id', 'title']),
            'breakdown' => $breakdown,
            'voters' => $voters,
            'summary' => $summary,
        ]);
    }

    /**
     * Get voters list for a post (JSON only).
     */
    public function getPostVoters(Request $request, Post $post): JsonResponse
    {
        // Authorization: Only post author can view
        if ($post->user_id !== $request->user()->id) {
            abort(403, 'You can only view analytics for your own posts.');
        }

        $voteType = $request->query('vote_type');
        $voters = $this->analyticsService->getPostVotersList($post, $voteType);

        return response()->json([
            'voters' => $voters,
        ]);
    }

    /**
     * Get voters list for a comment (JSON only).
     */
    public function getCommentVoters(Request $request, Comment $comment): JsonResponse
    {
        // Authorization: Only comment author can view
        if ($comment->user_id !== $request->user()->id) {
            abort(403, 'You can only view analytics for your own comments.');
        }

        $voteType = $request->query('vote_type');
        $voters = $this->analyticsService->getCommentVotersList($comment, $voteType);

        return response()->json([
            'voters' => $voters,
        ]);
    }
}

