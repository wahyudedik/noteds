<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrmSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'enabled',
        'encrypt_files',
        'time_limited_access',
        'access_duration_days',
        'device_limit_enabled',
        'max_devices',
        'license_key_enabled',
        'license_key_type',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'encrypt_files' => 'boolean',
            'time_limited_access' => 'boolean',
            'access_duration_days' => 'integer',
            'device_limit_enabled' => 'boolean',
            'max_devices' => 'integer',
            'license_key_enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DrmAccessLog::class, 'note_id', 'note_id');
    }

    public function licenseKeys(): HasMany
    {
        return $this->hasMany(DrmLicenseKey::class, 'note_id', 'note_id');
    }
}

