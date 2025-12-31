<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlatformWallet extends Model
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
        'fee_balance',
        'operational_balance',
    ];

    protected function casts(): array
    {
        return [
            'fee_balance' => 'decimal:2',
            'operational_balance' => 'decimal:2',
        ];
    }

    /**
     * Add fee to platform wallet.
     */
    public function addFee(float $amount): bool
    {
        $this->fee_balance += $amount;
        return $this->save();
    }

    /**
     * Get total balance.
     */
    public function getTotalBalance(): float
    {
        return (float) $this->fee_balance + (float) $this->operational_balance;
    }

    /**
     * Get or create the platform wallet (singleton).
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate(
            ['id' => '00000000-0000-0000-0000-000000000001'],
            [
                'fee_balance' => 0,
                'operational_balance' => 0,
            ]
        );
    }
}
