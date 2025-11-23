<?php

namespace App\Services;

use App\Models\User;
use App\Models\Note;
use App\Models\EmailCampaign;
use App\Models\EmailSequence;
use App\Models\EmailCampaignRecipient;
use App\Models\AbandonedCart;
use App\Models\UserEmailPreference;
use App\Models\EmailTemplate;
use App\Models\EmailAbTest;
use App\Models\EmailUnsubscribe;
use App\Mail\AbandonedCartMail;
use App\Mail\NewNoteFromFollowingMail;
use App\Mail\WeeklyDigestMail;
use App\Mail\SequenceEmailMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EmailCampaignService
{
    protected ?string $provider = null;
    protected array $providerConfig = [];

    public function __construct()
    {
        $this->provider = config('services.email_campaign.provider', 'laravel'); // laravel, mailchimp, sendgrid
        $this->providerConfig = config('services.email_campaign', []);
    }

    /**
     * Check if email is unsubscribed
     */
    protected function isUnsubscribed(string $email): bool
    {
        return EmailUnsubscribe::isUnsubscribed($email);
    }

    /**
     * Generate tracking token
     */
    protected function generateTrackingToken(): string
    {
        return Str::random(64);
    }

    /**
     * Send email via configured provider with tracking
     */
    protected function sendEmail(string $to, $mailable, array $options = []): bool
    {
        // Check if unsubscribed
        if ($this->isUnsubscribed($to)) {
            Log::info('Email not sent - user unsubscribed', ['email' => $to]);
            return false;
        }

        try {
            switch ($this->provider) {
                case 'mailchimp':
                    return $this->sendViaMailchimp($to, $mailable, $options);
                case 'sendgrid':
                    return $this->sendViaSendGrid($to, $mailable, $options);
                default:
                    // Use Laravel's default mail system
                    Mail::to($to)->send($mailable);
                    return true;
            }
        } catch (\Exception $e) {
            Log::error('Email campaign send failed', [
                'to' => $to,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send email with tracking and create recipient record
     */
    protected function sendEmailWithTracking(
        string $to,
        User $user,
        $mailable,
        ?EmailCampaign $campaign = null,
        ?EmailSequence $sequence = null,
        ?EmailAbTest $abTest = null,
        ?string $abVariantId = null
    ): ?EmailCampaignRecipient {
        // Check if unsubscribed
        if ($this->isUnsubscribed($to)) {
            return null;
        }

        $trackingToken = $this->generateTrackingToken();
        
        // Create recipient record before sending
        $recipient = EmailCampaignRecipient::create([
            'campaign_id' => $campaign?->id,
            'user_id' => $user->id,
            'sequence_id' => $sequence?->id,
            'status' => 'pending',
            'tracking_token' => $trackingToken,
            'ab_test_id' => $abTest?->id,
            'ab_variant_id' => $abVariantId,
        ]);

        try {
            // Inject tracking token into mailable
            if (method_exists($mailable, 'setTrackingToken')) {
                $mailable->setTrackingToken($trackingToken);
            }

            $this->sendEmail($to, $mailable);
            
            $recipient->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            
            return $recipient;
        } catch (\Exception $e) {
            $recipient->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            Log::error('Email send failed', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Send via Mailchimp API
     */
    protected function sendViaMailchimp(string $to, $mailable, array $options = []): bool
    {
        $apiKey = $this->providerConfig['mailchimp']['api_key'] ?? null;
        $listId = $this->providerConfig['mailchimp']['list_id'] ?? null;

        if (!$apiKey || !$listId) {
            Log::warning('Mailchimp not configured, falling back to Laravel mail');
            Mail::to($to)->send($mailable);
            return true;
        }

        // For now, fallback to Laravel mail
        // In production, implement Mailchimp Transactional API or Mandrill
        Mail::to($to)->send($mailable);
        return true;
    }

    /**
     * Send via SendGrid API
     */
    protected function sendViaSendGrid(string $to, $mailable, array $options = []): bool
    {
        $apiKey = $this->providerConfig['sendgrid']['api_key'] ?? null;

        if (!$apiKey) {
            Log::warning('SendGrid not configured, falling back to Laravel mail');
            Mail::to($to)->send($mailable);
            return true;
        }

        // For now, fallback to Laravel mail
        // In production, implement SendGrid API
        Mail::to($to)->send($mailable);
        return true;
    }

    /**
     * Track abandoned cart when user views a note
     */
    public function trackAbandonedCart(?User $user, Note $note, ?string $email = null, ?string $ipAddress = null): void
    {
        // Check if user has abandoned cart emails enabled
        if ($user) {
            $preference = UserEmailPreference::firstOrCreate(
                ['user_id' => $user->id],
                ['abandoned_cart_emails' => true]
            );

            if (!$preference->abandoned_cart_emails) {
                return;
            }
        }

        // Don't track if note is free or already purchased
        if ($note->price <= 0) {
            return;
        }

        if ($user && $user->hasPurchasedNote($note->id)) {
            return;
        }

        // Check if already exists and not purchased
        $abandonedCart = AbandonedCart::where('note_id', $note->id)
            ->where(function ($query) use ($user, $email) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } else {
                    $query->where('email', $email);
                }
            })
            ->where('purchased', false)
            ->first();

        if ($abandonedCart) {
            // Update viewed_at
            $abandonedCart->update(['viewed_at' => now()]);
        } else {
            // Create new abandoned cart
            AbandonedCart::create([
                'user_id' => $user?->id,
                'note_id' => $note->id,
                'email' => $email ?? $user?->email,
                'ip_address' => $ipAddress ?? request()->ip(),
                'viewed_at' => now(),
            ]);
        }
    }

    /**
     * Send abandoned cart emails
     */
    public function sendAbandonedCartEmails(): int
    {
        $sent = 0;
        
        // Get abandoned carts that haven't been sent email yet or need follow-up
        $abandonedCarts = AbandonedCart::where('purchased', false)
            ->where(function ($query) {
                $query->whereNull('email_sent_at')
                    ->orWhere('email_sent_at', '<', now()->subHours(24)); // Follow-up after 24 hours
            })
            ->where('email_count', '<', 3) // Max 3 emails
            ->where('viewed_at', '<=', now()->subHours(1)) // Wait at least 1 hour after view
            ->with(['note', 'user'])
            ->get();

        foreach ($abandonedCarts as $cart) {
            $email = $cart->email ?? $cart->user?->email;
            
            if (!$email) {
                continue;
            }

            // Check preferences
            if ($cart->user) {
                $preference = UserEmailPreference::firstOrCreate(
                    ['user_id' => $cart->user->id],
                    ['abandoned_cart_emails' => true]
                );

                if (!$preference->abandoned_cart_emails) {
                    continue;
                }
            }

            try {
                $mailable = new AbandonedCartMail($cart->note, $cart->user);
                $this->sendEmail($email, $mailable);

                $cart->increment('email_count');
                $cart->update(['email_sent_at' => now()]);

                $sent++;
            } catch (\Exception $e) {
                Log::error('Failed to send abandoned cart email', [
                    'cart_id' => $cart->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Send new note notification to followers
     */
    public function sendNewNoteNotificationToFollowers(Note $note): int
    {
        $sent = 0;
        $seller = $note->user;

        if (!$seller) {
            return $sent;
        }

        $followers = $seller->followers()
            ->where('users.id', '!=', $seller->id)
            ->get();

        foreach ($followers as $follower) {
            $preference = UserEmailPreference::firstOrCreate(
                ['user_id' => $follower->id],
                ['new_note_notifications' => true]
            );

            if (!$preference->new_note_notifications) {
                continue;
            }

            if (!$follower->email) {
                continue;
            }

            try {
                $mailable = new NewNoteFromFollowingMail($note, $seller, $follower);
                $this->sendEmail($follower->email, $mailable);

                $sent++;
            } catch (\Exception $e) {
                Log::error('Failed to send new note notification', [
                    'note_id' => $note->id,
                    'follower_id' => $follower->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Send weekly digest with recommended notes
     */
    public function sendWeeklyDigest(User $user): bool
    {
        $preference = UserEmailPreference::firstOrCreate(
            ['user_id' => $user->id],
            ['weekly_digest' => true]
        );

        if (!$preference->weekly_digest) {
            return false;
        }

        if (!$user->email) {
            return false;
        }

        // Get recommended notes based on user's interests
        $recommendedNotes = $this->getRecommendedNotes($user);

        try {
            $mailable = new WeeklyDigestMail($user, $recommendedNotes);
            $this->sendEmail($user->email, $mailable);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send weekly digest', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get recommended notes for user
     */
    protected function getRecommendedNotes(User $user, int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "weekly_digest_notes_{$user->id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($user, $limit) {
            // Get notes from followed sellers
            $followedSellerNotes = Note::publicOnly()
                ->where('status', 'active')
                ->whereHas('user', function ($query) use ($user) {
                    $query->whereIn('users.id', $user->following()->pluck('following_id'));
                })
                ->where('created_at', '>=', now()->subDays(7))
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            if ($followedSellerNotes->count() >= $limit) {
                return $followedSellerNotes;
            }

            // Fill with trending notes
            $trendingNotes = Note::publicOnly()
                ->where('status', 'active')
                ->whereNotIn('id', $followedSellerNotes->pluck('id'))
                ->withCount(['transactions' => function ($query) {
                    $query->where('created_at', '>=', now()->subDays(7));
                }])
                ->orderByDesc('transactions_count')
                ->orderByDesc('created_at')
                ->limit($limit - $followedSellerNotes->count())
                ->get();

            return $followedSellerNotes->merge($trendingNotes);
        });
    }

    /**
     * Process email sequences
     */
    public function processEmailSequences(): int
    {
        $processed = 0;
        
        $sequences = EmailSequence::where('is_active', true)
            ->with('campaign')
            ->orderBy('order')
            ->get();

        foreach ($sequences as $sequence) {
            $users = $this->getUsersForSequence($sequence);
            
            foreach ($users as $user) {
                // Check if already sent
                $alreadySent = EmailCampaignRecipient::where('user_id', $user->id)
                    ->where('sequence_id', $sequence->id)
                    ->where('status', 'sent')
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                // Check preferences
                $preference = UserEmailPreference::firstOrCreate(
                    ['user_id' => $user->id],
                    ['sequence_emails' => true]
                );

                if (!$preference->sequence_emails) {
                    continue;
                }

                // Check if delay has passed
                $triggerDate = $this->getTriggerDate($user, $sequence->trigger_event);
                if (!$triggerDate) {
                    continue;
                }

                $sendDate = $triggerDate->addDays($sequence->delay_days)->addHours($sequence->delay_hours);
                
                if ($sendDate->isFuture()) {
                    continue;
                }

                try {
                    $mailable = new SequenceEmailMail($sequence, $user);
                    $this->sendEmail($user->email, $mailable);

                    EmailCampaignRecipient::create([
                        'campaign_id' => $sequence->campaign_id,
                        'user_id' => $user->id,
                        'sequence_id' => $sequence->id,
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);

                    $processed++;
                } catch (\Exception $e) {
                    Log::error('Failed to send sequence email', [
                        'sequence_id' => $sequence->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $processed;
    }

    /**
     * Get users for a sequence based on trigger event
     */
    protected function getUsersForSequence(EmailSequence $sequence): \Illuminate\Database\Eloquent\Collection
    {
        return match ($sequence->trigger_event) {
            'user_registered' => User::where('created_at', '>=', now()->subDays(30))->get(),
            'first_purchase' => User::whereHas('transactions', function ($query) {
                $query->where('status', 'success');
            })->get(),
            'no_purchase_7days' => User::whereDoesntHave('transactions', function ($query) {
                $query->where('created_at', '>=', now()->subDays(7))
                    ->where('status', 'success');
            })->where('created_at', '<=', now()->subDays(7))->get(),
            default => collect(),
        };
    }

    /**
     * Get trigger date for user based on event
     */
    protected function getTriggerDate(User $user, string $event): ?\Carbon\Carbon
    {
        return match ($event) {
            'user_registered' => $user->created_at,
            'first_purchase' => $user->transactions()->where('status', 'success')->orderBy('created_at')->first()?->created_at,
            'no_purchase_7days' => $user->created_at,
            default => null,
        };
    }

    /**
     * Mark abandoned cart as purchased
     */
    public function markAbandonedCartAsPurchased(User $user, Note $note): void
    {
        AbandonedCart::where('user_id', $user->id)
            ->where('note_id', $note->id)
            ->where('purchased', false)
            ->update([
                'purchased' => true,
                'purchased_at' => now(),
            ]);
    }

    /**
     * Create A/B test for email campaign
     */
    public function createAbTest(
        EmailCampaign $campaign,
        string $name,
        string $testType,
        array $variants,
        int $splitPercentage = 50
    ): EmailAbTest {
        return EmailAbTest::create([
            'campaign_id' => $campaign->id,
            'name' => $name,
            'test_type' => $testType,
            'variants' => $variants,
            'split_percentage' => $splitPercentage,
            'status' => 'draft',
        ]);
    }

    /**
     * Get email template for type
     */
    public function getTemplate(string $type, bool $useCustom = true): ?EmailTemplate
    {
        if ($useCustom) {
            $template = EmailTemplate::where('type', $type)
                ->where('is_active', true)
                ->where('is_default', false)
                ->first();
            
            if ($template) {
                return $template;
            }
        }
        
        return EmailTemplate::getDefault($type);
    }
}

