<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuid;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();
    }

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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'business_name',
        'business_field',
        'skills',
        'goals',
        'portfolio_url',
        'website_url',
        'is_verified_mentor',
        'role',
        'balance',
        'midtrans_merchant_id',
        'clipper_role',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'is_verified_seller',
        'seller_rating',
        'low_stock_alert_threshold',
        'low_stock_alert_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'skills' => 'array',
            'goals' => 'array',
            'is_verified_mentor' => 'boolean',
            'balance' => 'decimal:2',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'is_verified_seller' => 'boolean',
            'seller_rating' => 'decimal:2',
            'low_stock_alert_threshold' => 'integer',
            'low_stock_alert_enabled' => 'boolean',
        ];
    }

    /**
     * Get the posts created by the user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the comments created by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the products created by the user (as seller).
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the orders created by the user (as buyer).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the withdrawals for the user.
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Get the payment methods for the user.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(UserPaymentMethod::class);
    }

    /**
     * Get the avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return asset('storage/' . $this->avatar);
    }

    /**
     * Get the creator wallet for the user.
     */
    public function creatorWallet(): HasOne
    {
        return $this->hasOne(CreatorWallet::class);
    }

    /**
     * Get the clipper wallet for the user.
     */
    public function clipperWallet(): HasOne
    {
        return $this->hasOne(ClipperWallet::class);
    }

    /**
     * Get the campaigns created by the user (as creator/brand).
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'creator_id');
    }

    /**
     * Get the campaign templates created by this user.
     */
    public function campaignTemplates(): HasMany
    {
        return $this->hasMany(CampaignTemplate::class);
    }

    /**
     * Get the clips submitted by the user (as clipper).
     */
    public function clips(): HasMany
    {
        return $this->hasMany(Clip::class, 'clipper_id');
    }

    /**
     * Get the brand registrations for the user.
     */
    public function brandRegistrations(): HasMany
    {
        return $this->hasMany(BrandRegistration::class);
    }

    /**
     * Check if user has pending brand registration.
     */
    public function hasPendingBrandRegistration(): bool
    {
        return $this->brandRegistrations()
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Get the clipper registrations for the user.
     */
    public function clipperRegistrations(): HasMany
    {
        return $this->hasMany(\App\Models\ClipperRegistration::class);
    }

    /**
     * Check if user has pending clipper registration.
     */
    public function hasPendingClipperRegistration(): bool
    {
        return $this->clipperRegistrations()
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Get the top ups for the user.
     */
    public function topUps(): HasMany
    {
        return $this->hasMany(TopUp::class);
    }

    /**
     * Check if user is a brand/creator.
     */
    public function isBrand(): bool
    {
        return $this->clipper_role === 'brand';
    }

    /**
     * Check if user is a clipper.
     */
    public function isClipper(): bool
    {
        return $this->clipper_role === 'clipper';
    }

    /**
     * Get the users that this user is following.
     */
    public function following(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /**
     * Get the users that follow this user.
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    /**
     * Get categories associated with this user.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'user_categories')
            ->withPivot('source', 'confidence')
            ->withTimestamps();
    }

    /**
     * Get manually added categories.
     */
    public function manualCategories(): BelongsToMany
    {
        return $this->categories()
            ->wherePivot('source', 'manual');
    }

    /**
     * Get inferred categories.
     */
    public function inferredCategories(): BelongsToMany
    {
        return $this->categories()
            ->wherePivot('source', 'inferred');
    }

    /**
     * Get all categories (manual + inferred).
     */
    public function getAllCategories()
    {
        return $this->categories()->get();
    }

    /**
     * Check if user has a specific category.
     */
    public function hasCategory(Category|string $category): bool
    {
        $categoryId = $category instanceof Category ? $category->id : $category;
        return $this->categories()->where('categories.id', $categoryId)->exists();
    }

    /**
     * Get the bookmarks created by the user.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get bookmark collections for the user.
     */
    public function bookmarkCollections(): HasMany
    {
        return $this->hasMany(BookmarkCollection::class);
    }

    /**
     * Get the user settings.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * Check if the user is following another user.
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Check if the user is followed by another user.
     */
    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    /**
     * Get the support tickets created by the user.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Get the support ticket responses created by the user.
     */
    public function supportTicketResponses(): HasMany
    {
        return $this->hasMany(SupportTicketResponse::class);
    }

    /**
     * Get the support tickets assigned to the user (as admin).
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }

    /**
     * Get the activity logs for the user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /**
     * Get the reposts created by the user.
     */
    public function reposts(): HasMany
    {
        return $this->hasMany(Repost::class);
    }

    /**
     * Get the seller verification for this user.
     */
    public function sellerVerification(): HasOne
    {
        return $this->hasOne(SellerVerification::class);
    }

    /**
     * Get the seller ratings received by this user.
     */
    public function sellerRatings(): HasMany
    {
        return $this->hasMany(SellerRating::class, 'seller_id');
    }

    /**
     * Get the seller ratings given by this user.
     */
    public function givenSellerRatings(): HasMany
    {
        return $this->hasMany(SellerRating::class, 'buyer_id');
    }

    /**
     * Get the performance metrics for this user as a seller.
     */
    public function performanceMetrics(): HasOne
    {
        return $this->hasOne(SellerPerformanceMetric::class, 'seller_id');
    }

    /**
     * Check if user is a verified seller.
     */
    public function isVerifiedSeller(): bool
    {
        return $this->is_verified_seller ?? false;
    }

    /**
     * Check if user can apply for verification.
     */
    public function canApplyForVerification(): bool
    {
        // Check if already verified
        if ($this->isVerifiedSeller()) {
            return false;
        }

        // Check if has pending application
        if ($this->sellerVerification && $this->sellerVerification->isPending()) {
            return false;
        }

        // Check if email is verified
        if (config('seller.verification.require_email_verification', true) && !$this->hasVerifiedEmail()) {
            return false;
        }

        // Check minimum products requirement
        $minProducts = config('seller.verification.min_products_required', 1);
        if ($this->products()->count() < $minProducts) {
            return false;
        }

        return true;
    }

    /**
     * Get seller rating (cached value).
     */
    public function getSellerRating(): ?float
    {
        return $this->seller_rating;
    }

    /**
     * Check if user has products with low stock.
     */
    public function hasLowStockProducts(): bool
    {
        return $this->products()
            ->whereNotNull('stock')
            ->whereRaw('stock <= COALESCE(low_stock_threshold, ?)', [
                $this->low_stock_alert_threshold ?? config('seller.inventory.default_low_stock_threshold', 10)
            ])
            ->exists();
    }

    /**
     * Get the stock watchlists for this user.
     */
    public function stockWatchlists(): HasMany
    {
        return $this->hasMany(StockWatchlist::class);
    }

    /**
     * Get conversations where user is a participant.
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['role', 'joined_at', 'left_at', 'last_read_at', 'muted_until', 'archived_at'])
            ->withTimestamps()
            ->whereNull('conversation_participants.left_at')
            ->orderBy('conversations.last_message_at', 'desc');
    }

    /**
     * Get conversation participants for this user.
     */
    public function conversationParticipants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Get messages sent by this user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get read receipts for this user.
     */
    public function readReceipts(): HasMany
    {
        return $this->hasMany(ReadReceipt::class);
    }

    /**
     * Get users blocked by this user.
     */
    public function blockedUsers(): HasMany
    {
        return $this->hasMany(BlockedUser::class, 'user_id');
    }

    /**
     * Get users who blocked this user.
     */
    public function blockedByUsers(): HasMany
    {
        return $this->hasMany(BlockedUser::class, 'blocked_user_id');
    }

    /**
     * Check if user is blocked by another user.
     */
    public function isBlockedBy(User $user): bool
    {
        return $this->blockedByUsers()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Check if user has blocked another user.
     */
    public function hasBlocked(User $user): bool
    {
        return $this->blockedUsers()
            ->where('blocked_user_id', $user->id)
            ->exists();
    }
}
