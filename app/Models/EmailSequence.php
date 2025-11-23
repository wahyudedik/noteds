<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailSequence extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'campaign_id',
        'name',
        'trigger_event',
        'delay_days',
        'delay_hours',
        'subject',
        'content',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'delay_days' => 'integer',
            'delay_hours' => 'integer',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'sequence_id');
    }
}

