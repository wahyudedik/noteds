<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LedgerEntry extends Model
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
        'transaction_id',
        'from_wallet_type',
        'from_wallet_id',
        'to_wallet_type',
        'to_wallet_id',
        'amount',
        'reason',
        'reference_type',
        'reference_id',
        'metadata',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the admin that created the entry (if manual).
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Create a ledger entry.
     */
    public static function createEntry(array $data): self
    {
        if (!isset($data['transaction_id'])) {
            $data['transaction_id'] = 'TXN-' . now()->format('YmdHis') . '-' . Str::random(8);
        }

        return static::create($data);
    }
}
