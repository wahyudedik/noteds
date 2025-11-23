<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteAbTestAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ab_test_id',
        'user_id',
        'session_id',
        'variant',
        'viewed',
        'purchased',
        'viewed_at',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed' => 'boolean',
            'purchased' => 'boolean',
            'viewed_at' => 'datetime',
            'purchased_at' => 'datetime',
        ];
    }

    public function abTest(): BelongsTo
    {
        return $this->belongsTo(NoteAbTest::class, 'ab_test_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
