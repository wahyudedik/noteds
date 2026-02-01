<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginLog extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'plugin_id',
        'level',
        'message',
        'context',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'duration_ms' => 'decimal:3',
        ];
    }

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }
}

