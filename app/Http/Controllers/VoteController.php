<?php

namespace App\Http\Controllers;

use App\Constants\VotingReasons;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use App\Models\PostVote;
use App\Services\VoteWeightService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VoteController extends Controller
{
    public function __construct(
        private VoteWeightService $voteWeightService
    ) {}

    public function votePost(Request $request, Post $post): RedirectResponse
    {
        $voteType = $request->input('vote_type');

        $request->validate([
            'vote_type' => 'required|in:upvote,downvote',
            'reason' => [
                'nullable',
                'string',
                Rule::in($voteType === 'upvote' ? VotingReasons::upvoteKeys() : VotingReasons::downvoteKeys()),
            ],
        ]);

        // Prevent users from voting on their own posts
        if ($post->user_id === $request->user()->id) {
            return back()->withErrors(['vote' => 'You cannot vote on your own post.']);
        }

        // Check if post is active
        if ($post->status !== 'active') {
            return back()->withErrors(['vote' => 'You cannot vote on this post.']);
        }

        DB::transaction(function () use ($request, $post) {
            // Get existing vote to check if user is switching vote types
            $existingVote = PostVote::where('user_id', $request->user()->id)
                ->where('post_id', $post->id)
                ->first();

            $oldVoteType = $existingVote?->vote_type;
            $newVoteType = $request->vote_type;

            // Update or create vote
            PostVote::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'post_id' => $post->id,
                ],
                [
                    'vote_type' => $newVoteType,
                    'reason' => $request->reason,
                ]
            );

            // Update vote counts using database increments/decrements to avoid race conditions
            // If switching from upvote to downvote (or vice versa), decrement old, increment new
            if ($oldVoteType && $oldVoteType !== $newVoteType) {
                // Decrement old vote type
                if ($oldVoteType === 'upvote') {
                    $post->decrement('upvotes_count');
                } else {
                    $post->decrement('downvotes_count');
                }
                // Increment new vote type
                if ($newVoteType === 'upvote') {
                    $post->increment('upvotes_count');
                } else {
                    $post->increment('downvotes_count');
                }
            } elseif (!$oldVoteType) {
                // New vote - just increment
                if ($newVoteType === 'upvote') {
                    $post->increment('upvotes_count');
                } else {
                    $post->increment('downvotes_count');
                }
            }
            // If same vote type, do nothing (just reason might have changed)

            // Refresh post to get updated counts before calculating weighted scores
            $post->refresh();

            // Update weighted scores inside transaction to ensure consistency
            // If weighted score calculation fails, the entire transaction will roll back
            $this->voteWeightService->updatePostWeightedScores($post);

            if ($newVoteType === 'upvote' && ($oldVoteType === null || $oldVoteType === 'downvote')) {
                app(\App\Services\GamificationService::class)->awardAction($request->user(), 'upvote', [
                    'post_id' => $post->id,
                ]);
            }
        });

        return back();
    }

    public function voteComment(Request $request, Comment $comment): RedirectResponse
    {
        $voteType = $request->input('vote_type');

        $request->validate([
            'vote_type' => 'required|in:upvote,downvote',
            'reason' => [
                'nullable',
                'string',
                Rule::in($voteType === 'upvote' ? VotingReasons::upvoteKeys() : VotingReasons::downvoteKeys()),
            ],
        ]);

        // Prevent users from voting on their own comments
        if ($comment->user_id === $request->user()->id) {
            return back()->withErrors(['vote' => 'You cannot vote on your own comment.']);
        }

        // Check if comment's post is active
        if ($comment->post->status !== 'active') {
            return back()->withErrors(['vote' => 'You cannot vote on this comment.']);
        }

        DB::transaction(function () use ($request, $comment) {
            // Get existing vote to check if user is switching vote types
            $existingVote = CommentVote::where('user_id', $request->user()->id)
                ->where('comment_id', $comment->id)
                ->first();

            $oldVoteType = $existingVote?->vote_type;
            $newVoteType = $request->vote_type;

            // Update or create vote
            CommentVote::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'comment_id' => $comment->id,
                ],
                [
                    'vote_type' => $newVoteType,
                    'reason' => $request->reason,
                ]
            );

            // Update vote counts using database increments/decrements to avoid race conditions
            // If switching from upvote to downvote (or vice versa), decrement old, increment new
            if ($oldVoteType && $oldVoteType !== $newVoteType) {
                // Decrement old vote type
                if ($oldVoteType === 'upvote') {
                    $comment->decrement('upvotes_count');
                } else {
                    $comment->decrement('downvotes_count');
                }
                // Increment new vote type
                if ($newVoteType === 'upvote') {
                    $comment->increment('upvotes_count');
                } else {
                    $comment->increment('downvotes_count');
                }
            } elseif (!$oldVoteType) {
                // New vote - just increment
                if ($newVoteType === 'upvote') {
                    $comment->increment('upvotes_count');
                } else {
                    $comment->increment('downvotes_count');
                }
            }
            // If same vote type, do nothing (just reason might have changed)

            // Refresh comment to get updated counts before calculating weighted scores
            $comment->refresh();

            // Update weighted scores inside transaction to ensure consistency
            // If weighted score calculation fails, the entire transaction will roll back
            $this->voteWeightService->updateCommentWeightedScores($comment);

            if ($newVoteType === 'upvote' && ($oldVoteType === null || $oldVoteType === 'downvote')) {
                app(\App\Services\GamificationService::class)->awardAction($request->user(), 'upvote', [
                    'comment_id' => $comment->id,
                ]);
            }
        });

        return back();
    }

    /**
     * Get voting reasons for frontend.
     */
    public function getReasons(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'upvote' => VotingReasons::upvoteReasons(),
            'downvote' => VotingReasons::downvoteReasons(),
        ]);
    }
}
