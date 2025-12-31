<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClipViewTracking extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'clip_id',
        'views_count',
        'tracked_at',
        'stability_score',
        'is_valid',
    ];

    protected function casts(): array
    {
        return [
            'views_count' => 'integer',
            'tracked_at' => 'datetime',
            'stability_score' => 'decimal:2',
            'is_valid' => 'boolean',
        ];
    }

    /**
     * Get the clip that owns the tracking record.
     */
    public function clip(): BelongsTo
    {
        return $this->belongsTo(Clip::class);
    }
}
