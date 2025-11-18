<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharePoint extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'note_id',
        'share_referral_id',
        'points',
        'action',
        'earned_date',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'earned_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function shareReferral(): BelongsTo
    {
        return $this->belongsTo(NoteShareReferral::class, 'share_referral_id');
    }
}
