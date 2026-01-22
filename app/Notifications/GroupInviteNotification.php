<?php

namespace App\Notifications;

use App\Models\GroupInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public GroupInvite $invite)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $group = $this->invite->group;
        $acceptUrl = route('groups.invites.accept', $this->invite->token);
        $declineUrl = route('groups.invites.decline', $this->invite->token);
        $openPixel = route('email.open', $this->invite->id);
        $ics = $this->generateIcs($group->name, now(), now()->addHour(), url()->route('groups.show', $group->slug));

        $message = (new MailMessage)
            ->subject('Undangan Bergabung ke ' . $group->name)
            ->line('Anda diundang untuk bergabung ke grup ' . $group->name . '.')
            ->action('Terima Undangan', $acceptUrl)
            ->line('Atau tolak undangan melalui tautan berikut:')
            ->action('Tolak Undangan', $declineUrl);

        $message->attachData($ics, 'invite.ics', ['mime' => 'text/calendar']);

        $message->view('emails.group_invite', [
            'group' => $group,
            'invite' => $this->invite,
            'acceptUrl' => $acceptUrl,
            'declineUrl' => $declineUrl,
            'openPixel' => $openPixel,
        ]);

        return $message;
    }

    protected function generateIcs(string $title, \DateTimeInterface $start, \DateTimeInterface $end, string $url): string
    {
        $uid = uniqid() . '@noteds';
        $fmt = 'Ymd\THis\Z';
        $s = gmdate($fmt, $start->getTimestamp());
        $e = gmdate($fmt, $end->getTimestamp());
        $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Noteds//Group Invite//EN\r\nBEGIN:VEVENT\r\nUID:$uid\r\nDTSTAMP:$s\r\nDTSTART:$s\r\nDTEND:$e\r\nSUMMARY:$title\r\nDESCRIPTION:$url\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
        return $ics;
    }
}
