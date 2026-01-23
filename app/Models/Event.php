<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
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
        if (!isset($this->attributes['share_token'])) {
            $this->attributes['share_token'] = Str::random(32);
        }
    }

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'is_virtual',
        'meeting_url',
        'privacy',
        'start_at',
        'end_at',
        'timezone',
        'share_token',
        'status',
        'max_attendees',
    ];

    protected function casts(): array
    {
        return [
            'is_virtual' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'max_attendees' => 'integer',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(EventReminder::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'event_categories');
    }
}
