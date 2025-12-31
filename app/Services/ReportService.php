<?php

namespace App\Services;

use App\Models\ContentReport;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;

class ReportService
{
    private const REPORT_THRESHOLD = 5; // Auto-hide threshold

    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Report a post.
     *
     * @param User $reporter
     * @param Post $post
     * @param string $reason
     * @param string|null $notes
     * @return ContentReport
     */
    public function reportPost(User $reporter, Post $post, string $reason, ?string $notes = null): ContentReport
    {
        // Check if user already reported this post
        $existingReport = ContentReport::where('user_id', $reporter->id)
            ->where('reportable_type', Post::class)
            ->where('reportable_id', $post->id)
            ->first();

        if ($existingReport) {
            throw new \Exception('You have already reported this post.');
        }

        $report = ContentReport::create([
            'user_id' => $reporter->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
            'reason' => $reason,
            'notes' => $notes,
            'status' => 'pending',
        ]);

        // Check if threshold reached for auto-moderation
        $reportCount = ContentReport::where('reportable_type', Post::class)
            ->where('reportable_id', $post->id)
            ->where('status', 'pending')
            ->count();

        if ($reportCount >= self::REPORT_THRESHOLD) {
            $this->autoModeratePost($post);
        }

        // Notify admins
        $this->notificationService->notifyContentReported($report);

        return $report;
    }

    /**
     * Report a comment.
     *
     * @param User $reporter
     * @param Comment $comment
     * @param string $reason
     * @param string|null $notes
     * @return ContentReport
     */
    public function reportComment(User $reporter, Comment $comment, string $reason, ?string $notes = null): ContentReport
    {
        // Check if user already reported this comment
        $existingReport = ContentReport::where('user_id', $reporter->id)
            ->where('reportable_type', Comment::class)
            ->where('reportable_id', $comment->id)
            ->first();

        if ($existingReport) {
            throw new \Exception('You have already reported this comment.');
        }

        $report = ContentReport::create([
            'user_id' => $reporter->id,
            'reportable_type' => Comment::class,
            'reportable_id' => $comment->id,
            'reason' => $reason,
            'notes' => $notes,
            'status' => 'pending',
        ]);

        // Check if threshold reached for auto-moderation
        $reportCount = ContentReport::where('reportable_type', Comment::class)
            ->where('reportable_id', $comment->id)
            ->where('status', 'pending')
            ->count();

        if ($reportCount >= self::REPORT_THRESHOLD) {
            $this->autoModerateComment($comment);
        }

        // Notify admins
        $this->notificationService->notifyContentReported($report);

        return $report;
    }

    /**
     * Report a user.
     *
     * @param User $reporter
     * @param User $reportedUser
     * @param string $reason
     * @param string|null $notes
     * @return ContentReport
     */
    public function reportUser(User $reporter, User $reportedUser, string $reason, ?string $notes = null): ContentReport
    {
        // Prevent self-report
        if ($reporter->id === $reportedUser->id) {
            throw new \Exception('You cannot report yourself.');
        }

        // Check if user already reported this user
        $existingReport = ContentReport::where('user_id', $reporter->id)
            ->where('reportable_type', User::class)
            ->where('reportable_id', $reportedUser->id)
            ->first();

        if ($existingReport) {
            throw new \Exception('You have already reported this user.');
        }

        $report = ContentReport::create([
            'user_id' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $reportedUser->id,
            'reason' => $reason,
            'notes' => $notes,
            'status' => 'pending',
        ]);

        // Check if threshold reached for auto-moderation
        $reportCount = ContentReport::where('reportable_type', User::class)
            ->where('reportable_id', $reportedUser->id)
            ->where('status', 'pending')
            ->count();

        if ($reportCount >= self::REPORT_THRESHOLD) {
            $this->autoModerateUser($reportedUser);
        }

        // Notify admins
        $this->notificationService->notifyContentReported($report);

        return $report;
    }

    /**
     * Auto-moderate post by hiding it.
     *
     * @param Post $post
     * @return void
     */
    protected function autoModeratePost(Post $post): void
    {
        $post->update(['status' => 'hidden']);
    }

    /**
     * Auto-moderate comment by hiding it (if post has status field).
     *
     * @param Comment $comment
     * @return void
     */
    protected function autoModerateComment(Comment $comment): void
    {
        // Comments might not have status field, so we could add a soft delete or flag
        // For now, we'll just log it - admin can review
        \Illuminate\Support\Facades\Log::info('Comment auto-moderated due to report threshold', [
            'comment_id' => $comment->id,
        ]);
    }

    /**
     * Auto-moderate user by banning them.
     *
     * @param User $user
     * @return void
     */
    protected function autoModerateUser(User $user): void
    {
        // Don't auto-ban admins
        if ($user->isAdmin()) {
            return;
        }

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => 'Auto-banned due to multiple reports',
        ]);
    }

    /**
     * Resolve a report.
     *
     * @param ContentReport $report
     * @param User $admin
     * @param string|null $adminNotes
     * @return ContentReport
     */
    public function resolveReport(ContentReport $report, User $admin, ?string $adminNotes = null): ContentReport
    {
        $report->update([
            'status' => 'resolved',
            'admin_id' => $admin->id,
            'admin_notes' => $adminNotes,
            'resolved_at' => now(),
        ]);

        // Notify reporter
        $this->notificationService->notifyReportResolved($report);

        return $report;
    }

    /**
     * Dismiss a report.
     *
     * @param ContentReport $report
     * @param User $admin
     * @param string|null $adminNotes
     * @return ContentReport
     */
    public function dismissReport(ContentReport $report, User $admin, ?string $adminNotes = null): ContentReport
    {
        $report->update([
            'status' => 'dismissed',
            'admin_id' => $admin->id,
            'admin_notes' => $adminNotes,
            'resolved_at' => now(),
        ]);

        return $report;
    }

    /**
     * Get report count for a reportable model.
     *
     * @param Model $reportable
     * @return int
     */
    public function getReportCount(Model $reportable): int
    {
        return ContentReport::where('reportable_type', get_class($reportable))
            ->where('reportable_id', $reportable->id)
            ->where('status', 'pending')
            ->count();
    }
}

