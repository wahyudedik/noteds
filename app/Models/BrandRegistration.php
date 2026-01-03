<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BrandRegistration extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'user_id',
        'company_name',
        'business_type',
        'website',
        'social_media',
        'contact_person',
        'phone',
        'status',
        'admin_notes',
        'approved_at',
        'rejected_at',
        'admin_id',
    ];

    protected $casts = [
        'social_media' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the user who registered as brand.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved/rejected the registration.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Check if registration is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if registration is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if registration is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Accessor for website_url (maps to website field).
     */
    public function getWebsiteUrlAttribute(): ?string
    {
        return $this->website;
    }

    /**
     * Accessor for contact_name (maps to contact_person field).
     */
    public function getContactNameAttribute(): ?string
    {
        return $this->contact_person;
    }

    /**
     * Accessor for contact_phone (maps to phone field).
     */
    public function getContactPhoneAttribute(): ?string
    {
        return $this->phone;
    }

    /**
     * Accessor for contact_email (from user relationship).
     */
    public function getContactEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Accessor for description (can be derived from admin_notes or empty).
     */
    public function getDescriptionAttribute(): ?string
    {
        return null; // Description field doesn't exist in schema, return null
    }

    /**
     * Accessor for rejection_reason (maps to admin_notes when status is rejected).
     */
    public function getRejectionReasonAttribute(): ?string
    {
        return $this->status === 'rejected' ? $this->admin_notes : null;
    }

    /**
     * Accessor for approval_notes (maps to admin_notes when status is approved).
     */
    public function getApprovalNotesAttribute(): ?string
    {
        return $this->status === 'approved' ? $this->admin_notes : null;
    }
}

