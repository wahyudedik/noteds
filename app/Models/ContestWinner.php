<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestWinner extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contest_id',
        'entry_id',
        'user_id',
        'position',
        'prizes_awarded',
        'prizes_distributed',
        'prizes_distributed_at',
    ];

    protected function casts(): array
    {
        return [
            'prizes_awarded' => 'array',
            'prizes_distributed' => 'boolean',
            'prizes_distributed_at' => 'datetime',
            'position' => 'integer',
        ];
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ContestEntry::class, 'entry_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

