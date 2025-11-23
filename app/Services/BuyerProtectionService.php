<?php

namespace App\Services;

use App\Models\BuyerProtectionSetting;
use App\Models\Refund;
use App\Models\Dispute;
use App\Models\QualityCheck;
use App\Models\Transaction;
use App\Models\Note;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuyerProtectionService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Get protection settings
     */
    public function getSettings(): BuyerProtectionSetting
    {
        return BuyerProtectionSetting::getSettings();
    }

    /**
     * Check if transaction is eligible for refund based on money-back guarantee
     */
    public function isEligibleForRefund(Transaction $transaction): array
    {
        $settings = $this->getSettings();
        
        if (!$settings->money_back_guarantee_enabled) {
            return [
                'eligible' => false,
                'reason' => 'Money-back guarantee is not enabled.',
            ];
        }

        $daysSincePurchase = $transaction->created_at->diffInDays(now());
        
        if ($daysSincePurchase > $settings->money_back_guarantee_days) {
            return [
                'eligible' => false,
                'reason' => "Refund can only be requested within {$settings->money_back_guarantee_days} days of purchase.",
            ];
        }

        // Check if refund already exists
        $existingRefund = Refund::where('transaction_id', $transaction->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existingRefund) {
            return [
                'eligible' => false,
                'reason' => 'A refund request already exists for this transaction.',
            ];
        }

        // Check max refund amount
        if ($settings->max_refund_amount && $transaction->amount > $settings->max_refund_amount) {
            return [
                'eligible' => false,
                'reason' => "Refund amount exceeds maximum allowed refund amount.",
            ];
        }

        return [
            'eligible' => true,
            'reason' => null,
        ];
    }

    /**
     * Auto-approve refund if within guarantee period and auto-approve is enabled
     */
    public function autoApproveRefundIfEligible(Refund $refund): bool
    {
        $settings = $this->getSettings();
        
        if (!$settings->auto_approve_refunds) {
            return false;
        }

        $transaction = $refund->transaction;
        $daysSincePurchase = $transaction->created_at->diffInDays(now());
        
        if ($daysSincePurchase <= $settings->money_back_guarantee_days) {
            // Auto-approve
            $refundService = app(\App\Services\RefundService::class);
            $refundService->approveRefund($refund, auth()->user() ?? User::role('admin')->first());
            
            return true;
        }

        return false;
    }

    /**
     * Create a dispute
     */
    public function createDispute(
        Transaction $transaction,
        User $buyer,
        string $type,
        string $complaint,
        ?array $evidence = null,
        ?Refund $refund = null
    ): Dispute {
        $settings = $this->getSettings();
        
        if (!$settings->dispute_resolution_enabled) {
            throw new \Exception('Dispute resolution is not enabled.');
        }

        // Check if dispute already exists
        $existingDispute = Dispute::where('transaction_id', $transaction->id)
            ->where('buyer_id', $buyer->id)
            ->whereIn('status', ['open', 'in_review'])
            ->first();

        if ($existingDispute) {
            throw new \Exception('A dispute already exists for this transaction.');
        }

        $dispute = Dispute::create([
            'refund_id' => $refund?->id,
            'transaction_id' => $transaction->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $transaction->seller_id,
            'note_id' => $transaction->note_id,
            'type' => $type,
            'status' => 'open',
            'buyer_complaint' => $complaint,
            'evidence' => $evidence,
        ]);

        // Notify seller
        $this->notificationService->create(
            $transaction->seller,
            'dispute_opened',
            '⚠️ Dispute Opened',
            "{$buyer->name} has opened a dispute for: {$transaction->note->title}",
            route('disputes.show', $dispute),
            ['dispute_id' => $dispute->id, 'transaction_id' => $transaction->id]
        );

        // Notify admins
        $this->notifyAdminsOfDispute($dispute);

        return $dispute;
    }

    /**
     * Resolve dispute
     */
    public function resolveDispute(
        Dispute $dispute,
        User $resolver,
        string $resolution,
        ?string $action = null // 'approve_refund', 'reject_refund', 'partial_refund', 'release_escrow', 'refund_escrow', 'other'
    ): void {
        $dispute->update([
            'status' => 'resolved',
            'admin_resolution' => $resolution,
            'resolved_by' => $resolver->id,
            'resolved_at' => now(),
        ]);

        // Notify buyer and seller
        $this->notificationService->create(
            $dispute->buyer,
            'dispute_resolved',
            '✅ Dispute Resolved',
            "Your dispute for '{$dispute->note->title}' has been resolved.",
            route('disputes.show', $dispute),
            ['dispute_id' => $dispute->id, 'resolution' => $resolution]
        );

        $this->notificationService->create(
            $dispute->seller,
            'dispute_resolved',
            '✅ Dispute Resolved',
            "The dispute for '{$dispute->note->title}' has been resolved.",
            route('disputes.show', $dispute),
            ['dispute_id' => $dispute->id, 'resolution' => $resolution]
        );

        // Handle action if specified
        if ($action === 'approve_refund' && $dispute->refund) {
            $refundService = app(\App\Services\RefundService::class);
            $refundService->approveRefund($dispute->refund, $resolver);
        }

        // Handle escrow actions
        $escrow = $dispute->transaction->escrow ?? null;
        if ($escrow) {
            $escrowService = app(\App\Services\EscrowService::class);
            
            if ($action === 'release_escrow') {
                $escrowService->releaseEscrow($escrow, $resolver, $resolution);
            } elseif ($action === 'refund_escrow') {
                $escrowService->refundEscrow($escrow, $resolver, $resolution);
            }
        }
    }

    /**
     * Perform quality check on note
     */
    public function performQualityCheck(
        Note $note,
        ?Transaction $transaction = null,
        string $checkType = 'random',
        ?User $checker = null
    ): QualityCheck {
        $settings = $this->getSettings();
        
        if (!$settings->quality_assurance_enabled) {
            throw new \Exception('Quality assurance is not enabled.');
        }

        $criteria = $settings->quality_check_criteria ?? [];
        $results = [];

        // Perform checks based on criteria
        foreach ($criteria as $criterion => $config) {
            if (!($config['enabled'] ?? false)) {
                continue;
            }

            $result = $this->checkCriterion($note, $criterion, $config);
            $results[$criterion] = $result;
        }

        // Determine overall status
        $failedChecks = array_filter($results, fn($r) => ($r['passed'] ?? false) === false);
        $status = empty($failedChecks) ? 'passed' : 'failed';

        $qualityCheck = QualityCheck::create([
            'note_id' => $note->id,
            'transaction_id' => $transaction?->id,
            'checked_by' => $checker?->id,
            'check_type' => $checkType,
            'status' => $status,
            'check_results' => $results,
            'checked_at' => now(),
        ]);

        // If failed, notify seller
        if ($status === 'failed') {
            $this->notificationService->create(
                $note->user,
                'quality_check_failed',
                '⚠️ Quality Check Failed',
                "Your note '{$note->title}' failed quality assurance checks.",
                route('notes.show', $note),
                ['note_id' => $note->id, 'check_id' => $qualityCheck->id]
            );
        }

        return $qualityCheck;
    }

    /**
     * Check a specific quality criterion
     */
    protected function checkCriterion(Note $note, string $criterion, array $config): array
    {
        $result = [
            'criterion' => $criterion,
            'passed' => false,
            'message' => '',
        ];

        switch ($criterion) {
            case 'content_completeness':
                $minLength = $config['min_length'] ?? 100;
                $contentLength = strlen(strip_tags($note->content ?? ''));
                $result['passed'] = $contentLength >= $minLength;
                $result['message'] = $result['passed'] 
                    ? "Content length ({$contentLength}) meets minimum ({$minLength})"
                    : "Content length ({$contentLength}) is below minimum ({$minLength})";
                break;

            case 'has_preview':
                $result['passed'] = !empty($note->preview_content) || !empty($note->thumbnails);
                $result['message'] = $result['passed'] 
                    ? "Note has preview content"
                    : "Note is missing preview content";
                break;

            case 'has_description':
                $result['passed'] = !empty($note->summary) || !empty($note->description);
                $result['message'] = $result['passed'] 
                    ? "Note has description"
                    : "Note is missing description";
                break;

            case 'has_tags':
                $minTags = $config['min_tags'] ?? 1;
                $tagCount = $note->tags()->count();
                $result['passed'] = $tagCount >= $minTags;
                $result['message'] = $result['passed'] 
                    ? "Note has {$tagCount} tags (minimum: {$minTags})"
                    : "Note has only {$tagCount} tags (minimum: {$minTags})";
                break;

            case 'price_reasonable':
                $maxPrice = $config['max_price'] ?? null;
                if ($maxPrice && $note->price > $maxPrice) {
                    $result['passed'] = false;
                    $result['message'] = "Price ({$note->price}) exceeds maximum ({$maxPrice})";
                } else {
                    $result['passed'] = true;
                    $result['message'] = "Price is within acceptable range";
                }
                break;

            default:
                $result['passed'] = true;
                $result['message'] = "Criterion not implemented";
        }

        return $result;
    }

    /**
     * Enforce refund policy
     */
    public function enforceRefundPolicy(Refund $refund): array
    {
        $settings = $this->getSettings();
        $transaction = $refund->transaction;
        $violations = [];

        // Check policy rules
        $rules = $settings->refund_policy_rules ?? [];

        foreach ($rules as $rule) {
            if (!($rule['enabled'] ?? false)) {
                continue;
            }

            $violated = $this->checkRefundRule($refund, $transaction, $rule);
            if ($violated) {
                $violations[] = [
                    'rule' => $rule['name'] ?? 'Unknown',
                    'message' => $rule['message'] ?? 'Policy violation',
                ];
            }
        }

        return [
            'compliant' => empty($violations),
            'violations' => $violations,
        ];
    }

    /**
     * Check a specific refund rule
     */
    protected function checkRefundRule(Refund $refund, Transaction $transaction, array $rule): bool
    {
        $ruleType = $rule['type'] ?? '';

        switch ($ruleType) {
            case 'no_refund_if_downloaded':
                $downloaded = \App\Models\NoteDownload::where('note_id', $transaction->note_id)
                    ->where('user_id', $transaction->buyer_id)
                    ->exists();
                return $downloaded;

            case 'no_refund_if_used':
                // Check if note was used (e.g., in projects, shared, etc.)
                // This can be extended based on your business logic
                return false;

            case 'min_purchase_age':
                $minAge = $rule['min_age_hours'] ?? 24;
                $ageHours = $transaction->created_at->diffInHours(now());
                return $ageHours < $minAge;

            default:
                return false;
        }
    }

    /**
     * Notify admins of new dispute
     */
    protected function notifyAdminsOfDispute(Dispute $dispute): void
    {
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            $this->notificationService->create(
                $admin,
                'admin_dispute',
                '⚠️ New Dispute',
                "{$dispute->buyer->name} has opened a dispute for '{$dispute->note->title}'.",
                route('admin.disputes.show', $dispute),
                [
                    'dispute_id' => $dispute->id,
                    'transaction_id' => $dispute->transaction_id,
                ]
            );
        }
    }
}

