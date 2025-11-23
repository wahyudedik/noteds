<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestVote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contest_id',
        'entry_id',
        'user_id',
        'ip_address',
    ];

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

