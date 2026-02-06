<?php

namespace App\Services;

use App\Models\User;
use App\Models\SupportTicket;
use App\Models\SupportTicketResponse;
use App\Models\Post;

class NotificationService
{
    /**
     * Notify post author about new comment.
     */
    public function notifyNewComment(\App\Models\Comment $comment): void
    {
        $post = $comment->post;
        $postAuthor = $post->user;

        // Don't notify if user commented on their own post
        if ($postAuthor->id !== $comment->user_id) {
            $postAuthor->notify(new class($comment) extends \Illuminate\Notifications\Notification {
                use \Illuminate\Bus\Queueable;
                public function __construct(public \App\Models\Comment $comment) {}
                public function via($notifiable): array
                {
                    return ['database'];
                }
                public function toArray($notifiable): array
                {
                    return [
                        'type' => 'new_comment',
                        'comment_id' => $this->comment->id,
                        'post_id' => $this->comment->post_id,
                        'title' => 'New Comment',
                        'message' => 'Your post received a new comment.',
                    ];
                }
            });
        }
    }

    /**
     * Notify post/comment author about new vote (optional).
     */
    public function notifyNewVote($vote): void
    {
        // This is optional - can be implemented if needed
        // For now, we'll skip to avoid notification spam
    }

    /**
     * Notify user about new follow.
     */
    public function notifyNewFollow(\App\Models\User $user, \App\Models\User $follower): void
    {
        $user->notify(new \App\Notifications\NewFollowNotification($follower));
    }

    /**
     * Notify admin about content report.
     */
    public function notifyContentReported(\App\Models\ContentReport $report): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ContentReportedNotification($report));
        }
    }

    /**
     * Notify reporter about report resolution.
     */
    public function notifyReportResolved(\App\Models\ContentReport $report): void
    {
        $reporter = $report->user;
        if ($reporter) {
            $reporter->notify(new class($report) extends \Illuminate\Notifications\Notification {
                use \Illuminate\Bus\Queueable;
                public function __construct(public \App\Models\ContentReport $report) {}
                public function via($notifiable): array
                {
                    return ['database'];
                }
                public function toArray($notifiable): array
                {
                    return [
                        'type' => 'report_resolved',
                        'report_id' => $this->report->id,
                        'title' => 'Report Resolved',
                        'message' => 'Your content report has been resolved.',
                        'status' => $this->report->status ?? 'resolved',
                    ];
                }
            });
        }
    }

    /**
     * Notify user about mention (future feature).
     */
    public function notifyMention(\App\Models\User $user, $mentionable): void
    {
        // Future implementation for mentions
    }

    /**
     * Notify admin about new support ticket.
     */
    public function notifyNewSupportTicket(SupportTicket $ticket): void
    {
        // Ensure user relationship is loaded
        if (!$ticket->relationLoaded('user')) {
            $ticket->load('user');
        }

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewSupportTicketNotification($ticket));
        }
    }

    /**
     * Notify user or admin about new support ticket response.
     */
    public function notifySupportTicketResponse(SupportTicket $ticket, SupportTicketResponse $response): void
    {
        // Ensure relationships are loaded
        if (!$ticket->relationLoaded('user')) {
            $ticket->load('user');
        }
        if (!$response->relationLoaded('user')) {
            $response->load('user');
        }

        $isAdminResponse = $response->is_admin_response;

        if ($isAdminResponse) {
            // Admin responded, notify the ticket owner (user)
            if ($ticket->user) {
                $ticket->user->notify(new \App\Notifications\SupportTicketResponseNotification($ticket, $response));
            }
        } else {
            // User responded, notify assigned admin or all admins if not assigned
            if ($ticket->assigned_to) {
                $assignedAdmin = User::find($ticket->assigned_to);
                if ($assignedAdmin) {
                    $assignedAdmin->notify(new \App\Notifications\SupportTicketResponseNotification($ticket, $response));
                }
            } else {
                // Notify all admins if no admin is assigned
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\SupportTicketResponseNotification($ticket, $response));
                }
            }
        }
    }

    /**
     * Notify user and admin about support ticket status update.
     */
    public function notifySupportTicketStatusUpdate(SupportTicket $ticket, string $oldStatus, string $newStatus): void
    {
        // Ensure user relationship is loaded
        if (!$ticket->relationLoaded('user')) {
            $ticket->load('user');
        }

        // Notify the ticket owner (user)
        if ($ticket->user) {
            $ticket->user->notify(new \App\Notifications\SupportTicketStatusUpdateNotification($ticket, $oldStatus, $newStatus));
        }

        // Notify assigned admin if exists
        if ($ticket->assigned_to) {
            $assignedAdmin = User::find($ticket->assigned_to);
            if ($assignedAdmin) {
                $assignedAdmin->notify(new \App\Notifications\SupportTicketStatusUpdateNotification($ticket, $oldStatus, $newStatus));
            }
        }
    }

    /**
     * Notify post author when their post is reposted.
     */
    public function notifyPostReposted(\App\Models\Post $post, User $reposter, ?\App\Models\Repost $repost = null): void
    {
        // Ensure relationships are loaded
        if (!$post->relationLoaded('user')) {
            $post->load('user');
        }

        if ($post->user && $post->user_id !== $reposter->id) {
            $post->user->notify(new \App\Notifications\PostRepostedNotification($post, $reposter, $repost));
        }
    }

    /**
     * Notify post author when their scheduled post is published.
     */
    public function notifyPostPublished(Post $post): void
    {
        // Ensure relationships are loaded
        if (!$post->relationLoaded('user')) {
            $post->load('user');
        }

        if ($post->user) {
            $post->user->notify(new \App\Notifications\PostPublishedNotification($post));
        }
    }

    /**
     * Notify user about collaboration invitation.
     */
    public function notifyCollaborationInvitation(\App\Models\PostCollaborator $collaboration): void
    {
        $collaboration->load(['user', 'post.user']);

        if ($collaboration->user) {
            $collaboration->user->notify(new \App\Notifications\CollaborationInvitationNotification($collaboration));
        }
    }

    /**
     * Notify post owner when collaboration is accepted.
     */
    public function notifyCollaborationAccepted(\App\Models\PostCollaborator $collaboration): void
    {
        $collaboration->load(['user', 'post.user']);

        if ($collaboration->post->user) {
            $collaboration->post->user->notify(new \App\Notifications\CollaborationAcceptedNotification($collaboration));
        }
    }

    /**
     * Notify post owner when collaboration is rejected.
     */
    public function notifyCollaborationRejected(\App\Models\PostCollaborator $collaboration): void
    {
        $collaboration->load(['user', 'post.user']);

        if ($collaboration->post->user) {
            $collaboration->post->user->notify(new \App\Notifications\CollaborationRejectedNotification($collaboration));
        }
    }

    /**
     * Notify post owner when post is edited by collaborator.
     */
    public function notifyPostEditedByCollaborator(Post $post, User $collaborator): void
    {
        if (!$post->relationLoaded('user')) {
            $post->load('user');
        }

        if ($post->user && $post->user_id !== $collaborator->id) {
            $post->user->notify(new \App\Notifications\PostEditedByCollaboratorNotification($post, $collaborator));
        }
    }
}
