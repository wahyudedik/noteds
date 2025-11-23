<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'ip_address',
        'query',
        'filters',
        'result_count',
        'searched_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'searched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
