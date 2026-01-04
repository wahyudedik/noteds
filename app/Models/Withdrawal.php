<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Withdrawal extends Model
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
        'user_type',
        'transfer_proof_approve',
        'transfer_proof_complete',
        'transfer_proof_approve_uploaded_at',
        'transfer_proof_complete_uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
            'transfer_proof_approve' => 'array',
            'transfer_proof_complete' => 'array',
            'transfer_proof_approve_uploaded_at' => 'datetime',
            'transfer_proof_complete_uploaded_at' => 'datetime',
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
    public function approve(?string $adminId = null, ?string $notes = null): void
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
    public function reject(?string $adminId = null, ?string $notes = null): void
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

    /**
     * Scope a query to only include seller withdrawals.
     */
    public function scopeForSeller($query)
    {
        return $query->where('user_type', 'seller');
    }

    /**
     * Scope a query to only include clipper withdrawals.
     */
    public function scopeForClipper($query)
    {
        return $query->where('user_type', 'clipper');
    }

    /**
     * Scope a query to only include creator withdrawals.
     */
    public function scopeForCreator($query)
    {
        return $query->where('user_type', 'creator');
    }

    /**
     * Get transfer proof URLs for approve stage.
     *
     * @return array
     */
    public function getTransferProofApproveUrls(): array
    {
        if (empty($this->transfer_proof_approve)) {
            return [];
        }

        $urls = [];
        foreach ($this->transfer_proof_approve as $path) {
            $urls[] = [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ];
        }

        return $urls;
    }

    /**
     * Get transfer proof URLs for complete stage.
     *
     * @return array
     */
    public function getTransferProofCompleteUrls(): array
    {
        if (empty($this->transfer_proof_complete)) {
            return [];
        }

        $urls = [];
        foreach ($this->transfer_proof_complete as $path) {
            $urls[] = [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ];
        }

        return $urls;
    }

    /**
     * Get all transfer proof URLs (both approve and complete).
     *
     * @return array
     */
    public function getAllTransferProofUrls(): array
    {
        return [
            'approve' => $this->getTransferProofApproveUrls(),
            'complete' => $this->getTransferProofCompleteUrls(),
        ];
    }
}
