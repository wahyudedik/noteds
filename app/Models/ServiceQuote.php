<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceQuote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_order_id',
        'vendor_id',
        'total_amount',
        'milestones',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'milestones' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}


