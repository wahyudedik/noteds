<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GDPRController extends Controller
{
    public function export(Request $request)
    {
        $user = $request->user();
        $data = [
            'user' => $user->only(['id','name','email','business_name','business_field','skills','goals','portfolio_url','website_url','created_at']),
            'posts' => $user->posts()->get(['id','title','content','created_at']),
            'comments' => $user->comments()->get(['id','post_id','content','created_at']),
            'transactions' => $user->transactions()->get(['id','amount','type','status','created_at']),
            'withdrawals' => $user->withdrawals()->get(['id','amount','status','created_at']),
            'consents' => DB::table('privacy_consents')->where('user_id', $user->id)->get(),
            'activity_logs' => DB::table('user_activity_logs')->where('user_id', $user->id)->orderByDesc('id')->limit(500)->get(),
        ];
        return response()->json($data);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        DB::table('gdpr_requests')->insert([
            'user_id' => $user->id,
            'type' => 'delete',
            'status' => 'completed',
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Auth::logout();
        $uid = $user->id;
        DB::transaction(function () use ($user, $uid) {
            DB::table('privacy_consents')->where('user_id', $uid)->delete();
            DB::table('gdpr_requests')->where('user_id', $uid)->delete();
            $user->posts()->delete();
            $user->comments()->delete();
            $user->transactions()->delete();
            $user->withdrawals()->delete();
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) Storage::disk('public')->delete($user->avatar);
            if ($user->header_image && Storage::disk('public')->exists($user->header_image)) Storage::disk('public')->delete($user->header_image);
            $user->delete();
        });
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('status', 'account-deleted');
    }

    public function saveConsent(Request $request)
    {
        $user = $request->user();
        $payload = $request->validate([
            'policy_version' => ['required','string'],
            'cookie_categories' => ['nullable','array'],
        ]);
        DB::table('privacy_consents')->insert([
            'user_id' => $user->id,
            'policy_version' => $payload['policy_version'],
            'cookie_categories' => json_encode($payload['cookie_categories'] ?? []),
            'consented_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['message' => 'consent_saved']);
    }

    public function getConsent(Request $request)
    {
        $user = $request->user();
        $latest = DB::table('privacy_consents')->where('user_id', $user->id)->orderByDesc('id')->first();
        return response()->json(['latest' => $latest]);
    }
}
