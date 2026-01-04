<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'ticket_number',
        'subject',
        'message',
        'category',
        'status',
        'priority',
        'attachments',
        'assigned_to',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    /**
     * Generate a unique ticket number.
     */
    public static function generateTicketNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TKT-' . $date . '-';
        
        $lastTicket = static::where('ticket_number', 'like', $prefix . '%')
            ->orderBy('ticket_number', 'desc')
            ->first();
        
        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Get the user that created the ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin assigned to the ticket.
     */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all responses for the ticket.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(SupportTicketResponse::class, 'ticket_id')->orderBy('created_at');
    }

    /**
     * Get public responses (excluding internal notes).
     */
    public function publicResponses(): HasMany
    {
        return $this->hasMany(SupportTicketResponse::class, 'ticket_id')
            ->where('is_internal_note', false)
            ->orderBy('created_at');
    }

    /**
     * Get admin responses.
     */
    public function adminResponses(): HasMany
    {
        return $this->hasMany(SupportTicketResponse::class, 'ticket_id')
            ->where('is_admin_response', true)
            ->orderBy('created_at');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope a query to only include resolved tickets.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Mark ticket as in progress.
     */
    public function markAsInProgress(?string $adminId = null): void
    {
        $this->update([
            'status' => 'in_progress',
            'assigned_to' => $adminId ?? auth()->id(),
        ]);
    }

    /**
     * Mark ticket as resolved.
     */
    public function markAsResolved(?string $adminId = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'assigned_to' => $adminId ?? $this->assigned_to,
        ]);
    }

    /**
     * Mark ticket as closed.
     */
    public function markAsClosed(?string $adminId = null): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'assigned_to' => $adminId ?? $this->assigned_to,
        ]);
    }

    /**
     * Reopen ticket.
     */
    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * Check if ticket is open.
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    /**
     * Check if ticket is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if ticket is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Get attachment URLs.
     */
    public function getAttachmentUrlsAttribute(): array
    {
        if (!$this->attachments) {
            return [];
        }

        return array_map(function ($path) {
            return asset('storage/' . $path);
        }, $this->attachments);
    }
}
