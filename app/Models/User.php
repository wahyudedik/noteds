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
        'header_image',
        'skills',
        'goals',
        'portfolio_url',
        'website_url',
        'is_verified_mentor',
        'role',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
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
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
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

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
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
     * Get the header image URL.
     */
    public function getHeaderImageUrlAttribute(): ?string
    {
        if (!$this->header_image) {
            return null;
        }
        if (str_starts_with($this->header_image, 'http')) {
            return $this->header_image;
        }
        return asset('storage/' . $this->header_image);
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

    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'user_id');
    }

    public function eventInvitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class);
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
