<?php

namespace App\Services;

use App\Models\Note;
use App\Models\NoteViewRevenue;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class NoteViewMonetizationService
{
    private const VIEW_REVENUE_AMOUNT = 0.01; // 0.01 rupiah per view
    private const MIN_VIEW_DURATION = 3; // Minimum 3 detik untuk valid view
    private const MAX_VIEWS_PER_IP_PER_HOUR = 10; // Max 10 views per IP per hour
    private const MAX_VIEWS_PER_FINGERPRINT_PER_HOUR = 5; // Max 5 views per fingerprint per hour

    /**
     * Process view monetization for free notes
     */
    public function processView(Note $note, ?string $ipAddress = null, ?string $userAgent = null, ?string $fingerprint = null, ?int $userId = null): ?NoteViewRevenue
    {
        // Only process free notes (price = 0)
        if ($note->price > 0) {
            return null;
        }

        $ipAddress = $ipAddress ?? request()->ip();
        $userAgent = $userAgent ?? request()->userAgent();
        
        // Try to get fingerprint from session first, then generate
        $fingerprint = $fingerprint ?? Session::get('browser_fingerprint') ?? $this->generateFingerprint($ipAddress, $userAgent);
        
        // Store fingerprint in session for future requests
        if (!Session::has('browser_fingerprint')) {
            Session::put('browser_fingerprint', $fingerprint);
        }

        // Bot detection
        if ($this->isBot($userAgent, $ipAddress, $fingerprint)) {
            Log::warning('Bot detected for note view', [
                'note_id' => $note->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
            return null;
        }

        // Rate limiting checks
        if (!$this->checkRateLimit($note->id, $ipAddress, $fingerprint)) {
            Log::warning('Rate limit exceeded for note view', [
                'note_id' => $note->id,
                'ip_address' => $ipAddress,
                'fingerprint' => $fingerprint,
            ]);
            return null;
        }

        // Check for duplicate view (same IP + fingerprint within 1 hour)
        $recentView = NoteViewRevenue::where('note_id', $note->id)
            ->where(function($query) use ($ipAddress, $fingerprint) {
                $query->where('ip_address', $ipAddress)
                      ->orWhere('fingerprint', $fingerprint);
            })
            ->where('viewed_at', '>=', now()->subHour())
            ->first();

        if ($recentView) {
            return null; // Duplicate view within 1 hour
        }

        try {
            DB::beginTransaction();

            // Create view revenue record
            $viewRevenue = NoteViewRevenue::create([
                'note_id' => $note->id,
                'user_id' => $userId,
                'amount' => self::VIEW_REVENUE_AMOUNT,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'fingerprint' => $fingerprint,
                'is_valid' => true,
                'validation_status' => 'pending', // Will be validated later
                'bot_detection_data' => [
                    'user_agent' => $userAgent,
                    'ip_address' => $ipAddress,
                    'timestamp' => now()->toIso8601String(),
                ],
                'viewed_at' => now(),
            ]);

            // Credit wallet to note owner
            $this->creditWallet($note, self::VIEW_REVENUE_AMOUNT);

            // Update rate limit cache
            $this->updateRateLimitCache($note->id, $ipAddress, $fingerprint);

            // Send notification to note owner if accumulated revenue is significant
            $this->maybeNotifyOwner($note, $viewRevenue);

            DB::commit();

            Log::info('View revenue processed', [
                'note_id' => $note->id,
                'view_revenue_id' => $viewRevenue->id,
                'amount' => self::VIEW_REVENUE_AMOUNT,
            ]);

            return $viewRevenue;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process view revenue', [
                'note_id' => $note->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Generate browser fingerprint
     */
    protected function generateFingerprint(string $ipAddress, string $userAgent): string
    {
        $data = [
            $ipAddress,
            $userAgent,
            request()->header('Accept-Language', ''),
            request()->header('Accept-Encoding', ''),
        ];
        
        return hash('sha256', implode('|', $data));
    }

    /**
     * Detect if request is from bot
     */
    protected function isBot(?string $userAgent, string $ipAddress, string $fingerprint): bool
    {
        if (!$userAgent) {
            return true; // No user agent = likely bot
        }

        $userAgentLower = strtolower($userAgent);

        // Common bot patterns
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'headless', 'phantom',
            'selenium', 'webdriver', 'puppeteer', 'playwright', 'curl',
            'wget', 'python-requests', 'go-http', 'java/', 'httpclient',
            'apache-httpclient', 'okhttp', 'scrapy', 'facebookexternalhit',
            'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider',
            'yandexbot', 'sogou', 'exabot', 'facebot', 'ia_archiver',
        ];

        foreach ($botPatterns as $pattern) {
            if (str_contains($userAgentLower, $pattern)) {
                return true;
            }
        }

        // Check if IP is in known bot IP ranges (can be extended)
        // For now, we'll rely on user agent detection

        return false;
    }

    /**
     * Check rate limiting
     */
    protected function checkRateLimit(string $noteId, string $ipAddress, string $fingerprint): bool
    {
        $ipKey = "view_rate_limit:ip:{$ipAddress}";
        $fingerprintKey = "view_rate_limit:fingerprint:{$fingerprint}";

        $ipViews = Cache::get($ipKey, 0);
        $fingerprintViews = Cache::get($fingerprintKey, 0);

        if ($ipViews >= self::MAX_VIEWS_PER_IP_PER_HOUR) {
            return false;
        }

        if ($fingerprintViews >= self::MAX_VIEWS_PER_FINGERPRINT_PER_HOUR) {
            return false;
        }

        return true;
    }

    /**
     * Update rate limit cache
     */
    protected function updateRateLimitCache(string $noteId, string $ipAddress, string $fingerprint): void
    {
        $ipKey = "view_rate_limit:ip:{$ipAddress}";
        $fingerprintKey = "view_rate_limit:fingerprint:{$fingerprint}";

        $ipViews = Cache::get($ipKey, 0);
        $fingerprintViews = Cache::get($fingerprintKey, 0);

        Cache::put($ipKey, $ipViews + 1, now()->addHour());
        Cache::put($fingerprintKey, $fingerprintViews + 1, now()->addHour());
    }

    /**
     * Credit wallet to note owner
     */
    protected function creditWallet(Note $note, float $amount): void
    {
        $owner = $note->user;
        if (!$owner) {
            return;
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $owner->id],
            ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
        );

        $wallet->increment('balance', $amount);

        Log::info('Wallet credited for view revenue', [
            'user_id' => $owner->id,
            'note_id' => $note->id,
            'amount' => $amount,
            'new_balance' => $wallet->balance,
        ]);
    }

    /**
     * Validate pending views (can be called by cron job)
     */
    public function validatePendingViews(int $limit = 100): int
    {
        $pendingViews = NoteViewRevenue::pending()
            ->where('viewed_at', '>=', now()->subDays(7)) // Only validate views from last 7 days
            ->limit($limit)
            ->get();

        $validated = 0;

        foreach ($pendingViews as $view) {
            // Additional validation logic
            $isValid = $this->validateView($view);

            $view->update([
                'validation_status' => $isValid ? 'approved' : 'rejected',
                'is_valid' => $isValid,
                'rejection_reason' => $isValid ? null : 'Failed validation checks',
            ]);

            if (!$isValid && $view->is_valid) {
                // Refund if previously valid but now invalid
                $this->refundWallet($view);
            }

            $validated++;
        }

        return $validated;
    }

    /**
     * Validate individual view
     */
    protected function validateView(NoteViewRevenue $view): bool
    {
        // Check for suspicious patterns
        $suspiciousPatterns = [
            // Same IP viewing many different notes quickly
            NoteViewRevenue::where('ip_address', $view->ip_address)
                ->where('viewed_at', '>=', now()->subHour())
                ->count() > 20,
            
            // Same fingerprint viewing many notes
            NoteViewRevenue::where('fingerprint', $view->fingerprint)
                ->where('viewed_at', '>=', now()->subHour())
                ->count() > 15,
        ];

        return !in_array(true, $suspiciousPatterns);
    }

    /**
     * Refund wallet (if view is invalidated)
     */
    protected function refundWallet(NoteViewRevenue $view): void
    {
        $note = $view->note;
        $owner = $note->user;

        if (!$owner) {
            return;
        }

        $wallet = Wallet::where('user_id', $owner->id)->first();
        if ($wallet && $wallet->balance >= $view->amount) {
            $wallet->decrement('balance', $view->amount);
            
            Log::info('Wallet refunded for invalid view', [
                'user_id' => $owner->id,
                'view_revenue_id' => $view->id,
                'amount' => $view->amount,
            ]);
        }
    }

    /**
     * Notify note owner about view revenue (if accumulated amount is significant)
     */
    protected function maybeNotifyOwner(Note $note, NoteViewRevenue $viewRevenue): void
    {
        // Check total revenue from views in last 24 hours
        $recentRevenue = NoteViewRevenue::where('note_id', $note->id)
            ->where('validation_status', 'approved')
            ->where('viewed_at', '>=', now()->subDay())
            ->sum('amount');

        // Notify if accumulated revenue >= 1 rupiah (100 views)
        if ($recentRevenue >= 1.00) {
            $owner = $note->user;
            if ($owner) {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->create(
                    $owner,
                    'view_revenue_accumulated',
                    '💰 View Revenue Accumulated',
                    "Your free note \"{$note->title}\" has generated Rp " . number_format($recentRevenue, 2, ',', '.') . " from views in the last 24 hours.",
                    route('marketplace.show', $note),
                    [
                        'note_id' => $note->id,
                        'revenue' => $recentRevenue,
                        'period' => '24_hours',
                    ]
                );
            }
        }
    }
}

