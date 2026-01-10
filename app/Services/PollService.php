<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PollService
{
    /**
     * Create a poll for a post.
     */
    public function createPoll(Post $post, string $question, array $options, ?\DateTime $endsAt = null): Poll
    {
        return DB::transaction(function () use ($post, $question, $options, $endsAt) {
            $poll = Poll::create([
                'post_id' => $post->id,
                'question' => $question,
                'ends_at' => $endsAt,
                'votes_count' => 0,
            ]);

            foreach ($options as $index => $optionText) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'option_text' => $optionText,
                    'votes_count' => 0,
                    'order' => $index,
                ]);
            }

            return $poll;
        });
    }

    /**
     * Vote on a poll option.
     */
    public function vote(string $pollId, string $optionId, string $userId): bool
    {
        $poll = Poll::findOrFail($pollId);

        if ($poll->isExpired()) {
            return false;
        }

        // Check if user already voted
        $existingVote = PollVote::where('poll_id', $pollId)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            // Update existing vote
            $oldOption = PollOption::find($existingVote->poll_option_id);
            if ($oldOption) {
                $oldOption->decrement('votes_count');
            }

            $existingVote->poll_option_id = $optionId;
            $existingVote->save();
        } else {
            // Create new vote
            PollVote::create([
                'poll_id' => $pollId,
                'poll_option_id' => $optionId,
                'user_id' => $userId,
            ]);
        }

        // Update option votes count
        $option = PollOption::findOrFail($optionId);
        $option->increment('votes_count');

        // Update poll votes count
        $poll->votes_count = $poll->votes()->count();
        $poll->save();

        return true;
    }

    /**
     * Get poll results.
     */
    public function getResults(string $pollId): array
    {
        $poll = Poll::with('options')->findOrFail($pollId);
        $totalVotes = $poll->votes_count;

        $results = $poll->options->map(function ($option) use ($totalVotes) {
            return [
                'id' => $option->id,
                'text' => $option->option_text,
                'votes' => $option->votes_count,
                'percentage' => $totalVotes > 0 ? round(($option->votes_count / $totalVotes) * 100, 2) : 0,
            ];
        })->toArray();

        return [
            'poll' => $poll,
            'results' => $results,
            'total_votes' => $totalVotes,
        ];
    }

    /**
     * Check if user has voted on poll.
     */
    public function hasUserVoted(string $pollId, string $userId): bool
    {
        return PollVote::where('poll_id', $pollId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get user's vote for a poll.
     */
    public function getUserVote(string $pollId, string $userId): ?PollVote
    {
        return PollVote::where('poll_id', $pollId)
            ->where('user_id', $userId)
            ->first();
    }
}

