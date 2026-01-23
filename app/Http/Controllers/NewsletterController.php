<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\NewsletterMailable;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string'],
        ]);
        $exists = DB::table('newsletter_suppression_list')->where('email', $data['email'])->exists();
        if ($exists) return response()->json(['message' => 'Email suppressed'], 422);
        $token = Str::random(32);
        DB::table('newsletter_subscribers')->updateOrInsert(
            ['email' => $data['email']],
            ['name' => $data['name'] ?? null, 'status' => config('newsletter.double_opt_in') ? 'pending' : 'active', 'confirm_token' => $token, 'subscribed_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );
        if (config('newsletter.double_opt_in')) {
            $confirmUrl = url('/newsletter/confirm?token='.$token.'&email='.urlencode($data['email']));
            Mail::to($data['email'])->send(new NewsletterMailable('Confirm Subscription', '<p>Confirm your subscription:</p><p><a href="'.$confirmUrl.'">Confirm</a></p>'));
        }
        return response()->json(['message' => 'Subscribed']);
    }

    public function confirm(Request $request): JsonResponse
    {
        $email = $request->query('email');
        $token = $request->query('token');
        $row = DB::table('newsletter_subscribers')->where('email', $email)->first();
        if (!$row || !$token || $row->confirm_token !== $token) return response()->json(['message' => 'Invalid'], 422);
        DB::table('newsletter_subscribers')->where('email', $email)->update(['status' => 'active', 'confirm_token' => null]);
        return response()->json(['message' => 'Confirmed']);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $email = $request->query('email');
        if (!$email) return response()->json(['message' => 'Invalid'], 422);
        DB::table('newsletter_subscribers')->where('email', $email)->update(['status' => 'unsubscribed']);
        DB::table('newsletter_suppression_list')->updateOrInsert(['email' => $email], ['reason' => 'unsubscribe', 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['message' => 'Unsubscribed']);
    }
    public function preferences(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'frequency' => ['required', 'string'],
        ]);
        $pref = ['frequency' => $data['frequency']];
        DB::table('newsletter_subscribers')->where('email', $data['email'])->update(['preferences' => json_encode($pref)]);
        if ($data['frequency'] === 'none') {
            DB::table('newsletter_subscribers')->where('email', $data['email'])->update(['status' => 'unsubscribed']);
            DB::table('newsletter_suppression_list')->updateOrInsert(['email' => $data['email']], ['reason' => 'preferences', 'created_at' => now(), 'updated_at' => now()]);
        }
        return response()->json(['message' => 'Saved']);
    }

    public function pixel(Request $request)
    {
        $sendId = (int) $request->query('s');
        if ($sendId) {
            DB::table('newsletter_events')->insert(['send_id' => $sendId, 'type' => 'open', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('newsletter_sends')->where('id', $sendId)->update(['open_count' => DB::raw('open_count + 1')]);
        }
        $gif = base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==');
        return response($gif, 200)->header('Content-Type', 'image/gif');
    }

    public function click(Request $request)
    {
        $sendId = (int) $request->query('s');
        $url = $request->query('u');
        if ($sendId) {
            DB::table('newsletter_events')->insert(['send_id' => $sendId, 'type' => 'click', 'metadata' => json_encode(['url' => $url]), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('newsletter_sends')->where('id', $sendId)->update(['click_count' => DB::raw('click_count + 1')]);
        }
        if ($url) return redirect()->away($url);
        return redirect('/');
    }
}
