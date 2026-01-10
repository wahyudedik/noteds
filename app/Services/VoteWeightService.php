<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use App\Models\PostVote;
use App\Models\User;

class VoteWeightService
{
    /**
     * Weight multiplier for verified users.
     */
    public const VERIFIED_USER_WEIGHT = 2.0;

    /**
     * Default weight for regular users.
     */
    public const DEFAULT_WEIGHT = 1.0;

    /**
     * Calculate the vote weight for a user.
     */
    public function calculateVoteWeight(User $user): float
    {
        // Check if user is verified (using is_verified_mentor field)
        if ($user->is_verified_mentor) {
            return self::VERIFIED_USER_WEIGHT;
        }

        return self::DEFAULT_WEIGHT;
    }

    /**
     * Update weighted scores for a post.
     */
    public function updatePostWeightedScores(Post $post): void
    {
        $weightedUpvotes = $this->calculateWeightedScore($post, 'upvote');
        $weightedDownvotes = $this->calculateWeightedScore($post, 'downvote');

        $post->weighted_upvotes_score = $weightedUpvotes;
        $post->weighted_downvotes_score = $weightedDownvotes;
        $post->save();
    }

    /**
     * Update weighted scores for a comment.
     */
    public function updateCommentWeightedScores(Comment $comment): void
    {
        $weightedUpvotes = $this->calculateWeightedScoreForComment($comment, 'upvote');
        $weightedDownvotes = $this->calculateWeightedScoreForComment($comment, 'downvote');

        $comment->weighted_upvotes_score = $weightedUpvotes;
        $comment->weighted_downvotes_score = $weightedDownvotes;
        $comment->save();
    }

    /**
     * Calculate weighted score for a post.
     */
    private function calculateWeightedScore(Post $post, string $voteType): float
    {
        $votes = PostVote::where('post_id', $post->id)
            ->where('vote_type', $voteType)
            ->with('user')
            ->get();

        $weightedScore = 0.0;

        foreach ($votes as $vote) {
            if ($vote->user) {
                $weightedScore += $this->calculateVoteWeight($vote->user);
            } else {
                $weightedScore += self::DEFAULT_WEIGHT;
            }
        }

        return $weightedScore;
    }

    /**
     * Calculate weighted score for a comment.
     */
    private function calculateWeightedScoreForComment(Comment $comment, string $voteType): float
    {
        $votes = CommentVote::where('comment_id', $comment->id)
            ->where('vote_type', $voteType)
            ->with('user')
            ->get();

        $weightedScore = 0.0;

        foreach ($votes as $vote) {
            if ($vote->user) {
                $weightedScore += $this->calculateVoteWeight($vote->user);
            } else {
                $weightedScore += self::DEFAULT_WEIGHT;
            }
        }

        return $weightedScore;
    }

    /**
     * Get weighted upvotes for a post.
     */
    public function getWeightedUpvotes(Post $post): float
    {
        return $post->weighted_upvotes_score ?? 0.0;
    }

    /**
     * Get weighted downvotes for a post.
     */
    public function getWeightedDownvotes(Post $post): float
    {
        return $post->weighted_downvotes_score ?? 0.0;
    }

    /**
     * Get weighted upvotes for a comment.
     */
    public function getCommentWeightedUpvotes(Comment $comment): float
    {
        return $comment->weighted_upvotes_score ?? 0.0;
    }

    /**
     * Get weighted downvotes for a comment.
     */
    public function getCommentWeightedDownvotes(Comment $comment): float
    {
        return $comment->weighted_downvotes_score ?? 0.0;
    }

    /**
     * Get weighted net score (upvotes - downvotes) for a post.
     */
    public function getWeightedNetScore(Post $post): float
    {
        return $this->getWeightedUpvotes($post) - $this->getWeightedDownvotes($post);
    }

    /**
     * Get weighted net score (upvotes - downvotes) for a comment.
     */
    public function getCommentWeightedNetScore(Comment $comment): float
    {
        return $this->getCommentWeightedUpvotes($comment) - $this->getCommentWeightedDownvotes($comment);
    }
}

