<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteShareUserTracking extends Model
{
    use HasUuids;

    protected $fillable = [
        'share_referral_id',
        'user_id',
        'share_count',
    ];

    protected function casts(): array
    {
        return [
            'share_count' => 'integer',
        ];
    }

    public function shareReferral(): BelongsTo
    {
        return $this->belongsTo(NoteShareReferral::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
