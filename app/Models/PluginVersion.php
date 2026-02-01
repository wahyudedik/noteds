<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginVersion extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'plugin_id',
        'version',
        'manifest',
        'archive_path',
        'storage_path',
        'checksum',
        'migration_status',
        'installed_at',
    ];

    protected function casts(): array
    {
        return [
            'manifest' => 'array',
            'installed_at' => 'datetime',
        ];
    }

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }
}

