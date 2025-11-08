<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use App\Models\NoteConversation;
use App\Models\NoteMessage;
use App\Models\NoteReviewReply;
use App\Models\NoteReview;
use App\Models\NoteReport;
use App\Models\UserReport;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable, HasRoles;

    protected ?array $sellerReviewStatsCache = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'wallet_balance',
        'avatar',
        'bio',
        'location',
        'referral_code',
        'referred_by',
        'currency',
        'timezone',
        'locale',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'forum_email_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'wallet_balance' => 'decimal:2',
            'forum_email_preferences' => 'array',
        ];
    }

    public function getForumEmailPreferencesAttribute($value): array
    {
        $stored = is_array($value) ? $value : (json_decode($value ?? '[]', true) ?? []);

        return array_merge($this->defaultForumEmailPreferences(), $stored);
    }

    public function setForumEmailPreferencesAttribute($value): void
    {
        $preferences = array_merge($this->defaultForumEmailPreferences(), array_intersect_key((array) $value, $this->defaultForumEmailPreferences()));

        $this->attributes['forum_email_preferences'] = json_encode($preferences);
    }

    public function defaultForumEmailPreferences(): array
    {
        return [
            'post_liked' => true,
            'post_commented' => true,
            'comment_replied' => true,
            'comment_liked' => true,
            'new_follower' => true,
        ];
    }

    public function wantsForumEmail(string $type): bool
    {
        if (!$this->hasVerifiedEmail()) {
            return false;
        }

        $map = [
            'forum_post_liked' => 'post_liked',
            'forum_post_commented' => 'post_commented',
            'forum_comment_replied' => 'comment_replied',
            'forum_comment_liked' => 'comment_liked',
            'forum_new_follower' => 'new_follower',
        ];

        if (!array_key_exists($type, $map)) {
            return false;
        }

        $preferences = $this->forum_email_preferences;

        return $preferences[$map[$type]] ?? false;
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class)->whereNull('parent_id')->orderBy('order');
    }

    public function allFolders()
    {
        return $this->hasMany(Folder::class)->orderBy('order');
    }

    /**
     * Get workspaces owned by user.
     */
    public function ownedWorkspaces()
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    /**
     * Get workspaces user is a member of.
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members', 'user_id', 'workspace_id')
            ->withPivot('role', 'is_active', 'joined_at')
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    /**
     * Get all workspaces (owned + member).
     */
    public function allWorkspaces()
    {
        $owned = $this->ownedWorkspaces()->get();
        $member = $this->workspaces()->get();
        
        return $owned->merge($member)->unique('id');
    }

    /**
     * Get or create personal workspace.
     */
    public function getPersonalWorkspace(): Workspace
    {
        $workspace = $this->ownedWorkspaces()
            ->where('type', 'personal')
            ->first();
        
        if (!$workspace) {
            $workspace = $this->ownedWorkspaces()->create([
                'name' => $this->name . "'s Workspace",
                'type' => 'personal',
                'description' => 'Personal workspace',
            ]);
            
            // Add user as owner member
            $workspace->memberRecords()->create([
                'user_id' => $this->id,
                'role' => 'owner',
                'is_active' => true,
            ]);
        }
        
        return $workspace;
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactionsAsBuyer()
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    public function transactionsAsSeller()
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    public function withdraws()
    {
        return $this->hasMany(Withdraw::class);
    }

    public function reviews()
    {
        return $this->hasMany(NoteReview::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralsReceived()
    {
        return $this->hasMany(Referral::class, 'referred_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function notifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    public function noteConversationsAsBuyer(): HasMany
    {
        return $this->hasMany(NoteConversation::class, 'buyer_id');
    }

    public function noteConversationsAsSeller(): HasMany
    {
        return $this->hasMany(NoteConversation::class, 'seller_id');
    }

    public function noteMessages(): HasMany
    {
        return $this->hasMany(NoteMessage::class, 'sender_id');
    }

    public function noteReviewReplies(): HasMany
    {
        return $this->hasMany(NoteReviewReply::class, 'user_id');
    }

    public function noteReports(): HasMany
    {
        return $this->hasMany(NoteReport::class, 'user_id');
    }

    public function accountReports(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reported_user_id');
    }

    public function submittedAccountReports(): HasMany
    {
        return $this->hasMany(UserReport::class, 'user_id');
    }

    public function sellerReviewStats(): array
    {
        if ($this->sellerReviewStatsCache !== null) {
            return $this->sellerReviewStatsCache;
        }

        $stats = DB::table('notes')
            ->join('note_reviews', 'note_reviews.note_id', '=', 'notes.id')
            ->where('notes.user_id', $this->id)
            ->selectRaw('AVG(note_reviews.rating) as avg_rating, COUNT(note_reviews.id) as total_reviews')
            ->first();

        return $this->sellerReviewStatsCache = [
            'average' => $stats && $stats->avg_rating !== null ? round((float) $stats->avg_rating, 1) : 0.0,
            'count' => $stats ? (int) $stats->total_reviews : 0,
        ];
    }

    public function sellerAverageRating(): float
    {
        return $this->sellerReviewStats()['average'];
    }

    public function sellerTotalReviews(): int
    {
        return $this->sellerReviewStats()['count'];
    }

    /**
     * Generate a unique referral code for the user.
     */
    public function generateReferralCode(): string
    {
        if ($this->referral_code) {
            return $this->referral_code;
        }

        do {
            $code = strtoupper(substr($this->username ?? $this->name, 0, 3) . rand(1000, 9999));
        } while (User::where('referral_code', $code)->exists());

        $this->update(['referral_code' => $code]);

        return $code;
    }

    /**
     * Get total referral rewards earned.
     */
    public function getTotalReferralRewards(): float
    {
        return $this->referralsMade()->paid()->sum('reward_amount') ?? 0;
    }

    /**
     * Get pending referral rewards.
     */
    public function getPendingReferralRewards(): float
    {
        return $this->referralsMade()->pending()->sum('reward_amount') ?? 0;
    }

    /**
     * Check if user has active premium subscription.
     */
    public function hasPremium(): bool
    {
        // Admin has full access
        if ($this->hasRole('admin')) {
            return true;
        }
        
        return $this->subscription && $this->subscription->isPremium();
    }

    /**
     * Get note creation limit based on subscription.
     */
    public function getNoteCreationLimit(): int
    {
        // Admin has unlimited access
        if ($this->hasRole('admin')) {
            return -1; // Unlimited
        }
        
        if ($this->hasPremium()) {
            return -1; // Unlimited
        }
        
        return 10; // Basic plan: 10 notes
    }

    /**
     * Check if user can create more notes.
     */
    public function canCreateMoreNotes(): bool
    {
        // Admin can always create notes
        if ($this->hasRole('admin')) {
            return true;
        }
        
        $limit = $this->getNoteCreationLimit();
        
        if ($limit === -1) {
            return true; // Unlimited
        }
        
        return $this->notes()->count() < $limit;
    }

    /**
     * Get purchased notes by this user.
     */
    public function purchasedNotes()
    {
        return $this->hasMany(PurchasedNote::class);
    }

    /**
     * Get buyer collections (wishlist).
     */
    public function collections()
    {
        return $this->hasMany(BuyerCollection::class)->orderBy('order');
    }

    /**
     * Get reading progress records.
     */
    public function readingProgress()
    {
        return $this->hasMany(ReadingProgress::class);
    }

    /**
     * Get bookmarks.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class)->orderBy('order');
    }

    /**
     * Get AI analyses.
     */
    public function aiAnalyses()
    {
        return $this->hasMany(AiAnalysis::class);
    }

    /**
     * Get study materials.
     */
    public function studyMaterials()
    {
        return $this->hasMany(StudyMaterial::class);
    }

    /**
     * Get note downloads.
     */
    public function noteDownloads()
    {
        return $this->hasMany(NoteDownload::class);
    }

    /**
     * Get note view history.
     */
    public function noteViewHistory()
    {
        return $this->hasMany(NoteViewHistory::class)->latest('viewed_at');
    }

    /**
     * Get user's posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class)->latest();
    }

    /**
     * Get posts that user has liked.
     */
    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_likes', 'user_id', 'post_id')
            ->withTimestamps();
    }

    /**
     * Get user's comments.
     */
    public function postComments()
    {
        return $this->hasMany(PostComment::class)->latest();
    }

    /**
     * Get comments that user has liked.
     */
    public function likedComments(): BelongsToMany
    {
        return $this->belongsToMany(PostComment::class, 'comment_likes', 'user_id', 'comment_id')
            ->withTimestamps();
    }

    /**
     * Get users that this user follows.
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Get users that follow this user.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Check if user follows another user.
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Check if user is followed by another user.
     */
    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    /**
     * Check if user has purchased a note.
     */
    public function hasPurchasedNote($noteId): bool
    {
        return $this->purchasedNotes()->where('note_id', $noteId)->exists();
    }

    /**
     * Get purchased note record.
     */
    public function getPurchasedNote($noteId): ?PurchasedNote
    {
        return $this->purchasedNotes()->where('note_id', $noteId)->first();
    }

    /**
     * Get bookmarked posts.
     */
    public function bookmarkedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_bookmarks', 'user_id', 'post_id')
            ->withTimestamps()
            ->latest('post_bookmarks.created_at');
    }

    /**
     * Check if user has bookmarked a post.
     */
    public function hasBookmarked(Post $post): bool
    {
        return $this->bookmarkedPosts()->where('post_id', $post->id)->exists();
    }
}
