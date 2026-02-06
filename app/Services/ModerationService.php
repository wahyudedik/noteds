<?php

namespace App\Services;

use App\Models\ModerationLog;
use App\Models\Post;
use App\Models\Comment;

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
}
