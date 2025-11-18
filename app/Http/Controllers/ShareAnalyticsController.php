<?php

namespace App\Http\Controllers;

use App\Services\NoteShareService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShareAnalyticsController extends Controller
{
    public function __construct(private NoteShareService $noteShareService)
    {
    }

    /**
     * Display share analytics dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $stats = $this->noteShareService->getUserShareStats($user);

        // Get detailed stats for each share referral with eager loading
        $shareReferrals = $stats['share_referrals']->load([
            'note:id,title,price,user_id',
            'note.user:id,name,username',
            'sharePurchases.buyer:id,name,username',
            'sharePurchases.transaction:id,amount,created_at'
        ])->map(function ($shareReferral) {
            return [
                'id' => $shareReferral->id,
                'note' => $shareReferral->note,
                'referral_token' => $shareReferral->referral_token,
                'share_url' => $shareReferral->share_url,
                'click_count' => $shareReferral->click_count,
                'purchase_count' => $shareReferral->purchase_count,
                'total_commission_earned' => $shareReferral->total_commission_earned,
                'total_revenue_generated' => $shareReferral->total_revenue_generated,
                'created_at' => $shareReferral->created_at,
                'purchases' => $shareReferral->sharePurchases,
            ];
        });

        return view('share.analytics', [
            'stats' => $stats,
            'shareReferrals' => $shareReferrals,
        ]);
    }
}
