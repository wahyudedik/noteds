<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function show(string $username): View|RedirectResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        
        $user->load(['notes' => function ($query) {
            $query->where('is_public', true)
                ->where('status', 'active')
                ->with(['tags', 'reviews'])
                ->latest();
        }]);

        // Get public notes with pagination
        $publicNotes = $user->notes()
            ->where('is_public', true)
            ->where('status', 'active')
            ->with(['tags', 'reviews'])
            ->latest()
            ->paginate(12);

        // Calculate seller statistics
        $stats = [
            'total_notes' => $user->notes()->where('is_public', true)->count(),
            'total_sales' => $user->transactionsAsSeller()->where('status', 'success')->count(),
            'total_revenue' => $user->transactionsAsSeller()->where('status', 'success')->sum('amount'),
            'average_rating' => $this->calculateAverageRating($user),
            'total_reviews' => $this->getTotalReviewsCount($user),
        ];

        return view('public.profile.show', compact('user', 'publicNotes', 'stats'));
    }

    /**
     * Calculate average rating from all notes by this user.
     */
    private function calculateAverageRating(User $user): float
    {
        $notes = $user->notes()->where('is_public', true)->pluck('id');
        
        if ($notes->isEmpty()) {
            return 0;
        }

        $avgRating = \App\Models\NoteReview::whereIn('note_id', $notes)->avg('rating');
        
        return round($avgRating ?? 0, 1);
    }

    /**
     * Get total number of reviews for all notes by this user.
     */
    private function getTotalReviewsCount(User $user): int
    {
        $notes = $user->notes()->where('is_public', true)->pluck('id');
        
        if ($notes->isEmpty()) {
            return 0;
        }

        return \App\Models\NoteReview::whereIn('note_id', $notes)->count();
    }
}
