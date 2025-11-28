<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use App\Models\Note;
use App\Models\Workspace;
use App\Models\FeaturedNote;
use App\Models\Withdraw;
use App\Models\NoteConversation;
use App\Models\NoteReview;
use App\Models\NoteReviewReply;
use App\Models\Setting;
use App\Models\Badge;
use App\Models\Level;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Bus;
use App\Mail\ForumNotificationMail;
use App\Jobs\SendNotificationJob;
use App\Jobs\SendBatchNotificationsJob;
use App\Jobs\SendEmailJob;
use App\Models\NoteMessage;

class NotificationService
{
    private const POPULARITY_THRESHOLDS = [10, 25, 50, 100];

    private function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function getWalletLowBalanceThreshold(): float
    {
        return (float) Setting::getSetting('wallet_low_balance_threshold', 'wallet', 50000);
    }

    public function notifyWalletLowBalance(User $user, float $balance): AppNotification
    {
        return $this->create(
            $user,
            'wallet_low_balance',
            '⚠️ Wallet Balance Low',
            "Your wallet balance is running low: {$this->formatCurrency($balance)}. Top up to keep purchasing without interruption.",
            route('wallet.index'),
            [
                'balance' => $balance,
                'threshold' => $this->getWalletLowBalanceThreshold(),
            ]
        );
    }

    public function maybeNotifyLowBalance(User $user, float $balance): void
    {
        $threshold = $this->getWalletLowBalanceThreshold();

        if ($threshold <= 0) {
            return;
        }

        if ($balance <= $threshold) {
            $this->notifyWalletLowBalance($user, $balance);
        }
    }

    public function notifyAiJobCompleted(User $user, string $jobType, string $title, string $message, array $data = [], ?string $link = null): AppNotification
    {
        $payload = array_merge($data, [
            'job_type' => $jobType,
        ]);

        $notification = $this->create(
            $user,
            "ai_{$jobType}_completed",
            $title,
            $message,
            $link,
            $payload
        );

        $this->sendPushIfEnabled($user, $title, $message, array_merge($payload, [
            'link' => $link,
        ]));

        return $notification;
    }

    public function notifyWorkspaceDigest(User $user, Workspace $workspace, array $summary, array $events): AppNotification
    {
        $parts = [];
        if (($summary['notes_added'] ?? 0) > 0) {
            $parts[] = ($summary['notes_added'] ?? 0) . ' note baru';
        }
        if (($summary['members_joined'] ?? 0) > 0) {
            $parts[] = ($summary['members_joined'] ?? 0) . ' member baru';
        }
        if (($summary['invitations_sent'] ?? 0) > 0) {
            $parts[] = ($summary['invitations_sent'] ?? 0) . ' undangan';
        }

        $message = empty($parts)
            ? "Tidak ada aktivitas baru dalam 24 jam terakhir di workspace {$workspace->name}."
            : 'Aktivitas 24 jam terakhir: ' . implode(', ', $parts) . '.';

        $notification = $this->create(
            $user,
            'workspace_digest',
            "Workspace Digest • {$workspace->name}",
            $message,
            route('workspaces.show', $workspace),
            [
                'workspace_id' => $workspace->id,
                'summary' => $summary,
                'events' => $events,
            ]
        );

        $this->sendPushIfEnabled($user, "Workspace • {$workspace->name}", $message, [
            'workspace_id' => $workspace->id,
            'summary' => $summary,
        ]);

        return $notification;
    }

    public function notifySellerDailyDigest(User $seller, array $summary): AppNotification
    {
        $title = 'Marketplace Daily Digest';
        $message = "Penjualan 24 jam terakhir: {$summary['total_sales']} item, pendapatan {$this->formatCurrency($summary['revenue'])}.";

        if (!empty($summary['top_note_title'])) {
            $message .= " Top note: {$summary['top_note_title']} ({$summary['top_note_sales']} terjual).";
        }

        if (($summary['total_sales'] ?? 0) === 0) {
            $message .= ' Belum ada penjualan, pertimbangkan untuk mempromosikan atau menampilkan note unggulan.';
        } elseif (($summary['total_sales'] ?? 0) >= 3) {
            $message .= ' Pertahankan momentum dengan menampilkan note terlaris Anda!';
        }

        $notification = $this->create(
            $seller,
            'marketplace_daily_digest',
            $title,
            $message,
            route('notes.index'),
            $summary
        );

        $this->sendPushIfEnabled($seller, $title, $message, $summary);

        return $notification;
    }

    public function notifyFeaturedNoteExpiring(User $seller, FeaturedNote $featuredNote, int $daysLeft): AppNotification
    {
        $title = 'Featured Note Akan Berakhir';
        $message = "Penempatan featured note {$featuredNote->location} akan berakhir dalam {$daysLeft} hari. Perpanjang untuk menjaga visibilitas.";

        $notification = $this->create(
            $seller,
            'featured_note_expiring',
            $title,
            $message,
            route('featured-notes.index'),
            [
                'featured_note_id' => $featuredNote->id,
                'note_id' => $featuredNote->note_id,
                'location' => $featuredNote->location,
                'days_left' => $daysLeft,
            ]
        );

        $this->sendPushIfEnabled($seller, $title, $message, [
            'featured_note_id' => $featuredNote->id,
            'days_left' => $daysLeft,
        ]);

        return $notification;
    }

    public function notifyAdminHighValueWithdraw(Withdraw $withdraw, float $threshold): void
    {
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $message = "Withdraw sebesar {$this->formatCurrency((float)$withdraw->amount)} membutuhkan review. Threshold: {$this->formatCurrency($threshold)}.";
            $data = [
                'withdraw_id' => $withdraw->id,
                'user_id' => $withdraw->user_id,
                'amount' => $withdraw->amount,
                'threshold' => $threshold,
            ];

            $notification = $this->create(
                $admin,
                'admin_withdraw_alert',
                '⚠️ High-Value Withdraw Request',
                $message,
                route('admin.withdraws.show', $withdraw),
                $data
            );

            $this->sendPushIfEnabled($admin, $notification->title, $message, $data);
        }
    }

    /**
     * Notify all admins when a user uploads document identity and selfie and needs verification.
     */
    public function notifyAdminUserVerificationPending(User $user): void
    {
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $documentType = $user->document_type === 'kartu_pelajar' ? 'Kartu Pelajar' : 'KTP';
            $message = "User baru ({$user->name} - {$user->email}) telah mengupload {$documentType} dan foto selfie dan memerlukan verifikasi identitas.";
            $data = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $user->role,
            ];

            $notification = $this->create(
                $admin,
                'admin_user_verification_pending',
                '📋 User Perlu Verifikasi Identitas',
                $message,
                route('admin.users.pending-verification'),
                $data
            );

            $this->sendPushIfEnabled($admin, $notification->title, $message, $data);
        }
    }

    /**
     * Notify all admins when a new user registers.
     */
    public function notifyAdminNewUserRegistration(User $user): void
    {
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $message = "User baru telah mendaftar: {$user->name} ({$user->email}) sebagai {$user->role}.";
            $data = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $user->role,
            ];

            $notification = $this->create(
                $admin,
                'admin_new_user_registration',
                '👤 User Baru Mendaftar',
                $message,
                route('admin.users.show', $user),
                $data
            );

            $this->sendPushIfEnabled($admin, $notification->title, $message, $data);
        }
    }

    public function getHighValueWithdrawThreshold(): float
    {
        return (float) Setting::getSetting('withdraw_high_value_threshold', 'wallet', 1000000);
    }

    protected function sendPushIfEnabled(User $user, string $title, string $message, array $payload = []): void
    {
        if (!config('services.push.enabled', false)) {
            return;
        }

        // Check if user allows push notifications (need to extract type from payload or pass it)
        $type = $payload['type'] ?? 'general';
        if (!$user->allowsPushNotification($type)) {
            return;
        }

        // Don't send push during quiet hours
        if ($user->isInQuietHours()) {
            return;
        }

        try {
            logger()->info('Push notification queued', [
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'payload' => $payload,
            ]);
            // Integration point for real push provider (e.g., OneSignal, Firebase)
        } catch (\Exception $e) {
            logger()->warning('Failed to queue push notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a notification for a user.
     * Uses queue for better performance.
     * Respects user preferences and quiet hours.
     */
    public function create(User $user, string $type, string $title, string $message, ?string $link = null, ?array $data = null, bool $useQueue = true): ?AppNotification
    {
        // Check if user is in quiet hours
        if ($user->isInQuietHours()) {
            // Still create notification but don't send immediate email/push
            // User will see it when quiet hours end
            $notification = AppNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'data' => $data,
            ]);
            return $notification;
        }

        // Check in-app notification preference
        $allowInApp = $user->allowsInAppNotification($type);

        if (!$allowInApp) {
            // User disabled this notification type, skip entirely
            return null;
        }

        if ($useQueue && config('queue.default') !== 'sync') {
            // Dispatch to queue for async processing
            SendNotificationJob::dispatch($user->id, $type, $title, $message, $link, $data)
                ->onQueue('notifications');

            // Still create notification immediately for instant display
            // The queue job will handle email sending
            $notification = AppNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'data' => $data,
            ]);
        } else {
            // Synchronous processing (for testing or sync queue)
            $notification = AppNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'data' => $data,
            ]);

            // Check email preference before sending
            if ($user->allowsEmailNotification($type)) {
                $this->sendForumEmailIfEnabled($user, $type, $title, $message, $link);
            }
        }

        return $notification;
    }

    /**
     * Create notifications for multiple users in batch.
     */
    public function createBatch(array $userIds, string $type, string $title, string $message, ?string $link = null, ?array $data = null): void
    {
        if (config('queue.default') !== 'sync') {
            // Split into chunks for better performance
            $chunks = array_chunk($userIds, 100);

            foreach ($chunks as $chunk) {
                SendBatchNotificationsJob::dispatch($chunk, $type, $title, $message, $link, $data)
                    ->onQueue('notifications');
            }
        } else {
            // Synchronous processing
            $users = User::whereIn('id', $userIds)->get();
            foreach ($users as $user) {
                $this->create($user, $type, $title, $message, $link, $data, false);
            }
        }
    }

    /**
     * Notify user about a new purchase.
     */
    public function notifyPurchase(User $user, Note $note, float $amount, string $transactionId, array $breakdown = []): AppNotification
    {
        $lines = [
            "You successfully purchased: {$note->title}.",
        ];

        if (!empty($breakdown)) {
            if (isset($breakdown['subtotal'])) {
                $lines[] = 'Subtotal: ' . $this->formatCurrency((float) $breakdown['subtotal']) . '.';
            }
            if (isset($breakdown['tax_amount'])) {
                $percentLabel = isset($breakdown['tax_percent']) ? "{$breakdown['tax_percent']}%" : '';
                $lines[] = 'Tax' . ($percentLabel ? " ({$percentLabel})" : '') . ': ' . $this->formatCurrency((float) $breakdown['tax_amount']) . '.';
            }
            if (!empty($breakdown['commission_tier'])) {
                $lines[] = 'Commission tier: ' . $breakdown['commission_tier'] . '.';
            }
        }

        $lines[] = 'Total Paid: ' . $this->formatCurrency($breakdown['total'] ?? $amount) . '.';
        $message = implode(' ', $lines);

        return $this->create(
            $user,
            'purchase',
            '✅ Purchase Successful!',
            $message,
            route('marketplace.show', $note),
            [
                'transaction_id' => $transactionId,
                'note_id' => $note->id,
                'amount' => $amount,
                'breakdown' => $breakdown,
            ]
        );
    }

    /**
     * Notify seller about a new sale.
     */
    public function notifySale(User $seller, Note $note, float $amount, string $buyerName, array $breakdown = []): AppNotification
    {
        $lines = [
            "{$buyerName} purchased your note: {$note->title}.",
        ];

        if (!empty($breakdown)) {
            if (isset($breakdown['subtotal'])) {
                $lines[] = 'Subtotal: ' . $this->formatCurrency((float) $breakdown['subtotal']) . '.';
            }
            if (isset($breakdown['tax_amount'])) {
                $percentLabel = isset($breakdown['tax_percent']) ? "{$breakdown['tax_percent']}%" : '';
                $lines[] = 'Tax' . ($percentLabel ? " ({$percentLabel})" : '') . ': ' . $this->formatCurrency((float) $breakdown['tax_amount']) . '.';
            }
            if (isset($breakdown['platform_fee'])) {
                $lines[] = 'Platform fee: ' . $this->formatCurrency((float) $breakdown['platform_fee']) . '.';
            }
            if (isset($breakdown['creator_commission']) && (float) $breakdown['creator_commission'] > 0) {
                $lines[] = 'Creator commission: ' . $this->formatCurrency((float) $breakdown['creator_commission']) . '.';
            }
            if (isset($breakdown['net_amount'])) {
                $lines[] = 'Net received: ' . $this->formatCurrency((float) $breakdown['net_amount']) . '.';
            }
            if (!empty($breakdown['commission_tier'])) {
                $lines[] = 'Commission tier: ' . $breakdown['commission_tier'] . '.';
            }
        }

        $lines[] = 'Total sale: ' . $this->formatCurrency($breakdown['total'] ?? $amount) . '.';
        $message = implode(' ', $lines);

        return $this->create(
            $seller,
            'sale',
            '💰 New Sale!',
            $message,
            route('notes.index'),
            [
                'buyer_name' => $buyerName,
                'note_id' => $note->id,
                'amount' => $amount,
                'breakdown' => $breakdown,
            ]
        );
    }

    public function notifyAccountSuspended(User $user, ?string $reason = null, ?User $admin = null): AppNotification
    {
        $title = '🚫 ' . __('messages.account_suspended_title');
        $adminName = $admin?->name ?? 'Admin';
        $reasonSuffix = $reason ? ' ' . __('messages.account_action_reason', ['reason' => $reason]) : '';
        $message = __('messages.account_suspended_notification', ['admin' => $adminName]) . $reasonSuffix;

        return $this->create(
            $user,
            'account_suspended',
            $title,
            $message,
            null,
            [
                'reason' => $reason,
                'admin_id' => $admin?->id,
            ]
        );
    }

    public function notifyAccountReactivated(User $user, ?User $admin = null): AppNotification
    {
        $title = '✅ ' . __('messages.account_reactivated_title');
        $adminName = $admin?->name ?? 'Admin';
        $message = __('messages.account_reactivated_notification', ['admin' => $adminName]);

        return $this->create(
            $user,
            'account_reactivated',
            $title,
            $message,
            route('login'),
            [
                'admin_id' => $admin?->id,
            ]
        );
    }

    /**
     * Notify user about a review on their note.
     */
    public function notifyReview(User $user, string $noteTitle, int $rating, string $reviewerName): AppNotification
    {
        return $this->create(
            $user,
            'review',
            '⭐ New Review!',
            "{$reviewerName} rated your note '{$noteTitle}' with {$rating} stars.",
            route('notes.index'),
            ['rating' => $rating, 'reviewer' => $reviewerName]
        );
    }

    /**
     * Notify user about ticket response.
     */
    public function notifyTicketResponse(User $user, string $ticketTitle): AppNotification
    {
        return $this->create(
            $user,
            'ticket_response',
            '🎫 Ticket Response',
            "Admin has responded to your support ticket: {$ticketTitle}",
            route('support-tickets.index'),
            ['ticket_title' => $ticketTitle]
        );
    }

    /**
     * Notify user about subscription approval.
     */
    public function notifySubscriptionApproved(User $user, string $plan): AppNotification
    {
        return $this->create(
            $user,
            'subscription',
            '🎉 Subscription Approved!',
            "Your {$plan} subscription has been approved!",
            route('subscription.index'),
            ['plan' => $plan]
        );
    }

    /**
     * Notify user about withdrawal status.
     */
    public function notifyWithdrawal(User $user, string $status, float $amount): AppNotification
    {
        $title = $status === 'approved' ? '✅ Withdrawal Approved!' : '❌ Withdrawal Rejected';
        $message = $status === 'approved'
            ? "Your withdrawal of Rp " . number_format($amount, 0, ',', '.') . " has been approved."
            : "Your withdrawal request was rejected. Please contact support.";

        return $this->create(
            $user,
            'withdrawal',
            $title,
            $message,
            route('wallet.index'),
            ['amount' => $amount, 'status' => $status]
        );
    }

    /**
     * Notify user about subscription renewal success.
     */
    public function notifySubscriptionRenewed(User $user, float $amount): AppNotification
    {
        return $this->create(
            $user,
            'subscription_renewed',
            '✅ Subscription Renewed',
            "Your premium subscription has been automatically renewed for another month. Rp " . number_format($amount, 0, ',', '.') . " has been deducted from your wallet.",
            route('subscription.index'),
            ['amount' => $amount, 'renewed_at' => now()]
        );
    }

    /**
     * Notify user about subscription expiration due to insufficient balance.
     */
    public function notifySubscriptionExpired(User $user, float $requiredAmount, float $currentBalance): AppNotification
    {
        return $this->create(
            $user,
            'subscription_expired',
            '⚠️ Premium Subscription Expired',
            "Your premium subscription has expired due to insufficient wallet balance (Rp " . number_format($requiredAmount, 0, ',', '.') . " required). Please top up your wallet to reactivate.",
            route('wallet.index'),
            ['required_amount' => $requiredAmount, 'current_balance' => $currentBalance]
        );
    }

    public function notifySubscriptionRenewalReminder(User $user, float $amount, float $currentBalance): AppNotification
    {
        return $this->create(
            $user,
            'subscription_renewal_reminder',
            '⏰ Premium Renewal Reminder',
            "Your premium subscription will renew soon for {$this->formatCurrency($amount)}. Current wallet balance: {$this->formatCurrency($currentBalance)}.",
            route('subscription.index'),
            [
                'required_amount' => $amount,
                'current_balance' => $currentBalance,
            ]
        );
    }

    public function notifyTopupSuccess(User $user, float $amount, float $newBalance, string $transactionId): AppNotification
    {
        $notification = $this->create(
            $user,
            'wallet_topup_success',
            '💳 Top-up Successful',
            "Your wallet top-up of {$this->formatCurrency($amount)} is complete. Current balance: {$this->formatCurrency($newBalance)}.",
            route('wallet.index'),
            [
                'amount' => $amount,
                'balance' => $newBalance,
                'transaction_id' => $transactionId,
            ]
        );

        $this->maybeNotifyLowBalance($user, $newBalance);

        return $notification;
    }

    public function notifyTopupFailed(User $user, float $amount, string $status, ?string $transactionId = null): AppNotification
    {
        return $this->create(
            $user,
            'wallet_topup_failed',
            '⚠️ Top-up Issue',
            "Your top-up of {$this->formatCurrency($amount)} could not be completed (status: {$status}). Please retry or contact support if the issue persists.",
            route('wallet.index'),
            [
                'amount' => $amount,
                'status' => $status,
                'transaction_id' => $transactionId,
            ]
        );
    }

    public function notifyWithdrawRequested(User $user, float $amount): AppNotification
    {
        return $this->create(
            $user,
            'withdraw_requested',
            '🏦 Withdraw Request Submitted',
            "We received your withdraw request for {$this->formatCurrency($amount)}. We'll notify you once it has been processed.",
            route('wallet.index'),
            ['amount' => $amount]
        );
    }

    public function notifyWithdrawProcessed(User $user, string $status, float $amount, ?string $adminNotes = null, ?float $remainingBalance = null): AppNotification
    {
        $title = $status === 'approved' ? '✅ Withdraw Approved' : '❌ Withdraw Rejected';
        $fallbackNotes = 'Please review your account details or contact support.';
        $message = $status === 'approved'
            ? "Your withdraw request for {$this->formatCurrency($amount)} has been approved. Funds will arrive shortly."
            : "Your withdraw request for {$this->formatCurrency($amount)} was rejected. " . ($adminNotes ?: $fallbackNotes);

        $notification = $this->create(
            $user,
            "withdraw_{$status}",
            $title,
            $message,
            route('wallet.index'),
            [
                'amount' => $amount,
                'status' => $status,
                'admin_notes' => $adminNotes,
            ]
        );

        if ($status === 'approved' && $remainingBalance !== null) {
            $this->maybeNotifyLowBalance($user, $remainingBalance);
        }

        return $notification;
    }

    public function notifyNewNotePublished(Note $note): void
    {
        if (!$note->is_public || $note->status !== 'active') {
            return;
        }

        $seller = $note->user;
        if (!$seller) {
            return;
        }

        $this->create(
            $seller,
            'note_published',
            '📚 Note Published',
            "Your note \"{$note->title}\" is now live in the marketplace.",
            route('notes.show', $note),
            [
                'note_id' => $note->id,
            ]
        );

        $this->notifyFollowersAboutNewNote($note);
    }

    protected function notifyFollowersAboutNewNote(Note $note): void
    {
        $seller = $note->user;
        if (!$seller) {
            return;
        }

        $seller->loadMissing('followers');
        foreach ($seller->followers as $follower) {
            if ($follower->id === $seller->id) {
                continue;
            }

            $this->create(
                $follower,
                'note_new_from_following',
                "📚 {$seller->name} published a new note",
                "\"{$note->title}\" just dropped. Check it out!",
                route('marketplace.show', $note),
                [
                    'note_id' => $note->id,
                    'seller_id' => $seller->id,
                ]
            );
        }

        // Also send email notifications via EmailCampaignService
        try {
            $emailCampaignService = app(\App\Services\EmailCampaignService::class);
            $emailCampaignService->sendNewNoteNotificationToFollowers($note);
        } catch (\Exception $e) {
            logger()->error('Failed to send email notifications for new note', [
                'note_id' => $note->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getPopularityThresholds(): array
    {
        return self::POPULARITY_THRESHOLDS;
    }

    public function notifyNotePopular(Note $note, int $milestone): void
    {
        $seller = $note->user;
        if (!$seller) {
            return;
        }

        $this->create(
            $seller,
            'note_popular',
            '🔥 Note is Trending',
            "\"{$note->title}\" has reached {$milestone} purchases. Keep up the momentum!",
            route('notes.index'),
            [
                'note_id' => $note->id,
                'milestone' => $milestone,
                'purchase_count' => $note->purchase_count,
            ]
        );
    }

    public function notifyReferralSignup(User $referrer, User $referred, float $rewardAmount): AppNotification
    {
        return $this->create(
            $referrer,
            'referral_signup',
            '🤝 Referral Joined',
            "{$referred->name} joined Noteds using your referral! Bonus earned: {$this->formatCurrency($rewardAmount)}.",
            route('referral.index'),
            [
                'referred_user_id' => $referred->id,
                'reward_amount' => $rewardAmount,
            ]
        );
    }

    public function notifyReferralPurchase(User $referrer, User $referred, float $rewardAmount, float $percent): AppNotification
    {
        return $this->create(
            $referrer,
            'referral_purchase',
            '💸 Referral Purchase Bonus',
            "{$referred->name} completed their first purchase. You earned {$this->formatCurrency($rewardAmount)} ({$percent}% bonus).",
            route('referral.statistics'),
            [
                'referred_user_id' => $referred->id,
                'reward_amount' => $rewardAmount,
                'percent' => $percent,
            ]
        );
    }

    /**
     * Notify user about commission earned from sharing a note.
     */
    public function notifyShareCommission(User $sharer, Note $note, float $commissionAmount, float $percent): AppNotification
    {
        return $this->create(
            $sharer,
            'share_commission',
            '🎁 Share Commission Earned',
            "Someone purchased '{$note->title}' through your share link! You earned {$this->formatCurrency($commissionAmount)} ({$percent}% commission).",
            route('marketplace.show', $note),
            [
                'note_id' => $note->id,
                'note_title' => $note->title,
                'commission_amount' => $commissionAmount,
                'percent' => $percent,
            ]
        );
    }

    public function notifyCreatorCommission(User $creator, Note $note, float $commissionAmount, ?User $seller = null): AppNotification
    {
        $sellerName = $seller ? $seller->name : 'a reseller';

        return $this->create(
            $creator,
            'creator_commission',
            '💼 Commission Earned',
            "You received {$this->formatCurrency($commissionAmount)} commission from \"{$note->title}\" thanks to {$sellerName}.",
            route('wallet.index'),
            [
                'note_id' => $note->id,
                'commission_amount' => $commissionAmount,
                'seller_id' => $seller?->id,
            ]
        );
    }

    /**
     * Notify user about a like on their post.
     */
    public function notifyPostLiked(User $user, string $postId, string $likerName, string $postContent): AppNotification
    {
        return $this->create(
            $user,
            'forum_post_liked',
            '❤️ Your post was liked',
            "{$likerName} liked your post: " . Str::limit($postContent, 50),
            route('forum.show', $postId),
            ['post_id' => $postId, 'liker_name' => $likerName]
        );
    }

    /**
     * Notify user about a comment on their post.
     */
    public function notifyPostCommented(User $user, string $postId, string $commenterName, string $commentContent): AppNotification
    {
        return $this->create(
            $user,
            'forum_post_commented',
            '💬 New comment on your post',
            "{$commenterName} commented on your post: " . Str::limit($commentContent, 50),
            route('forum.show', $postId),
            ['post_id' => $postId, 'commenter_name' => $commenterName]
        );
    }

    /**
     * Notify user about a reply to their comment.
     */
    public function notifyCommentReplied(User $user, string $postId, string $replierName, string $replyContent): AppNotification
    {
        return $this->create(
            $user,
            'forum_comment_replied',
            '💬 Reply to your comment',
            "{$replierName} replied to your comment: " . Str::limit($replyContent, 50),
            route('forum.show', $postId),
            ['post_id' => $postId, 'replier_name' => $replierName]
        );
    }

    /**
     * Notify user about a like on their comment.
     */
    public function notifyCommentLiked(User $user, string $postId, string $likerName): AppNotification
    {
        return $this->create(
            $user,
            'forum_comment_liked',
            '❤️ Your comment was liked',
            "{$likerName} liked your comment",
            route('forum.show', $postId),
            ['post_id' => $postId, 'liker_name' => $likerName]
        );
    }

    /**
     * Notify user about a new follower.
     */
    public function notifyNewFollower(User $user, string $followerName): AppNotification
    {
        // Get follower user to get their username
        $follower = User::where('name', $followerName)->first();
        $followerUsername = $follower ? $follower->username : null;

        return $this->create(
            $user,
            'forum_new_follower',
            '👤 New follower',
            "{$followerName} started following you",
            $followerUsername ? route('public.profile.show', $followerUsername) : null,
            ['follower_name' => $followerName]
        );
    }

    protected function sendForumEmailIfEnabled(User $user, string $type, string $title, string $message, ?string $link = null): void
    {
        if (!Str::startsWith($type, 'forum_')) {
            return;
        }

        // Check email preference
        if (!$user->allowsEmailNotification($type)) {
            return;
        }

        // Don't send email during quiet hours
        if ($user->isInQuietHours()) {
            return;
        }

        if (!$user->wantsForumEmail($type)) {
            return;
        }

        if (empty($user->email)) {
            return;
        }

        // Use queue for email sending
        if (config('queue.default') !== 'sync') {
            SendEmailJob::dispatch($user->email, new ForumNotificationMail($title, $message, $link))
                ->onQueue('emails');
        } else {
            Mail::to($user->email)->send(new ForumNotificationMail($title, $message, $link));
        }
    }

    public function notifyNoteChatMessage(User $recipient, NoteConversation $conversation, string $messagePreview, ?User $sender = null): AppNotification
    {
        $senderName = $sender?->name ?? 'pengguna';

        $notification = $this->create(
            $recipient,
            'note_chat_message',
            '💬 Pesan baru dari ' . $senderName,
            $messagePreview,
            route('note-conversations.show', $conversation),
            [
                'note_id' => $conversation->note_id,
                'conversation_id' => $conversation->id,
            ]
        );

        // Send push notification
        $this->sendPushIfEnabled($recipient, '💬 New Chat Message', $messagePreview, [
            'conversation_id' => $conversation->id,
            'note_id' => $conversation->note_id,
            'sender_id' => $sender?->id,
        ]);

        return $notification;
    }

    /**
     * Send email notification for chat message.
     */
    public function sendChatEmailNotification(User $recipient, NoteConversation $conversation, NoteMessage $message, ?User $sender = null): void
    {
        if (empty($recipient->email)) {
            return;
        }

        // Check if user has email notifications enabled for chat
        // This could be a user preference in the future
        // For now, send email notification

        $senderName = $sender?->name ?? 'pengguna';
        $noteTitle = $conversation->note->title ?? 'Unknown Note';
        $subject = __('chat.email_subject', ['sender' => $senderName]);
        $emailMessage = __('chat.email_body', [
            'sender' => $senderName,
            'note' => $noteTitle,
            'message' => Str::limit($message->message, 200),
        ]);

        if (config('queue.default') !== 'sync') {
            SendEmailJob::dispatch($recipient->email, new \App\Mail\ChatNotificationMail($subject, $emailMessage, route('note-conversations.show', $conversation)))
                ->onQueue('emails');
        } else {
            Mail::to($recipient->email)->send(new \App\Mail\ChatNotificationMail($subject, $emailMessage, route('note-conversations.show', $conversation)));
        }
    }

    public function notifyReviewReplied(User $recipient, NoteReview $review, NoteReviewReply $reply): AppNotification
    {
        return $this->create(
            $recipient,
            'note_review_replied',
            '💬 Balasan baru pada ulasan Anda',
            Str::limit($reply->message, 120),
            route('marketplace.show', $review->note) . '#review-' . $review->id,
            [
                'review_id' => $review->id,
                'reply_id' => $reply->id,
                'note_id' => $review->note_id,
            ]
        );
    }

    /**
     * Notify user when they earn a badge.
     */
    public function notifyBadgeEarned(User $user, Badge $badge): AppNotification
    {
        return $this->create(
            $user,
            'badge_earned',
            '🏆 Badge Earned!',
            "Congratulations! You've earned the '{$badge->name}' badge! {$badge->description}",
            route('public.profile.show', $user->username),
            [
                'badge_id' => $badge->id,
                'badge_name' => $badge->name,
                'badge_category' => $badge->category,
                'badge_icon' => $badge->icon,
            ]
        );
    }

    /**
     * Notify user when they level up.
     */
    public function notifyLevelUp(User $user, Level $level, string $type): AppNotification
    {
        $typeLabel = $type === 'seller' ? 'Seller' : 'Buyer';
        $benefits = $level->benefits_array;
        $benefitsText = !empty($benefits) ? "\n\nBenefits: " . implode(', ', $benefits) : '';

        return $this->create(
            $user,
            'level_up',
            '🎉 Level Up!',
            "Congratulations! You've reached {$typeLabel} Level: {$level->name}! {$level->description}{$benefitsText}",
            route('public.profile.show', $user->username),
            [
                'level_id' => $level->id,
                'level_name' => $level->name,
                'level_type' => $type,
                'level_icon' => $level->icon,
                'commission_discount' => $level->commission_discount_percent,
            ]
        );
    }
}
