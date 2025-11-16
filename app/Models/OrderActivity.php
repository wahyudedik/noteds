<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderActivity extends Model
{
    use HasUuids;

    protected $fillable = [
        'service_order_id',
        'user_id',
        'action',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


