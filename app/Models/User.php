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
use App\Models\Point;
use App\Models\PointRedemption;
use App\Models\Level;
use App\Models\UserLevel;
use App\Models\ChatQuickReply;
use App\Models\ChatRating;
use App\Models\AffiliateLink;
use App\Models\AffiliateConversion;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\Certification;
use App\Models\UserCertification;

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
        'is_active',
        'suspended_at',
        'agreement_accepted_at',
        'agreement_version',
        'ktp_path',
        'document_type',
        'selfie_path',
        'verification_status',
        'verification_reviewed_at',
        'verification_reviewed_by',
        'verification_notes',
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
            'is_active' => 'boolean',
            'suspended_at' => 'datetime',
            'agreement_accepted_at' => 'datetime',
            'verification_reviewed_at' => 'datetime',
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

    public function isSuspended(): bool
    {
        return (bool) $this->suspended_at;
    }

    public function isDeactivated(): bool
    {
        return ! $this->is_active;
    }

    public function isAccessible(): bool
    {
        return $this->is_active && ! $this->isSuspended();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('suspended_at');
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

    public function sharePoints()
    {
        return $this->hasMany(SharePoint::class);
    }

    public function monthlyShareRewards()
    {
        return $this->hasMany(MonthlyShareReward::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at', 'notes')
            ->withTimestamps()
            ->orderByPivot('earned_at', 'desc');
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Check if user has a specific badge.
     */
    public function hasBadge(Badge $badge): bool
    {
        return $this->badges()->where('badges.id', $badge->id)->exists();
    }

    /**
     * Get badges by category.
     */
    public function getBadgesByCategory(string $category)
    {
        return $this->badges()->where('badges.category', $category)->get();
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class, 'user_id');
    }

    public function pointRedemptions(): HasMany
    {
        return $this->hasMany(PointRedemption::class, 'user_id');
    }

    public function userLevels(): HasMany
    {
        return $this->hasMany(UserLevel::class);
    }

    public function sellerLevel(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'user_levels')
            ->where('user_levels.type', 'seller')
            ->where('levels.type', 'seller')
            ->withPivot('achieved_at', 'notes')
            ->withTimestamps()
            ->orderBy('levels.level_order', 'desc')
            ->limit(1);
    }

    public function buyerLevel(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'user_levels')
            ->where('user_levels.type', 'buyer')
            ->where('levels.type', 'buyer')
            ->withPivot('achieved_at', 'notes')
            ->withTimestamps()
            ->orderBy('levels.level_order', 'desc')
            ->limit(1);
    }

    /**
     * Get current seller level.
     */
    public function getCurrentSellerLevelAttribute()
    {
        return $this->userLevels()
            ->where('type', 'seller')
            ->with('level')
            ->orderBy('achieved_at', 'desc')
            ->first()?->level;
    }

    /**
     * Get current buyer level.
     */
    public function getCurrentBuyerLevelAttribute()
    {
        return $this->userLevels()
            ->where('type', 'buyer')
            ->with('level')
            ->orderBy('achieved_at', 'desc')
            ->first()?->level;
    }

    /**
     * Get total available points (not redeemed, not expired).
     */
    public function getTotalPointsAttribute(): int
    {
        return $this->points()
            ->where('is_redeemed', false)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->sum('points');
    }

    /**
     * Get total points ever earned.
     */
    public function getTotalPointsEarnedAttribute(): int
    {
        return $this->points()->sum('points');
    }

    /**
     * Get total points redeemed.
     */
    public function getTotalPointsRedeemedAttribute(): int
    {
        return $this->points()->where('is_redeemed', true)->sum('points');
    }

    /**
     * Get total share points earned by user.
     */
    public function getTotalSharePointsAttribute(): int
    {
        return $this->sharePoints()->sum('points');
    }

    /**
     * Get share points for current month.
     */
    public function getCurrentMonthSharePointsAttribute(): int
    {
        return $this->sharePoints()
            ->whereYear('earned_date', now()->year)
            ->whereMonth('earned_date', now()->month)
            ->sum('points');
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
     * @deprecated Premium features have been removed. This method always returns true for backward compatibility.
     */
    public function hasPremium(): bool
    {
        // All users have access to all features (premium removed)
        return true;
    }

    /**
     * Get note creation limit based on subscription.
     * @deprecated Premium features have been removed. All users have unlimited notes.
     */
    public function getNoteCreationLimit(): int
    {
        // All users have unlimited notes
        return -1; // Unlimited
    }

    /**
     * Check if user can create more notes.
     * @deprecated Premium features have been removed. All users can create unlimited notes.
     */
    public function canCreateMoreNotes(): bool
    {
        // All users can create unlimited notes
        return true;
    }

    /**
     * Get purchased notes by this user.
     */
    public function purchasedNotes()
    {
        return $this->hasMany(PurchasedNote::class);
    }

    /**
     * Get AI feature usage records.
     */
    public function aiFeatureUsages(): HasMany
    {
        return $this->hasMany(AiFeatureUsage::class);
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
     * @deprecated AI features have been removed. This relationship is kept for backward compatibility.
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
     * Get user preferences.
     */
    public function preference()
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Get view history (alias for noteViewHistory).
     */
    public function viewHistory()
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

    /**
     * Get chat quick replies for user.
     */
    public function chatQuickReplies(): HasMany
    {
        return $this->hasMany(ChatQuickReply::class);
    }

    /**
     * Get chat ratings given by user.
     */
    public function chatRatingsGiven(): HasMany
    {
        return $this->hasMany(ChatRating::class, 'rater_id');
    }

    /**
     * Get chat ratings received by user.
     */
    public function chatRatingsReceived(): HasMany
    {
        return $this->hasMany(ChatRating::class, 'rated_user_id');
    }

    /**
     * Get affiliate links.
     */
    public function affiliateLinks(): HasMany
    {
        return $this->hasMany(AffiliateLink::class, 'affiliate_id');
    }

    /**
     * Get affiliate conversions.
     */
    public function affiliateConversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class, 'affiliate_id');
    }

    /**
     * Get affiliate commissions.
     */
    public function affiliateCommissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'affiliate_id');
    }

    /**
     * Get affiliate payouts.
     */
    public function affiliatePayouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class, 'affiliate_id');
    }

    /**
     * Get user email preferences
     */
    public function emailPreference(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserEmailPreference::class);
    }

    /**
     * Get user certifications
     */
    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'user_certifications')
            ->withPivot('status', 'application_notes', 'admin_notes', 'approved_by', 'applied_at', 'approved_at', 'rejected_at', 'expires_at', 'evidence')
            ->withTimestamps()
            ->orderByPivot('approved_at', 'desc');
    }

    /**
     * Get user certification records
     */
    public function userCertifications(): HasMany
    {
        return $this->hasMany(UserCertification::class);
    }

    /**
     * Get approved certifications
     */
    public function approvedCertifications()
    {
        return $this->userCertifications()
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('certification')
            ->get();
    }

    /**
     * Check if user has a specific certification (approved)
     */
    public function hasCertification(Certification $certification): bool
    {
        return $this->userCertifications()
            ->where('certification_id', $certification->id)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Get contest entries
     */
    public function contestEntries(): HasMany
    {
        return $this->hasMany(\App\Models\ContestEntry::class);
    }

    /**
     * Get contest votes
     */
    public function contestVotes(): HasMany
    {
        return $this->hasMany(\App\Models\ContestVote::class);
    }

    /**
     * Get contest wins
     */
    public function contestWins(): HasMany
    {
        return $this->hasMany(\App\Models\ContestWinner::class);
    }

    /**
     * Get note subscriptions
     */
    public function noteSubscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\NoteSubscription::class);
    }

    /**
     * Get active note subscriptions
     */
    public function activeNoteSubscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\NoteSubscription::class)
            ->where('status', 'active')
            ->where('current_period_end', '>', now());
    }

    /**
     * Check if user has active subscription to a note
     */
    public function hasNoteSubscription(\App\Models\Note $note): bool
    {
        return $this->noteSubscriptions()
            ->where('note_id', $note->id)
            ->where('status', 'active')
            ->where('current_period_end', '>', now())
            ->exists();
    }
}
