<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeEvidence extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'dispute_id',
        'submitted_by',
        'file_path',
        'original_filename',
        'mime_type',
        'description',
    ];

    /**
     * Relationship: Belongs to ServiceOrderDispute
     */
    public function dispute(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderDispute::class);
    }

    /**
     * Relationship: Submitted by User
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
