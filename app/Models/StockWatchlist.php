<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockWatchlist extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'stock_id',
        'notes',
        'alert_price_above',
        'alert_price_below',
        'notify_on_signal',
    ];

    protected function casts(): array
    {
        return [
            'alert_price_above' => 'decimal:2',
            'alert_price_below' => 'decimal:2',
            'notify_on_signal' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this watchlist item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the stock in this watchlist.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include watchlist items with alerts.
     */
    public function scopeWithAlerts($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('alert_price_above')
              ->orWhereNotNull('alert_price_below');
        });
    }
}

