<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affiliate extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'code',
        'commission_rate',
        'total_clicks',
        'total_conversions',
        'total_earned',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'float',
        'total_clicks' => 'integer',
        'total_conversions' => 'integer',
        'total_earned' => 'float',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
