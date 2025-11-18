<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'interests',
        'preferred_categories',
        'preferred_tags',
        'browsing_history_summary',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'interests' => 'array',
            'preferred_categories' => 'array',
            'preferred_tags' => 'array',
            'browsing_history_summary' => 'array',
            'last_updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
