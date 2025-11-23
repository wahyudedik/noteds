<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'query',
        'filters',
        'result_count',
    ];

    protected $casts = [
        'filters' => 'array',
        'result_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
