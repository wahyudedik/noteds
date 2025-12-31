<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CampaignWallet extends Model
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
    }

    protected $fillable = [
        'campaign_id',
        'total_budget',
        'remaining_budget',
        'locked_amount',
    ];

    protected function casts(): array
    {
        return [
            'total_budget' => 'decimal:2',
            'remaining_budget' => 'decimal:2',
            'locked_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the campaign that owns the wallet.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Lock budget from creator wallet.
     */
    public function lockBudget(float $amount): bool
    {
        $this->total_budget = $amount;
        $this->remaining_budget = $amount;
        $this->locked_amount = $amount;
        return $this->save();
    }

    /**
     * Release budget back to creator wallet.
     */
    public function releaseBudget(): bool
    {
        $amount = $this->locked_amount;
        $this->locked_amount = 0;
        $this->remaining_budget = 0;
        return $this->save();
    }

    /**
     * Deduct budget for reward payment.
     */
    public function deductBudget(float $amount): bool
    {
        if ($this->remaining_budget < $amount) {
            return false;
        }

        $this->remaining_budget -= $amount;
        return $this->save();
    }

    /**
     * Refund remaining budget.
     */
    public function refund(): float
    {
        $amount = $this->remaining_budget;
        $this->remaining_budget = 0;
        $this->save();
        return $amount;
    }
}
