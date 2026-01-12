<?php

namespace App\Services;

use App\Models\ProductReview;
use App\Models\ProductReviewMedia;
use App\Models\ProductReviewVote;
use App\Models\ProductReviewReply;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ReviewService
{
    protected ModerationService $moderationService;

    public function __construct(ModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    /**
     * Create a new review with optional media files.
     */
    public function createReview(array $data, array $mediaFiles = []): ProductReview
    {
        return DB::transaction(function () use ($data, $mediaFiles) {
            // Verify purchase if order_id is provided
            if (isset($data['order_id'])) {
                $order = Order::find($data['order_id']);
                if ($order && $order->status === 'completed' && $order->payment_status === 'paid') {
                    $data['is_verified_purchase'] = true;
                }
            }

            // Check for spam
            $review = new ProductReview($data);
            if ($this->moderationService->shouldAutoModerateReview($data['comment'] ?? '')) {
                $data['status'] = 'moderated';
            } else {
                $data['status'] = 'active';
            }

            $review = ProductReview::create($data);

            // Upload media files
            if (!empty($mediaFiles)) {
                $this->uploadMedia($review, $mediaFiles);
            }

            // Auto-moderate if needed
            if ($review->status === 'moderated') {
                $this->moderationService->moderateReview($review, null, 'hide');
            }

            return $review->fresh(['media', 'user', 'product']);
        });
    }

    /**
     * Update an existing review.
     */
    public function updateReview(ProductReview $review, array $data, array $mediaFiles = []): ProductReview
    {
        if ($review->isLocked()) {
            throw new \Exception('Review is locked and cannot be edited.');
        }

        return DB::transaction(function () use ($review, $data, $mediaFiles) {
            // Check for spam on update
            if (isset($data['comment']) && $this->moderationService->shouldAutoModerateReview($data['comment'])) {
                $data['status'] = 'moderated';
            }

            $review->update($data);

            // Trigger seller rating recalculation on update
            try {
                $sellerRatingService = app(\App\Services\SellerRatingService::class);
                $sellerRatingService->updateSellerRating($review->product->seller);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to update seller rating after review update', [
                    'review_id' => $review->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Upload new media files
            if (!empty($mediaFiles)) {
                $this->uploadMedia($review, $mediaFiles);
            }

            // Auto-moderate if needed
            if ($review->status === 'moderated') {
                $this->moderationService->moderateReview($review, null, 'hide');
            }

            return $review->fresh(['media', 'user', 'product']);
        });
    }

    /**
     * Delete a review.
     */
    public function deleteReview(ProductReview $review): bool
    {
        return DB::transaction(function () use ($review) {
            // Delete associated media files
            foreach ($review->media as $media) {
                Storage::disk('public')->delete($media->file_path);
                $media->delete();
            }

            // Delete votes
            $review->votes()->delete();

            // Delete reply if exists
            if ($review->reply) {
                $review->reply->delete();
            }

            return $review->delete();
        });
    }

    /**
     * Vote helpful/not helpful on a review.
     */
    public function voteHelpful(ProductReview $review, User $user, bool $helpful = true): ProductReviewVote
    {
        return DB::transaction(function () use ($review, $user, $helpful) {
            // Check if user already voted
            $existingVote = ProductReviewVote::where('user_id', $user->id)
                ->where('product_review_id', $review->id)
                ->first();

            if ($existingVote) {
                // Update existing vote
                $wasHelpful = $existingVote->vote_type === 'helpful';
                $existingVote->update(['vote_type' => $helpful ? 'helpful' : 'not_helpful']);

                // Update count if vote type changed
                if ($wasHelpful !== $helpful) {
                    if ($helpful) {
                        $review->increment('helpful_count');
                    } else {
                        $review->decrement('helpful_count');
                    }
                }

                return $existingVote->fresh();
            }

            // Create new vote
            $vote = ProductReviewVote::create([
                'user_id' => $user->id,
                'product_review_id' => $review->id,
                'vote_type' => $helpful ? 'helpful' : 'not_helpful',
            ]);

            // Update helpful count
            if ($helpful) {
                $review->increment('helpful_count');
            }

            return $vote;
        });
    }

    /**
     * Remove a vote from a review.
     */
    public function removeVote(ProductReview $review, User $user): bool
    {
        return DB::transaction(function () use ($review, $user) {
            $vote = ProductReviewVote::where('user_id', $user->id)
                ->where('product_review_id', $review->id)
                ->first();

            if ($vote) {
                // Decrement count if it was helpful
                if ($vote->vote_type === 'helpful') {
                    $review->decrement('helpful_count');
                }

                return $vote->delete();
            }

            return false;
        });
    }

    /**
     * Create a seller reply to a review.
     */
    public function createSellerReply(ProductReview $review, User $seller, string $content): ProductReviewReply
    {
        // Verify seller owns the product
        if ($review->product->user_id !== $seller->id) {
            throw new \Exception('Only the product seller can reply to reviews.');
        }

        // Check if reply already exists
        if ($review->reply) {
            throw new \Exception('A reply already exists for this review.');
        }

        return DB::transaction(function () use ($review, $seller, $content) {
            $reply = ProductReviewReply::create([
                'product_review_id' => $review->id,
                'seller_id' => $seller->id,
                'content' => $content,
            ]);

            // Lock the review (handled by model boot method, but ensure it's locked)
            $review->fresh()->lock();

            return $reply->fresh(['seller']);
        });
    }

    /**
     * Verify purchase for a review.
     */
    public function verifyPurchase(ProductReview $review): bool
    {
        if (!$review->order_id) {
            return false;
        }

        $order = $review->order;
        if ($order && $order->status === 'completed' && $order->payment_status === 'paid') {
            $review->update(['is_verified_purchase' => true]);
            return true;
        }

        return false;
    }

    /**
     * Sort reviews by specified criteria.
     */
    public function sortReviews(Builder $query, string $sortBy = 'recent'): Builder
    {
        switch ($sortBy) {
            case 'helpful':
                return $query->orderBy('helpful_count', 'desc')
                    ->orderBy('created_at', 'desc');
            case 'rating':
                return $query->orderBy('rating', 'desc')
                    ->orderBy('created_at', 'desc');
            case 'recent':
            default:
                return $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Upload media files for a review.
     */
    public function uploadMedia(ProductReview $review, array $files): void
    {
        $order = $review->media()->max('order') ?? 0;

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $order++;
                $this->storeMediaFile($review, $file, $order);
            }
        }
    }

    /**
     * Store a single media file.
     */
    protected function storeMediaFile(ProductReview $review, UploadedFile $file, int $order): ProductReviewMedia
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = $review->id . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        $filePath = 'reviews/' . $fileName;

        // Store file
        $file->storeAs('reviews', $fileName, 'public');

        // Create media record
        return ProductReviewMedia::create([
            'product_review_id' => $review->id,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'order' => $order,
        ]);
    }
}

