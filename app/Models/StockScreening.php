<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StockScreening extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'filters',
        'results',
        'results_count',
        'last_run_at',
        'is_saved',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'results' => 'array',
            'results_count' => 'integer',
            'last_run_at' => 'datetime',
            'is_saved' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this screening (nullable for anonymous).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Run the screening with current filters.
     * This method should be implemented in StockScreeningService.
     */
    public function run(): array
    {
        // This should delegate to StockScreeningService
        // Placeholder implementation
        return [];
    }

    /**
     * Get screening results.
     */
    public function getResults(): array
    {
        return $this->results ?? [];
    }

    /**
     * Check if screening results are expired (older than 1 hour).
     */
    public function isExpired(): bool
    {
        if ($this->last_run_at === null) {
            return true;
        }

        return Carbon::now()->diffInHours($this->last_run_at) > 1;
    }

    /**
     * Scope a query to only include saved screenings.
     */
    public function scopeSaved($query)
    {
        return $query->where('is_saved', true);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}

