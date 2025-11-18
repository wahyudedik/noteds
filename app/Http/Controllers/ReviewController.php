<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Note;
use App\Models\NoteReview;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Note $note): RedirectResponse
    {
        // Check if user has purchased this note
        $hasPurchased = Transaction::where('buyer_id', auth()->id())
            ->where('note_id', $note->id)
            ->where('status', 'success')
            ->exists();

        if (!$hasPurchased) {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'You must purchase this note before leaving a review.');
        }

        // Check if user has already reviewed this note
        $existingReview = NoteReview::where('user_id', auth()->id())
            ->where('note_id', $note->id)
            ->first();

        if ($existingReview) {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'You have already reviewed this note.');
        }

        // Create review
        $review = NoteReview::create([
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Award points for review
        try {
            $pointsService = app(\App\Services\PointsService::class);
            $pointsService->awardReviewPoints(auth()->user(), $review);
        } catch (\Exception $e) {
            logger()->error('Failed to award review points', [
                'user_id' => auth()->id(),
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('marketplace.show', $note)
            ->with('success', 'Thank you for your review!');
    }

    public function update(StoreReviewRequest $request, NoteReview $review): RedirectResponse
    {
        // Ensure user owns this review
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('marketplace.show', $review->note)
            ->with('success', 'Review updated successfully.');
    }

    public function destroy(NoteReview $review): RedirectResponse
    {
        // Ensure user owns this review
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $note = $review->note;
        $review->delete();

        return redirect()->route('marketplace.show', $note)
            ->with('success', 'Review deleted successfully.');
    }
}
