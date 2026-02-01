<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plugin extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'version',
        'type',
        'author',
        'android_package_name',
        'description',
        'manifest',
        'dependencies',
        'permissions',
        'enabled',
        'checksum',
        'storage_path',
        'installed_at',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'manifest' => 'array',
            'dependencies' => 'array',
            'permissions' => 'array',
            'enabled' => 'boolean',
            'installed_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PluginVersion::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PluginLog::class);
    }
}

