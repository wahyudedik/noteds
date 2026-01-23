<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class NewsletterAnalyticsController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $campaignId = (int) $request->query('campaign_id');
        $query = DB::table('newsletter_sends');
        if ($campaignId) $query->where('campaign_id', $campaignId);
        $total = (clone $query)->count();
        $opens = (clone $query)->where('open_count', '>', 0)->count();
        $clicks = (clone $query)->where('click_count', '>', 0)->count();
        $unsubs = DB::table('newsletter_subscribers')->where('status', 'unsubscribed')->count();
        $data = [
            'total' => $total,
            'open_rate' => $total ? round(($opens / $total) * 100, 2) : 0,
            'click_rate' => $total ? round(($clicks / $total) * 100, 2) : 0,
            'unsubscribe_count' => $unsubs,
        ];
        return response()->json($data);
    }

    public function exportCsv(Request $request)
    {
        $campaignId = (int) $request->query('campaign_id');
        $rows = DB::table('newsletter_sends')->where('campaign_id', $campaignId)->orderByDesc('id')->get();
        $csv = "email,sent_at,open_count,click_count\n";
        foreach ($rows as $r) {
            $csv .= "{$r->email},{$r->sent_at},{$r->open_count},{$r->click_count}\n";
        }
        return response($csv, 200)->header('Content-Type', 'text/csv');
    }

    public function exportPdf(Request $request)
    {
        $campaignId = (int) $request->query('campaign_id');
        $rows = DB::table('newsletter_sends')->where('campaign_id', $campaignId)->orderByDesc('id')->get();
        $html = '<h3>Campaign Report</h3><table border="1" cellpadding="4" cellspacing="0"><tr><th>Email</th><th>Sent At</th><th>Opens</th><th>Clicks</th></tr>';
        foreach ($rows as $r) {
            $html .= "<tr><td>{$r->email}</td><td>{$r->sent_at}</td><td>{$r->open_count}</td><td>{$r->click_count}</td></tr>";
        }
        $html .= '</table>';
        $pdf = Pdf::loadHTML($html);
        return $pdf->download('campaign_report.pdf');
    }
}
