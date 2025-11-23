<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignRecipient extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'sequence_id',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'error_message',
        'metadata',
        'tracking_token',
        'open_count',
        'click_count',
        'clicked_links',
        'ab_test_id',
        'ab_variant_id',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'clicked_links' => 'array',
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'open_count' => 'integer',
            'click_count' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(EmailSequence::class, 'sequence_id');
    }

    public function abTest(): BelongsTo
    {
        return $this->belongsTo(EmailAbTest::class, 'ab_test_id');
    }

    /**
     * Track email open
     */
    public function trackOpen(): void
    {
        if (!$this->opened_at) {
            $this->update([
                'opened_at' => now(),
                'open_count' => 1,
            ]);
        } else {
            $this->increment('open_count');
        }
    }

    /**
     * Track email click
     */
    public function trackClick(string $url): void
    {
        $clickedLinks = $this->clicked_links ?? [];
        $clickedLinks[] = [
            'url' => $url,
            'clicked_at' => now()->toIso8601String(),
        ];

        if (!$this->clicked_at) {
            $this->update([
                'clicked_at' => now(),
                'click_count' => 1,
                'clicked_links' => $clickedLinks,
            ]);
        } else {
            $this->increment('click_count');
            $this->update(['clicked_links' => $clickedLinks]);
        }
    }
}

