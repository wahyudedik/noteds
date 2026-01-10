<?php

namespace App\Services;

use App\Models\ModerationLog;
use App\Models\Post;
use App\Models\Comment;
use App\Models\ProductReview;

class ModerationService
{
    protected array $forbiddenWords = [
        'drama', 'galau', 'curhat', 'broken', 'sedih', 'menangis',
        'putus', 'cinta', 'rindu', 'sayang', 'mantan',
    ];

    protected array $forbiddenPhrases = [
        'masalah pribadi',
        'rumah tangga',
        'pasangan',
        'hubungan',
    ];

    public function checkContent(string $content): array
    {
        $issues = [];
        $contentLower = strtolower($content);

        // Check forbidden words
        foreach ($this->forbiddenWords as $word) {
            if (stripos($contentLower, $word) !== false) {
                $issues[] = "Contains inappropriate word: {$word}";
            }
        }

        // Check forbidden phrases
        foreach ($this->forbiddenPhrases as $phrase) {
            if (stripos($contentLower, $phrase) !== false) {
                $issues[] = "Contains inappropriate phrase: {$phrase}";
            }
        }

        return $issues;
    }

    public function moderatePost(Post $post, ?string $moderatorId = null, string $action = 'warn'): ModerationLog
    {
        $issues = $this->checkContent($post->title . ' ' . $post->content);
        $reason = !empty($issues) ? implode('; ', $issues) : 'Manual moderation';

        if ($action === 'hide') {
            $post->update(['status' => 'moderated']);
        } elseif ($action === 'delete') {
            $post->update(['status' => 'archived']);
        }

        return ModerationLog::create([
            'user_id' => $post->user_id,
            'post_id' => $post->id,
            'moderator_id' => $moderatorId,
            'reason' => $reason,
            'action' => $action,
        ]);
    }

    public function moderateComment(Comment $comment, ?string $moderatorId = null, string $action = 'warn'): ModerationLog
    {
        $issues = $this->checkContent($comment->content);
        $reason = !empty($issues) ? implode('; ', $issues) : 'Manual moderation';

        if ($action === 'delete') {
            $comment->delete();
        }

        return ModerationLog::create([
            'user_id' => $comment->user_id,
            'comment_id' => $comment->id,
            'moderator_id' => $moderatorId,
            'reason' => $reason,
            'action' => $action,
        ]);
    }

    public function shouldAutoModerate(string $content): bool
    {
        $issues = $this->checkContent($content);
        return !empty($issues);
    }

    /**
     * Moderate a product review.
     */
    public function moderateReview(ProductReview $review, ?string $moderatorId = null, string $action = 'warn'): ModerationLog
    {
        $issues = $this->checkReviewSpam($review);
        $reason = !empty($issues) ? implode('; ', $issues) : 'Manual moderation';

        if ($action === 'hide') {
            $review->update(['status' => 'moderated']);
        } elseif ($action === 'delete') {
            $review->update(['status' => 'archived']);
        }

        return ModerationLog::create([
            'user_id' => $review->user_id,
            'product_review_id' => $review->id,
            'moderator_id' => $moderatorId,
            'reason' => $reason,
            'action' => $action,
        ]);
    }

    /**
     * Check if review content should be auto-moderated.
     */
    public function shouldAutoModerateReview(string $content): bool
    {
        $issues = $this->checkContent($content);
        return !empty($issues);
    }

    /**
     * Check review for spam patterns.
     */
    public function checkReviewSpam(ProductReview $review): array
    {
        $issues = [];
        $content = $review->comment ?? '';

        // Check for forbidden words/phrases (reuse existing checkContent)
        $contentIssues = $this->checkContent($content);
        $issues = array_merge($issues, $contentIssues);

        // Check for repeated text (spam pattern)
        if (strlen($content) > 20) {
            $words = explode(' ', $content);
            $wordCounts = array_count_values($words);
            foreach ($wordCounts as $word => $count) {
                if ($count > 5 && strlen($word) > 3) {
                    $issues[] = "Repeated word detected: {$word}";
                    break;
                }
            }
        }

        // Check for suspicious patterns (all caps, excessive punctuation)
        if (strlen($content) > 10) {
            $uppercaseRatio = strlen(preg_replace('/[^A-Z]/', '', $content)) / strlen($content);
            if ($uppercaseRatio > 0.7) {
                $issues[] = "Excessive uppercase text detected";
            }

            $punctuationRatio = strlen(preg_replace('/[^!?.]/', '', $content)) / strlen($content);
            if ($punctuationRatio > 0.3) {
                $issues[] = "Excessive punctuation detected";
            }
        }

        return $issues;
    }
}
