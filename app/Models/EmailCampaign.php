<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'content',
        'settings',
        'is_active',
        'scheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'scheduled_at' => 'datetime',
        ];
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(EmailSequence::class, 'campaign_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }
}

