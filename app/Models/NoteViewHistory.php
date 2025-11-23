<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteViewHistory extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'note_view_history';

    protected $fillable = [
        'user_id',
        'note_id',
        'viewed_at',
        'ip_address',
        'user_agent',
        'traffic_source',
        'referrer_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'country_code',
        'country_name',
        'city',
        'region',
        'hour',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
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
}
