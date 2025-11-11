<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Webhook extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'event',
        'secret',
        'is_active',
        'success_count',
        'failure_count',
        'last_triggered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'success_count' => 'integer',
            'failure_count' => 'integer',
            'last_triggered_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($webhook) {
            if (empty($webhook->secret)) {
                $webhook->secret = Str::random(32);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incrementSuccess(): void
    {
        $this->increment('success_count');
        $this->update(['last_triggered_at' => now()]);
    }

    public function incrementFailure(): void
    {
        $this->increment('failure_count');
        $this->update(['last_triggered_at' => now()]);
    }
}
