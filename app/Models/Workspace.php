<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\Transaction;

class Workspace extends Model
{
    use HasUuids;

    protected $fillable = [
        'owner_id',
        'original_creator_id',
        'name',
        'slug',
        'type',
        'description',
        'avatar',
        'is_active',
        'price',
        'discount_price',
        'is_for_sale',
        'sold_at',
        'sold_to_user_id',
        'marketplace_description',
        'sale_mode',
        'grace_period_days',
        'relist_price_multiplier',
        'attachments',
        'thumbnails',
        'file_count',
        'status',
        'is_public',
        'is_sold',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_for_sale' => 'boolean',
            'is_public' => 'boolean',
            'is_sold' => 'boolean',
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'sold_at' => 'datetime',
            'attachments' => 'array',
            'thumbnails' => 'array',
            'file_count' => 'integer',
            'grace_period_days' => 'integer',
            'relist_price_multiplier' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workspace) {
            if (empty($workspace->slug)) {
                $baseSlug = Str::slug($workspace->name);
                $slug = $baseSlug;
                $counter = 1;
                
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $workspace->slug = $slug;
            }
        });
    }

    /**
     * Get the owner of the workspace.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the original creator of this workspace.
     */
    public function originalCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_creator_id');
    }

    /**
     * Get all members of the workspace.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members', 'workspace_id', 'user_id')
            ->withPivot('role', 'is_active', 'joined_at')
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    /**
     * Get all workspace members (including inactive).
     */
    public function allMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members', 'workspace_id', 'user_id')
            ->withPivot('role', 'is_active', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get workspace member records.
     */
    public function memberRecords(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /**
     * Get notes in this workspace.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->latest();
    }

    /**
     * Get folders in this workspace.
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class)->whereNull('parent_id')->latest();
    }

    /**
     * Check if user is a member of this workspace.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('users.id', $user->id)->exists();
    }

    /**
     * Get user's role in this workspace.
     */
    public function getUserRole(User $user): ?string
    {
        $member = $this->memberRecords()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
        
        if ($user->id === $this->owner_id) {
            return 'owner';
        }
        
        return $member?->role;
    }

    /**
     * Check if user can manage workspace.
     */
    public function canManage(User $user): bool
    {
        if ($user->id === $this->owner_id) {
            return true;
        }
        
        $role = $this->getUserRole($user);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if workspace is for sale.
     */
    public function isForSale(): bool
    {
        return $this->is_for_sale 
            && $this->price > 0 
            && $this->is_public 
            && $this->status === 'active'
            && ($this->isStandardMode() || (!$this->is_sold && !$this->sold_at));
    }

    /**
     * Check if workspace is sold.
     */
    public function isSold(): bool
    {
        return $this->sold_at !== null;
    }

    /**
     * Get the buyer user.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_to_user_id');
    }

    /**
     * Get workspace invitations.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    /**
     * Get pending (not accepted) invitations.
     */
    public function pendingInvitations(): HasMany
    {
        return $this->invitations()
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Get workspace transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'workspace_id');
    }

    /**
     * Get workspace tasks
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(\App\Models\WorkspaceTask::class);
    }

    /**
     * Get workspace reminders
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(\App\Models\WorkspaceReminder::class);
    }

    /**
     * Get workspace timeline
     */
    public function timeline(): HasMany
    {
        return $this->hasMany(\App\Models\WorkspaceTimeline::class);
    }

    /**
     * Get workspace insights
     */
    public function insights(): HasMany
    {
        return $this->hasMany(\App\Models\WorkspaceInsight::class);
    }

    /**
     * Get workspace semantic embeddings
     */
    public function semanticEmbeddings(): HasMany
    {
        return $this->hasMany(\App\Models\WorkspaceSemanticEmbedding::class);
    }

    /**
     * Get workspace knowledge graph
     */
    public function knowledgeGraph(): HasMany
    {
        return $this->hasMany(\App\Models\WorkspaceKnowledgeGraph::class);
    }

    /**
     * Check if attachments exist
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments) && is_array($this->attachments) && count($this->attachments) > 0;
    }

    /**
     * Check if workspace has thumbnails.
     */
    public function hasThumbnails(): bool
    {
        return !empty($this->thumbnails) && is_array($this->thumbnails) && count($this->thumbnails) > 0;
    }

    /**
     * Get the final price (discount price if available, otherwise regular price).
     */
    public function getFinalPriceAttribute(): float
    {
        if ($this->discount_price !== null && $this->discount_price > 0) {
            return (float) $this->discount_price;
        }
        return (float) $this->price;
    }

    /**
     * Get the discount percentage.
     */
    public function getDiscountPercentAttribute(): ?float
    {
        if ($this->discount_price === null || $this->discount_price <= 0 || $this->price <= 0) {
            return null;
        }
        
        $discount = $this->price - $this->discount_price;
        return round(($discount / $this->price) * 100, 0);
    }

    /**
     * Check if workspace has discount.
     */
    public function hasDiscount(): bool
    {
        return $this->discount_price !== null 
            && $this->discount_price > 0 
            && $this->discount_price < $this->price;
    }

    /**
     * Check if workspace is in scarcity mode.
     */
    public function isScarcityMode(): bool
    {
        return $this->sale_mode === 'scarcity';
    }

    /**
     * Check if workspace is in standard mode.
     */
    public function isStandardMode(): bool
    {
        return $this->sale_mode === 'standard';
    }

    /**
     * Check if workspace has been sold (scarcity mode only).
     */
    public function hasBeenSold(): bool
    {
        return $this->is_sold;
    }

    /**
     * Check if a user has purchased this workspace.
     */
    public function isPurchasedBy($userId): bool
    {
        return $this->transactions()
            ->where('buyer_id', $userId)
            ->where('status', 'success')
            ->exists();
    }

    /**
     * Check if user can repurchase this workspace (within grace period or after).
     */
    public function canRepurchase($userId): bool
    {
        if (!$this->isScarcityMode()) {
            return false; // Only scarcity mode supports repurchase
        }

        $transaction = Transaction::where('buyer_id', $userId)
            ->where('workspace_id', $this->id)
            ->where('status', 'success')
            ->first();

        if (!$transaction) {
            return false; // User never purchased
        }

        // Check if user sold the workspace
        if ($this->owner_id !== $userId) {
            // User sold it, check grace period
            if ($transaction->grace_period_ends_at && $transaction->grace_period_ends_at->isFuture()) {
                return true; // Within grace period - can repurchase at original price
            }
            // After grace period - can repurchase at premium price
            return true;
        }

        return false; // User still owns it
    }

    /**
     * Get repurchase price for user (original price within grace period, premium after).
     */
    public function getRepurchasePrice($userId): ?float
    {
        if (!$this->canRepurchase($userId)) {
            return null;
        }

        $transaction = Transaction::where('buyer_id', $userId)
            ->where('workspace_id', $this->id)
            ->where('status', 'success')
            ->first();

        if (!$transaction) {
            return null;
        }

        $basePrice = $this->hasDiscount() ? $this->discount_price : $this->price;

        // Check grace period
        if ($transaction->grace_period_ends_at && $transaction->grace_period_ends_at->isFuture()) {
            return (float) $basePrice; // Original price within grace period
        }

        // After grace period - premium price
        return (float) ($basePrice * $this->relist_price_multiplier);
    }

    /**
     * Get purchase count (number of successful transactions)
     */
    public function getPurchaseCountAttribute(): int
    {
        if ($this->relationLoaded('transactions')) {
            return $this->transactions->where('status', 'success')->count();
        }
        return $this->transactions()->where('status', 'success')->count();
    }
}
