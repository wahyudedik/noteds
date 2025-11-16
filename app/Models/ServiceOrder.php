<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrder extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'assigned_user_id',
        'title',
        'description',
        'budget',
        'status',
        'escrow_amount',
        'milestones',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'escrow_amount' => 'decimal:2',
            'milestones' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedVendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}


