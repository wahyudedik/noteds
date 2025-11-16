<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowLedger extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_order_id',
        'user_id',
        'type',
        'amount',
        'milestone_index',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }
}


