<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsletterProviderController extends Controller
{
    public function config(Request $request): JsonResponse
    {
        $rows = DB::table('newsletter_providers')->get();
        return response()->json(['providers' => $rows]);
    }

    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'string'],
            'credentials' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
        ]);
        DB::table('newsletter_providers')->updateOrInsert(
            ['provider' => $data['provider']],
            [
                'credentials' => isset($data['credentials']) ? json_encode($data['credentials']) : null,
                'settings' => isset($data['settings']) ? json_encode($data['settings']) : null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        return response()->json(['message' => 'Saved']);
    }

    public function test(Request $request): JsonResponse
    {
        $provider = $request->query('provider');
        $row = DB::table('newsletter_providers')->where('provider', $provider)->first();
        if (!$row) return response()->json(['message' => 'Not configured'], 404);
        return response()->json(['message' => 'OK']);
    }
    public function status(Request $request): JsonResponse
    {
        $rows = DB::table('newsletter_provider_status')->get();
        return response()->json(['status' => $rows]);
    }
    public function logs(Request $request): JsonResponse
    {
        $provider = $request->query('provider');
        $limit = min((int) $request->query('limit', 200), 1000);
        $q = DB::table('newsletter_webhook_logs')->orderByDesc('id')->limit($limit);
        if ($provider) $q->where('provider', $provider);
        return response()->json(['logs' => $q->get()]);
    }
    public function resync(Request $request): JsonResponse
    {
        $provider = $request->query('provider');
        if (!$provider) return response()->json(['message' => 'provider required'], 422);
        DB::table('newsletter_provider_status')->updateOrInsert(['provider' => $provider], ['last_success_at' => now(), 'failures_count' => 0, 'updated_at' => now(), 'created_at' => now()]);
        return response()->json(['message' => 'Resync triggered']);
    }

    public function webhookSendgrid(Request $request): JsonResponse
    {
        $provider = 'sendgrid';
        $raw = $request->getContent();
        $sig = $request->header('X-Webhook-Signature') ?? '';
        $secretRow = \Illuminate\Support\Facades\DB::table('newsletter_providers')->where('provider', $provider)->first();
        $secret = $secretRow ? optional(json_decode($secretRow->credentials, true))['secret'] ?? '' : '';
        $valid = $secret ? hash_equals(hash_hmac('sha256', $raw, $secret), $sig) : false;
        \Illuminate\Support\Facades\DB::table('newsletter_webhook_logs')->insert([
            'provider' => $provider, 'valid' => $valid, 'signature' => $sig, 'payload' => $raw, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $events = $request->json()->all() ?: [];
        foreach ($events as $ev) {
            $type = $ev['event'] ?? null;
            $email = $ev['email'] ?? null;
            if ($type === 'bounce' && $email) {
                DB::table('newsletter_suppression_list')->updateOrInsert(['email' => $email], ['reason' => 'bounce', 'updated_at' => now(), 'created_at' => now()]);
            } elseif ($type === 'unsubscribe' && $email) {
                DB::table('newsletter_subscribers')->where('email', $email)->update(['status' => 'unsubscribed']);
                DB::table('newsletter_suppression_list')->updateOrInsert(['email' => $email], ['reason' => 'unsubscribe', 'updated_at' => now(), 'created_at' => now()]);
            }
        }
        if ($valid) {
            DB::table('newsletter_provider_status')->updateOrInsert(['provider' => $provider], ['last_success_at' => now(), 'failures_count' => 0, 'updated_at' => now(), 'created_at' => now()]);
            return response()->json(['message' => 'Accepted']);
        }
        DB::table('newsletter_provider_status')->updateOrInsert(['provider' => $provider], ['last_error' => 'Invalid signature', 'failures_count' => DB::raw('failures_count + 1'), 'updated_at' => now(), 'created_at' => now()]);
        return response()->json(['message' => 'Invalid signature'], 400);
    }

    public function webhookMailgun(Request $request): JsonResponse
    {
        $provider = 'mailgun';
        $raw = $request->getContent();
        $sig = $request->input('signature') ?? $request->header('X-Webhook-Signature') ?? '';
        $secretRow = \Illuminate\Support\Facades\DB::table('newsletter_providers')->where('provider', $provider)->first();
        $secret = $secretRow ? optional(json_decode($secretRow->credentials, true))['secret'] ?? '' : '';
        $valid = $secret ? hash_equals(hash_hmac('sha256', $raw, $secret), $sig) : false;
        \Illuminate\Support\Facades\DB::table('newsletter_webhook_logs')->insert([
            'provider' => $provider, 'valid' => $valid, 'signature' => $sig, 'payload' => $raw, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $event = $request->input('event-data.event');
        $email = $request->input('event-data.recipient');
        if ($event === 'failed' && $email) {
            DB::table('newsletter_suppression_list')->updateOrInsert(['email' => $email], ['reason' => 'bounce', 'updated_at' => now(), 'created_at' => now()]);
        } elseif ($event === 'unsubscribed' && $email) {
            DB::table('newsletter_subscribers')->where('email', $email)->update(['status' => 'unsubscribed']);
            DB::table('newsletter_suppression_list')->updateOrInsert(['email' => $email], ['reason' => 'unsubscribe', 'updated_at' => now(), 'created_at' => now()]);
        }
        if ($valid) {
            DB::table('newsletter_provider_status')->updateOrInsert(['provider' => $provider], ['last_success_at' => now(), 'failures_count' => 0, 'updated_at' => now(), 'created_at' => now()]);
            return response()->json(['message' => 'Accepted']);
        }
        DB::table('newsletter_provider_status')->updateOrInsert(['provider' => $provider], ['last_error' => 'Invalid signature', 'failures_count' => DB::raw('failures_count + 1'), 'updated_at' => now(), 'created_at' => now()]);
        return response()->json(['message' => 'Invalid signature'], 400);
    }

    public function webhookSes(Request $request): JsonResponse
    {
        $provider = 'ses';
        $raw = $request->getContent();
        $sig = $request->header('X-Webhook-Signature') ?? '';
        $secretRow = \Illuminate\Support\Facades\DB::table('newsletter_providers')->where('provider', $provider)->first();
        $secret = $secretRow ? optional(json_decode($secretRow->credentials, true))['secret'] ?? '' : '';
        $valid = $secret ? hash_equals(hash_hmac('sha256', $raw, $secret), $sig) : false;
        \Illuminate\Support\Facades\DB::table('newsletter_webhook_logs')->insert([
            'provider' => $provider, 'valid' => $valid, 'signature' => $sig, 'payload' => $raw, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $type = $request->input('notificationType');
        $email = $request->input('bounce.bouncedRecipients.0.emailAddress') ?: $request->input('complaint.complainedRecipients.0.emailAddress');
        if ($type === 'Bounce' && $email) {
            DB::table('newsletter_suppression_list')->updateOrInsert(['email' => $email], ['reason' => 'bounce', 'updated_at' => now(), 'created_at' => now()]);
        } elseif ($type === 'Complaint' && $email) {
            DB::table('newsletter_subscribers')->where('email', $email)->update(['status' => 'unsubscribed']);
            DB::table('newsletter_suppression_list')->updateOrInsert(['email' => $email], ['reason' => 'complaint', 'updated_at' => now(), 'created_at' => now()]);
        }
        if ($valid) {
            DB::table('newsletter_provider_status')->updateOrInsert(['provider' => $provider], ['last_success_at' => now(), 'failures_count' => 0, 'updated_at' => now(), 'created_at' => now()]);
            return response()->json(['message' => 'Accepted']);
        }
        DB::table('newsletter_provider_status')->updateOrInsert(['provider' => $provider], ['last_error' => 'Invalid signature', 'failures_count' => DB::raw('failures_count + 1'), 'updated_at' => now(), 'created_at' => now()]);
        return response()->json(['message' => 'Invalid signature'], 400);
    }
}
