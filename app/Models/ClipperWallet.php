<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClipperWallet extends Model
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
        'user_id',
        'balance_pending',
        'balance_available',
        'balance_withdrawn',
    ];

    protected function casts(): array
    {
        return [
            'balance_pending' => 'decimal:2',
            'balance_available' => 'decimal:2',
            'balance_withdrawn' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add reward to pending balance.
     */
    public function addReward(float $amount): bool
    {
        $this->balance_pending += $amount;
        return $this->save();
    }

    /**
     * Move pending to available balance.
     */
    public function movePendingToAvailable(float $amount): bool
    {
        if ($this->balance_pending < $amount) {
            return false;
        }

        $this->balance_pending -= $amount;
        $this->balance_available += $amount;
        return $this->save();
    }

    /**
     * Lock amount for withdrawal.
     */
    public function lockForWithdrawal(float $amount): bool
    {
        if ($this->balance_available < $amount) {
            return false;
        }

        $this->balance_available -= $amount;
        return $this->save();
    }

    /**
     * Mark amount as withdrawn.
     */
    public function markAsWithdrawn(float $amount): bool
    {
        $this->balance_withdrawn += $amount;
        return $this->save();
    }
}
