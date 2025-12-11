<?php

namespace App\Http\Controllers;

use App\Models\NoteShareReferral;
use App\Services\NoteShareService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShareAnalyticsController extends Controller
{
    public function __construct(private NoteShareService $noteShareService)
    {
        // Sellers and admin can access share analytics
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || ($user->role !== 'seller' && !$user->hasRole('admin'))) {
                abort(403, 'Only sellers and admin can access share analytics.');
            }
            return $next($request);
        });
    }

    /**
     * Display share analytics dashboard.
     * Accessible to sellers (their own data) and admin (all data)
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // If admin, get all share stats; if seller, get only their own
        if ($user->hasRole('admin')) {
            // Get all share referrals for admin view
            $shareReferralsCollection = NoteShareReferral::with('note:id,title,price,user_id')
                ->get();

            $stats = [
                'total_shares' => $shareReferralsCollection->count(),
                'total_clicks' => $shareReferralsCollection->sum('click_count'),
                'total_purchases' => $shareReferralsCollection->sum('purchase_count'),
                'total_commission_earned' => $shareReferralsCollection->sum('total_commission_earned'),
                'total_revenue_generated' => $shareReferralsCollection->sum('total_revenue_generated'),
                'share_referrals' => $shareReferralsCollection,
            ];
            $isAdmin = true;
        } else {
            $stats = $this->noteShareService->getUserShareStats($user);
            $isAdmin = false;
        }

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
            'isAdmin' => $isAdmin,
        ]);
    }
}
