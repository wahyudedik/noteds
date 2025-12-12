<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function show(string $username): View|RedirectResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        $viewer = auth()->user();

        // Load relationships (but don't use load with closure to avoid Collection conversion issues)
        $user->load(['badges', 'userLevels.level', 'approvedCertifications.certification']);

        // Get public notes with pagination (use fresh query, not loaded relationship)
        $publicNotes = $user->notes()
            ->where('is_public', true)
            ->where('status', 'active')
            ->with(['tags', 'reviews'])
            ->latest()
            ->paginate(12);

        // Calculate seller statistics (optimized: 3 queries → 1 aggregation)
        $notesStats = \DB::table('notes')
            ->selectRaw('COUNT(*) as total_notes')
            ->where('user_id', $user->id)
            ->where('is_public', true)
            ->first();

        $salesStats = \DB::table('transactions')
            ->selectRaw('COUNT(*) as total_sales, SUM(amount) as total_revenue')
            ->where('seller_id', $user->id)
            ->where('status', 'success')
            ->first();

        $stats = [
            'total_notes' => $notesStats->total_notes ?? 0,
            'total_sales' => $salesStats->total_sales ?? 0,
            'total_revenue' => $salesStats->total_revenue ?? 0,
            'average_rating' => $this->calculateAverageRating($user),
            'total_reviews' => $this->getTotalReviewsCount($user),
            'total_posts' => $user->posts()->whereNull('parent_id')->where('is_hidden', false)->published()->visibleTo($viewer)->count(),
        ];

        // Get user posts for posts tab
        $userPosts = $user->posts()
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->published()
            ->visibleTo($viewer)
            ->with(['note', 'likes'])
            ->withCount(['replies', 'allComments'])
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(12);

        return view('public.profile.show', compact('user', 'publicNotes', 'stats', 'userPosts'));
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
