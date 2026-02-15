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
        'id',
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
        'price',
        'demo_url',
        'thumbnail_url',
        'is_paid',
        'category',
        'screenshots',
        'system_requirements',
        'file_path',
        'file_size',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'manifest' => 'array',
            'dependencies' => 'array',
            'permissions' => 'array',
            'screenshots' => 'array',
            'enabled' => 'boolean',
            'is_paid' => 'boolean',
            'price' => 'decimal:2',
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

