<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShareAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform', 'count', 'last_shared_at',
    ];

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }
}
