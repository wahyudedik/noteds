<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketResponse extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_admin_response',
        'is_internal_note',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'is_admin_response' => 'boolean',
            'is_internal_note' => 'boolean',
        ];
    }

    /**
     * Get the ticket that owns the response.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Get the user that created the response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
