<?php

namespace App\Jobs;

use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $statType = 'all', // 'all', 'dashboard', 'user', 'note'
        public ?int $userId = null,
        public ?int $noteId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            match ($this->statType) {
                'all' => $this->calculateAllStats(),
                'dashboard' => $this->calculateDashboardStats(),
                'user' => $this->calculateUserStats(),
                'note' => $this->calculateNoteStats(),
                default => throw new \InvalidArgumentException("Unknown stat type: {$this->statType}"),
            };

            Log::info('Statistics calculated', [
                'stat_type' => $this->statType,
                'user_id' => $this->userId,
                'note_id' => $this->noteId,
            ]);
        } catch (\Exception $e) {
            Log::error('Statistics calculation job failed', [
                'stat_type' => $this->statType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate all statistics.
     */
    protected function calculateAllStats(): void
    {
        $this->calculateDashboardStats();
    }

    /**
     * Calculate dashboard statistics.
     */
    protected function calculateDashboardStats(): void
    {
        $cacheKey = 'dashboard_stats';
        $cacheDuration = now()->addHours(1);

        $stats = [
            'total_users' => Cache::remember("{$cacheKey}_total_users", $cacheDuration, fn() => User::count()),
            'total_notes' => Cache::remember("{$cacheKey}_total_notes", $cacheDuration, fn() => Note::count()),
            'public_notes' => Cache::remember("{$cacheKey}_public_notes", $cacheDuration, fn() => Note::where('is_public', true)->count()),
            'total_transactions' => Cache::remember("{$cacheKey}_total_transactions", $cacheDuration, fn() => Transaction::count()),
            'total_revenue' => Cache::remember("{$cacheKey}_total_revenue", $cacheDuration, fn() => Transaction::where('status', 'success')->sum('commission')),
            'revenue_today' => Cache::remember("{$cacheKey}_revenue_today", $cacheDuration, fn() => Transaction::where('status', 'success')->whereDate('created_at', today())->sum('commission')),
            'revenue_this_month' => Cache::remember("{$cacheKey}_revenue_this_month", $cacheDuration, fn() => Transaction::where('status', 'success')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('commission')),
        ];

        Cache::put($cacheKey, $stats, $cacheDuration);
        Cache::put("{$cacheKey}_last_updated", now()->timestamp, $cacheDuration);
    }

    /**
     * Calculate user-specific statistics.
     */
    protected function calculateUserStats(): void
    {
        if (!$this->userId) {
            return;
        }

        $cacheKey = "user_stats_{$this->userId}";
        $cacheDuration = now()->addMinutes(30);

        $user = User::find($this->userId);
        if (!$user) {
            return;
        }

        $stats = [
            'total_notes' => Cache::remember("{$cacheKey}_total_notes", $cacheDuration, fn() => $user->notes()->count()),
            'total_sales' => Cache::remember("{$cacheKey}_total_sales", $cacheDuration, fn() => $user->transactionsAsSeller()->where('status', 'success')->count()),
            'total_revenue' => Cache::remember("{$cacheKey}_total_revenue", $cacheDuration, fn() => $user->transactionsAsSeller()->where('status', 'success')->sum('amount')),
            'total_purchases' => Cache::remember("{$cacheKey}_total_purchases", $cacheDuration, fn() => $user->transactionsAsBuyer()->where('status', 'success')->count()),
            'total_spent' => Cache::remember("{$cacheKey}_total_spent", $cacheDuration, fn() => $user->transactionsAsBuyer()->where('status', 'success')->sum('amount')),
        ];

        Cache::put($cacheKey, $stats, $cacheDuration);
    }

    /**
     * Calculate note-specific statistics.
     */
    protected function calculateNoteStats(): void
    {
        if (!$this->noteId) {
            return;
        }

        $cacheKey = "note_stats_{$this->noteId}";
        $cacheDuration = now()->addMinutes(15);

        $note = Note::find($this->noteId);
        if (!$note) {
            return;
        }

        $stats = [
            'purchase_count' => Cache::remember("{$cacheKey}_purchase_count", $cacheDuration, fn() => $note->purchase_count),
            'view_count' => Cache::remember("{$cacheKey}_view_count", $cacheDuration, fn() => $note->view_count),
            'total_revenue' => Cache::remember("{$cacheKey}_total_revenue", $cacheDuration, fn() => Transaction::where('note_id', $note->id)->where('status', 'success')->sum('amount')),
        ];

        Cache::put($cacheKey, $stats, $cacheDuration);
    }
}

