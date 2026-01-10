<?php

namespace App\Services;

use App\Constants\VotingReasons;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use App\Models\PostVote;
use Illuminate\Support\Collection;

class VoteAnalyticsService
{
    public function __construct(
        private VoteWeightService $voteWeightService
    ) {}

    /**
     * Get vote breakdown by reason for a post.
     */
    public function getPostVoteBreakdown(Post $post): array
    {
        $votes = PostVote::where('post_id', $post->id)
            ->with('user')
            ->get();

        return $this->buildVoteBreakdown($votes);
    }

    /**
     * Get vote breakdown by reason for a comment.
     */
    public function getCommentVoteBreakdown(Comment $comment): array
    {
        $votes = CommentVote::where('comment_id', $comment->id)
            ->with('user')
            ->get();

        return $this->buildVoteBreakdown($votes);
    }

    /**
     * Build vote breakdown from votes collection.
     */
    private function buildVoteBreakdown(Collection $votes): array
    {
        $breakdown = [
            'upvote' => [
                'total' => 0,
                'weighted_total' => 0.0,
                'reasons' => [],
                'no_reason' => 0,
            ],
            'downvote' => [
                'total' => 0,
                'weighted_total' => 0.0,
                'reasons' => [],
                'no_reason' => 0,
            ],
        ];

        // Initialize reason counts
        foreach (VotingReasons::upvoteReasons() as $key => $label) {
            $breakdown['upvote']['reasons'][$key] = [
                'label' => $label,
                'count' => 0,
                'weighted_count' => 0.0,
            ];
        }

        foreach (VotingReasons::downvoteReasons() as $key => $label) {
            $breakdown['downvote']['reasons'][$key] = [
                'label' => $label,
                'count' => 0,
                'weighted_count' => 0.0,
            ];
        }

        foreach ($votes as $vote) {
            $voteType = $vote->vote_type;
            $weight = $vote->user ? $this->voteWeightService->calculateVoteWeight($vote->user) : 1.0;

            $breakdown[$voteType]['total']++;
            $breakdown[$voteType]['weighted_total'] += $weight;

            if ($vote->reason && isset($breakdown[$voteType]['reasons'][$vote->reason])) {
                $breakdown[$voteType]['reasons'][$vote->reason]['count']++;
                $breakdown[$voteType]['reasons'][$vote->reason]['weighted_count'] += $weight;
            } else {
                $breakdown[$voteType]['no_reason']++;
            }
        }

        return $breakdown;
    }

    /**
     * Get list of voters for a post.
     */
    public function getPostVotersList(Post $post, ?string $voteType = null): Collection
    {
        $query = PostVote::where('post_id', $post->id)
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($voteType) {
            $query->where('vote_type', $voteType);
        }

        return $query->get()->map(function ($vote) {
            return $this->formatVoterData($vote);
        });
    }

    /**
     * Get list of voters for a comment.
     */
    public function getCommentVotersList(Comment $comment, ?string $voteType = null): Collection
    {
        $query = CommentVote::where('comment_id', $comment->id)
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($voteType) {
            $query->where('vote_type', $voteType);
        }

        return $query->get()->map(function ($vote) {
            return $this->formatVoterData($vote);
        });
    }

    /**
     * Format voter data for display.
     */
    private function formatVoterData($vote): array
    {
        $user = $vote->user;
        $weight = $user ? $this->voteWeightService->calculateVoteWeight($user) : 1.0;

        return [
            'id' => $vote->id,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->business_name ?? $user->name,
                'avatar_url' => $user->avatar_url,
                'is_verified' => $user->is_verified_mentor ?? false,
            ] : null,
            'vote_type' => $vote->vote_type,
            'reason' => $vote->reason,
            'reason_label' => $vote->reason_label,
            'weight' => $weight,
            'voted_at' => $vote->created_at->toIso8601String(),
        ];
    }

    /**
     * Get weighted breakdown by reason for a post.
     */
    public function getPostWeightedBreakdown(Post $post): array
    {
        $breakdown = $this->getPostVoteBreakdown($post);

        return [
            'upvotes' => [
                'simple' => $breakdown['upvote']['total'],
                'weighted' => $breakdown['upvote']['weighted_total'],
                'reasons' => $breakdown['upvote']['reasons'],
            ],
            'downvotes' => [
                'simple' => $breakdown['downvote']['total'],
                'weighted' => $breakdown['downvote']['weighted_total'],
                'reasons' => $breakdown['downvote']['reasons'],
            ],
        ];
    }

    /**
     * Get weighted breakdown by reason for a comment.
     */
    public function getCommentWeightedBreakdown(Comment $comment): array
    {
        $breakdown = $this->getCommentVoteBreakdown($comment);

        return [
            'upvotes' => [
                'simple' => $breakdown['upvote']['total'],
                'weighted' => $breakdown['upvote']['weighted_total'],
                'reasons' => $breakdown['upvote']['reasons'],
            ],
            'downvotes' => [
                'simple' => $breakdown['downvote']['total'],
                'weighted' => $breakdown['downvote']['weighted_total'],
                'reasons' => $breakdown['downvote']['reasons'],
            ],
        ];
    }

    /**
     * Get summary statistics for a post.
     */
    public function getPostVoteSummary(Post $post): array
    {
        $breakdown = $this->getPostVoteBreakdown($post);

        return [
            'total_votes' => $breakdown['upvote']['total'] + $breakdown['downvote']['total'],
            'upvotes' => $breakdown['upvote']['total'],
            'downvotes' => $breakdown['downvote']['total'],
            'weighted_upvotes' => $breakdown['upvote']['weighted_total'],
            'weighted_downvotes' => $breakdown['downvote']['weighted_total'],
            'net_score' => $breakdown['upvote']['total'] - $breakdown['downvote']['total'],
            'weighted_net_score' => $breakdown['upvote']['weighted_total'] - $breakdown['downvote']['weighted_total'],
            'top_upvote_reason' => $this->getTopReason($breakdown['upvote']['reasons']),
            'top_downvote_reason' => $this->getTopReason($breakdown['downvote']['reasons']),
        ];
    }

    /**
     * Get summary statistics for a comment.
     */
    public function getCommentVoteSummary(Comment $comment): array
    {
        $breakdown = $this->getCommentVoteBreakdown($comment);

        return [
            'total_votes' => $breakdown['upvote']['total'] + $breakdown['downvote']['total'],
            'upvotes' => $breakdown['upvote']['total'],
            'downvotes' => $breakdown['downvote']['total'],
            'weighted_upvotes' => $breakdown['upvote']['weighted_total'],
            'weighted_downvotes' => $breakdown['downvote']['weighted_total'],
            'net_score' => $breakdown['upvote']['total'] - $breakdown['downvote']['total'],
            'weighted_net_score' => $breakdown['upvote']['weighted_total'] - $breakdown['downvote']['weighted_total'],
            'top_upvote_reason' => $this->getTopReason($breakdown['upvote']['reasons']),
            'top_downvote_reason' => $this->getTopReason($breakdown['downvote']['reasons']),
        ];
    }

    /**
     * Get the top reason from reasons array.
     */
    private function getTopReason(array $reasons): ?array
    {
        $topReason = null;
        $maxCount = 0;

        foreach ($reasons as $key => $data) {
            if ($data['count'] > $maxCount) {
                $maxCount = $data['count'];
                $topReason = [
                    'key' => $key,
                    'label' => $data['label'],
                    'count' => $data['count'],
                ];
            }
        }

        return $topReason;
    }
}

