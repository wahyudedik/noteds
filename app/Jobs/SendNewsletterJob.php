<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterMailable;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $campaignId;

    public function __construct(int $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function handle(): void
    {
        $campaign = DB::table('newsletter_campaigns')->where('id', $this->campaignId)->first();
        if (!$campaign) return;
        $subscribers = DB::table('newsletter_subscribers')->where('status', 'active')->get();
        $template = DB::table('newsletter_templates')->where('id', $campaign->template_id)->first();
        foreach ($subscribers as $sub) {
            $sendId = DB::table('newsletter_sends')->insertGetId([
                'campaign_id' => $campaign->id,
                'subscriber_id' => $sub->id,
                'email' => $sub->email,
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $pixel = url('/newsletter/pixel?s='.$sendId);
            $unsubscribe = url('/newsletter/unsubscribe?email='.urlencode($sub->email));
            $html = str_replace(['{{nama}}'], [$sub->name ?? ''], $template->html);
            $html = str_replace('href="', 'href="'.url('/newsletter/click?s='.$sendId.'&u='), $html);
            $html .= '<img src="'.$pixel.'" width="1" height="1" />';
            $html .= '<p><a href="'.$unsubscribe.'">Berhenti berlangganan</a></p>';
            Mail::to($sub->email)->send(new NewsletterMailable($campaign->name, $html));
        }
        DB::table('newsletter_campaigns')->where('id', $campaign->id)->update(['status' => 'sent', 'updated_at' => now()]);
    }
}
