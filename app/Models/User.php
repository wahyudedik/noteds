<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable, HasRoles;

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
        ];
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
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
}
