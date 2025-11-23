<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityCheck extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'transaction_id',
        'checked_by',
        'check_type',
        'status',
        'check_results',
        'notes',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'check_results' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * Check if quality check passed
     */
    public function passed(): bool
    {
        return $this->status === 'passed';
    }

    /**
     * Check if quality check failed
     */
    public function failed(): bool
    {
        return $this->status === 'failed';
    }
}

