<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserSetting extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'user_id',
        'notification_preferences',
        'privacy_settings',
        'email_preferences',
        'profile_visibility',
        'search_visibility',
        'auto_play_enabled',
    ];

    protected function casts(): array
    {
        return [
            'notification_preferences' => 'array',
            'privacy_settings' => 'array',
            'email_preferences' => 'array',
            'profile_visibility' => 'boolean',
            'search_visibility' => 'boolean',
            'auto_play_enabled' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
