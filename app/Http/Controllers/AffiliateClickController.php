<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Services\FraudDetectionService;
use App\Services\ClickDeduplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AffiliateClickController extends Controller
{
    public function __construct(
        private FraudDetectionService $fraudDetectionService,
        private ClickDeduplicationService $dedupService,
    ) {}

    /**
     * Track affiliate click dengan fraud detection + deduplication
     */
    public function trackClick(Request $request, string $affiliateCode): JsonResponse
    {
        try {
            // Validate affiliate exists
            $affiliate = Affiliate::where('code', $affiliateCode)->first();
            if (!$affiliate || !$affiliate->user) {
                return response()->json(['error' => 'Invalid affiliate code'], 404);
            }

            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            $sessionId = $request->cookie('XSRF-TOKEN') ?? $request->session()->getId();
            $referrer = $request->header('referer') ?? '';

            // ========== STEP 1: Check untuk duplicate clicks ==========
            $dedupResult = $this->dedupService->detectDuplicateClick(
                affiliate: $affiliate,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                sessionId: $sessionId,
                referrer: $referrer,
            );

            // Jika ini duplicate click
            if ($dedupResult['is_duplicate']) {
                Log::warning('Duplicate affiliate click rejected', [
                    'affiliate_id' => $affiliate->user_id,
                    'reason' => $dedupResult['reason'],
                    'click_id' => $dedupResult['click_id'],
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Duplicate click detected',
                    'reason' => $dedupResult['reason'],
                    'click_id' => $dedupResult['click_id'], // Return original click ID if duplicate
                    'fraud_indicators' => $dedupResult['fraud_indicators'],
                ], 400);
            }

            $clickId = $dedupResult['click_id'];

            // ========== STEP 2: Log dan deteksi fraud ==========
            $fraudLog = $this->fraudDetectionService->logAndDetectFraud(
                affiliate: $affiliate->user,
                converter: null,
                activityType: 'click',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                metadata: [
                    'affiliate_code' => $affiliateCode,
                    'referrer' => $referrer,
                    'click_id' => $clickId,
                    'dedup_indicators' => $dedupResult['fraud_indicators'],
                ]
            );

            // Combine risk scores
            $totalRiskScore = $fraudLog->risk_score + $dedupResult['risk_score_increase'];

            // Check jika affiliate is flagged for fraud
            if ($fraudLog->is_flagged && $totalRiskScore >= 60) {
                // Log ke monitoring system
                Log::warning('Potential affiliate fraud detected', [
                    'affiliate_id' => $affiliate->user_id,
                    'risk_score' => $totalRiskScore,
                    'indicators' => $fraudLog->fraud_indicators,
                    'dedup_indicators' => $dedupResult['fraud_indicators'],
                ]);

                // Suspend affiliate jika risk score very high
                if ($totalRiskScore >= 80) {
                    $affiliate->user->update(['is_fraud_suspected' => true]);
                    return response()->json([
                        'error' => 'Account suspended due to fraud detection',
                        'risk_score' => $totalRiskScore
                    ], 403);
                }
            }

            // ========== STEP 3: Create click record ==========
            // Store click data dalam cache untuk conversion tracking
            cache()->put("click_{$clickId}", [
                'affiliate_id' => $affiliate->user_id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'timestamp' => now(),
                'fraud_log_id' => $fraudLog->id,
            ], 86400); // 24 hours

            // Update affiliate fraud log dengan dedup info
            $fraudLog->update([
                'click_id' => $clickId,
                'device_fingerprint' => $this->dedupService->generateDeviceFingerprint($ipAddress, $userAgent),
                'session_id' => $sessionId,
                'dedup_status' => 'valid',
                'dedup_reason' => null,
            ]);

            return response()->json([
                'success' => true,
                'click_id' => $clickId,
                'affiliate_id' => $affiliate->user_id,
                'fraud_risk' => $totalRiskScore,
                'fraud_indicators' => $fraudLog->fraud_indicators,
            ]);
        } catch (\Exception $e) {
            Log::error('Error tracking affiliate click', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Track conversion setelah click
     */
    public function trackConversion(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'click_id' => 'required|uuid',
                'amount' => 'required|numeric|min:0',
                'product_id' => 'required|uuid',
            ]);

            // Get click data dari cache
            $clickData = cache()->get("click_{$validated['click_id']}");
            if (!$clickData) {
                return response()->json(['error' => 'Invalid or expired click'], 404);
            }

            // Get converter user
            $converter = Auth::user();
            if (!$converter) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            // Detect fraud
            $fraudLog = $this->fraudDetectionService->logAndDetectFraud(
                affiliate: null,
                converter: $converter,
                activityType: 'conversion',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                metadata: [
                    'click_id' => $validated['click_id'],
                    'affiliate_id' => $clickData['affiliate_id'],
                    'amount' => $validated['amount'],
                    'product_id' => $validated['product_id'],
                    'time_since_click' => now()->diffInSeconds($clickData['timestamp']),
                ]
            );

            // Check fraud flags
            if ($fraudLog->is_flagged && $fraudLog->risk_score >= 60) {
                Log::warning('Potential converter fraud detected', [
                    'converter_id' => $converter->id,
                    'risk_score' => $fraudLog->risk_score,
                    'indicators' => $fraudLog->fraud_indicators,
                ]);

                if ($fraudLog->risk_score >= 80) {
                    return response()->json(['error' => 'Transaction declined'], 403);
                }
            }

            // Process conversion (business logic)
            // Update affiliate commission, etc.

            cache()->forget("click_{$validated['click_id']}");

            return response()->json([
                'success' => true,
                'conversion_id' => Str::uuid(),
                'fraud_risk' => $fraudLog->risk_score,
                'amount' => $validated['amount'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error tracking conversion', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
