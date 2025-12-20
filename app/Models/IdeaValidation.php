<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaValidation extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'validation_status',
        'estimated_capital',
        'estimated_bep',
        'feedback',
        'risks',
    ];

    protected function casts(): array
    {
        return [
            'estimated_capital' => 'decimal:2',
            'estimated_bep' => 'decimal:2',
            'risks' => 'array',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
