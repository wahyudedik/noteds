<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\NoteReview;
use App\Models\NoteShareReferral;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaderboardService
{
    /**
     * Get top sellers by revenue.
     */
    public function getTopSellersByRevenue(?string $period = 'all-time', int $limit = 50): array
    {
        $query = Transaction::select('seller_id', DB::raw('SUM(amount) as total_revenue'))
            ->where('status', 'success')
            ->groupBy('seller_id')
            ->with('seller:id,name,username,avatar')
            ->orderByDesc('total_revenue');

        if ($period !== 'all-time') {
            $dateFilter = $this->getDateFilter($period);
            $query->whereBetween('created_at', $dateFilter);
        }

        $results = $query->limit($limit)->get();

        return $results->map(function ($item, $index) {
            $seller = $item->seller;
            return [
                'rank' => $index + 1,
                'id' => $seller?->id,
                'name' => $seller?->name,
                'username' => $seller?->username,
                'avatar' => $seller?->avatar,
                'total_revenue' => (float) $item->total_revenue,
            ];
        })->toArray();
    }

    /**
     * Get top sellers by sales count.
     */
    public function getTopSellersBySalesCount(?string $period = 'all-time', int $limit = 50): array
    {
        $query = Transaction::select('seller_id', DB::raw('COUNT(*) as sales_count'))
            ->where('status', 'success')
            ->groupBy('seller_id')
            ->with('seller:id,name,username,avatar')
            ->orderByDesc('sales_count');

        if ($period !== 'all-time') {
            $dateFilter = $this->getDateFilter($period);
            $query->whereBetween('created_at', $dateFilter);
        }

        $results = $query->limit($limit)->get();

        return $results->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'user' => $item->seller,
                'sales_count' => (int) $item->sales_count,
            ];
        })->toArray();
    }

    /**
     * Get top sellers by ratings.
     */
    public function getTopSellersByRatings(?string $period = 'all-time', int $limit = 50): array
    {
        // Get sellers with their average ratings
        $query = DB::table('notes')
            ->join('note_reviews', 'notes.id', '=', 'note_reviews.note_id')
            ->join('users', 'notes.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.username',
                'users.avatar',
                DB::raw('AVG(note_reviews.rating) as average_rating'),
                DB::raw('COUNT(note_reviews.id) as review_count')
            )
            ->where('notes.is_public', true)
            ->groupBy('users.id', 'users.name', 'users.username', 'users.avatar')
            ->having('review_count', '>=', 5) // Minimum 5 reviews
            ->orderByDesc('average_rating')
            ->orderByDesc('review_count');

        if ($period !== 'all-time') {
            $dateFilter = $this->getDateFilter($period);
            $query->whereBetween('note_reviews.created_at', $dateFilter);
        }

        $results = $query->limit($limit)->get();

        return $results->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'id' => $item->id,
                'name' => $item->name,
                'username' => $item->username,
                'avatar' => $item->avatar,
                'average_rating' => round((float) $item->average_rating, 2),
                'review_count' => (int) $item->review_count,
            ];
        })->toArray();
    }

    /**
     * Get top buyers by purchase count.
     */
    public function getTopBuyersByPurchaseCount(?string $period = 'all-time', int $limit = 50): array
    {
        $query = Transaction::select('buyer_id', DB::raw('COUNT(*) as purchase_count'))
            ->where('status', 'success')
            ->groupBy('buyer_id')
            ->with('buyer:id,name,username,avatar')
            ->orderByDesc('purchase_count');

        if ($period !== 'all-time') {
            $dateFilter = $this->getDateFilter($period);
            $query->whereBetween('created_at', $dateFilter);
        }

        $results = $query->limit($limit)->get();

        return $results->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'user' => $item->buyer,
                'purchase_count' => (int) $item->purchase_count,
            ];
        })->toArray();
    }

    /**
     * Get top buyers by spending.
     */
    public function getTopBuyersBySpending(?string $period = 'all-time', int $limit = 50): array
    {
        $query = Transaction::select('buyer_id', DB::raw('SUM(amount) as total_spending'))
            ->where('status', 'success')
            ->groupBy('buyer_id')
            ->with('buyer:id,name,username,avatar')
            ->orderByDesc('total_spending');

        if ($period !== 'all-time') {
            $dateFilter = $this->getDateFilter($period);
            $query->whereBetween('created_at', $dateFilter);
        }

        $results = $query->limit($limit)->get();

        return $results->map(function ($item, $index) {
            $buyer = $item->buyer;
            return [
                'rank' => $index + 1,
                'id' => $buyer?->id,
                'name' => $buyer?->name,
                'username' => $buyer?->username,
                'avatar' => $buyer?->avatar,
                'total_spending' => (float) $item->total_spending,
            ];
        })->toArray();
    }

    /**
     * Get top contributors by reviews.
     */
    public function getTopContributorsByReviews(?string $period = 'all-time', int $limit = 50): array
    {
        $query = NoteReview::select('user_id', DB::raw('COUNT(*) as review_count'))
            ->groupBy('user_id')
            ->with('user:id,name,username,avatar')
            ->orderByDesc('review_count');

        if ($period !== 'all-time') {
            $dateFilter = $this->getDateFilter($period);
            $query->whereBetween('created_at', $dateFilter);
        }

        $results = $query->limit($limit)->get();

        return $results->map(function ($item, $index) {
            $user = $item->user;
            return [
                'rank' => $index + 1,
                'id' => $user?->id,
                'name' => $user?->name,
                'username' => $user?->username,
                'avatar' => $user?->avatar,
                'user_id' => (int) $item->user_id,
                'review_count' => (int) $item->review_count,
            ];
        })->toArray();
    }

    /**
     * Get top contributors by forum posts.
     */
    public function getTopContributorsByForumPosts(?string $period = 'all-time', int $limit = 50): array
    {
        // Check if Post model exists
        if (!class_exists(\App\Models\Post::class)) {
            return [];
        }

        $query = \App\Models\Post::select('user_id', DB::raw('COUNT(*) as post_count'))
            ->where('is_published', true)
            ->groupBy('user_id')
            ->with('user:id,name,username,avatar')
            ->orderByDesc('post_count');

        if ($period !== 'all-time') {
            $dateFilter = $this->getDateFilter($period);
            $query->whereBetween('created_at', $dateFilter);
        }

        $results = $query->limit($limit)->get();

        return $results->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'user' => $item->user,
                'post_count' => (int) $item->post_count,
            ];
        })->toArray();
    }

    /**
     * Get top contributors by shares.
     */
    public function getTopContributorsByShares(?string $period = 'all-time', int $limit = 50): array
    {
        $query = NoteShareReferral::select('sharer_id', DB::raw('COUNT(*) as share_count'))
            ->groupBy('sharer_id')
            ->with('sharer:id,name,username,avatar')
            ->orderByDesc('share_count');

        if ($period !== 'all-time') {
            $dateFilter = $this->getDateFilter($period);
            $query->whereBetween('created_at', $dateFilter);
        }

        $results = $query->limit($limit)->get();

        return $results->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'user' => $item->sharer,
                'share_count' => (int) $item->share_count,
            ];
        })->toArray();
    }

    /**
     * Get date filter for period.
     */
    protected function getDateFilter(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
        };
    }
}
