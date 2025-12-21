<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'method',
        'account_number',
        'account_name',
        'bank_name',
        'ewallet_type',
        'status',
        'admin_id',
        'admin_notes',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that requested the withdrawal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that processed the withdrawal.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Approve the withdrawal.
     */
    public function approve(?int $adminId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'admin_id' => $adminId ?? auth()->id(),
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Reject the withdrawal.
     */
    public function reject(?int $adminId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_id' => $adminId ?? auth()->id(),
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Complete the withdrawal.
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }
}
