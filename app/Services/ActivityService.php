<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use App\Events\ActivityCreated;
use Illuminate\Database\Eloquent\Model;

class ActivityService
{
    /**
     * Log an activity.
     */
    public function log(string $type, Model $subject, ?User $user = null, array $properties = []): Activity
    {
        $activity = Activity::create([
            'user_id' => $user?->id ?? auth()->id(),
            'type' => $type,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'properties' => $properties,
        ]);

        // Broadcast real-time update
        event(new ActivityCreated($activity));

        return $activity;
    }

    /**
     * Log note created activity.
     */
    public function logNoteCreated(\App\Models\Note $note, User $user): Activity
    {
        return $this->log('note.created', $note, $user, [
            'title' => $note->title,
            'price' => $note->price,
            'is_public' => $note->is_public,
        ]);
    }

    /**
     * Log note purchased activity.
     */
    public function logNotePurchased(\App\Models\Transaction $transaction, User $buyer): Activity
    {
        return $this->log('note.purchased', $transaction->note, $buyer, [
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'note_title' => $transaction->note->title,
        ]);
    }

    /**
     * Log bundle purchased activity.
     */
    public function logBundlePurchased(\App\Models\NoteBundle $bundle, User $buyer, \App\Models\Transaction $transaction): Activity
    {
        return $this->log('bundle.purchased', $bundle, $buyer, [
            'bundle_id' => $bundle->id,
            'bundle_title' => $bundle->title,
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
        ]);
    }

    /**
     * Log gift sent activity.
     */
    public function logGiftSent(\App\Models\GiftNote $giftNote, User $gifter): Activity
    {
        return $this->log('gift.sent', $giftNote->note, $gifter, [
            'gift_id' => $giftNote->id,
            'recipient_id' => $giftNote->recipient_id,
            'note_title' => $giftNote->note->title,
        ]);
    }

    /**
     * Log gift claimed activity.
     */
    public function logGiftClaimed(\App\Models\GiftNote $giftNote, User $recipient): Activity
    {
        return $this->log('gift.claimed', $giftNote->note, $recipient, [
            'gift_id' => $giftNote->id,
            'gifter_id' => $giftNote->gifter_id,
            'note_title' => $giftNote->note->title,
        ]);
    }

    /**
     * Log user followed activity.
     */
    public function logUserFollowed(User $follower, User $following): Activity
    {
        return $this->log('user.followed', $following, $follower, [
            'following_id' => $following->id,
            'following_name' => $following->name,
        ]);
    }

    /**
     * Log review created activity.
     */
    public function logReviewCreated(\App\Models\NoteReview $review, User $user): Activity
    {
        return $this->log('review.created', $review->note, $user, [
            'review_id' => $review->id,
            'rating' => $review->rating,
            'note_title' => $review->note->title,
        ]);
    }

    /**
     * Get activities for a user.
     */
    public function getUserActivities(User $user, int $limit = 20)
    {
        return Activity::where('user_id', $user->id)
            ->with(['subject', 'user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get activities for followed users.
     */
    public function getFollowingActivities(User $user, int $limit = 20)
    {
        $followingIds = $user->following()->pluck('following_id');

        return Activity::whereIn('user_id', $followingIds)
            ->whereIn('type', [
                'note.created',
                'note.purchased',
                'review.created',
                'user.followed',
            ])
            ->with(['subject', 'user'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}

