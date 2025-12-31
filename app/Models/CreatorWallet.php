<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CreatorWallet extends Model
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
        'balance_available',
        'balance_locked',
    ];

    protected function casts(): array
    {
        return [
            'balance_available' => 'decimal:2',
            'balance_locked' => 'decimal:2',
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
     * Lock amount from available balance.
     */
    public function lockAmount(float $amount): bool
    {
        if ($this->balance_available < $amount) {
            return false;
        }

        $this->balance_available -= $amount;
        $this->balance_locked += $amount;
        return $this->save();
    }

    /**
     * Unlock amount back to available balance.
     */
    public function unlockAmount(float $amount): bool
    {
        if ($this->balance_locked < $amount) {
            return false;
        }

        $this->balance_locked -= $amount;
        $this->balance_available += $amount;
        return $this->save();
    }

    /**
     * Add balance to available.
     */
    public function addBalance(float $amount): bool
    {
        $this->balance_available += $amount;
        return $this->save();
    }

    /**
     * Deduct balance from available.
     */
    public function deductBalance(float $amount): bool
    {
        if ($this->balance_available < $amount) {
            return false;
        }

        $this->balance_available -= $amount;
        return $this->save();
    }
}
