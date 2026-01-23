<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class RecurrenceRule extends Model
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
        'scheduleable_type',
        'scheduleable_id',
        'timezone',
        'rrule',
        'freq',
        'interval',
        'byday',
        'bymonthday',
        'dtstart',
        'until',
        'count',
    ];

    protected function casts(): array
    {
        return [
            'byday' => 'array',
            'bymonthday' => 'array',
            'dtstart' => 'datetime',
            'until' => 'datetime',
            'interval' => 'integer',
            'count' => 'integer',
        ];
    }

    public function scheduleable(): MorphTo
    {
        return $this->morphTo();
    }
}
