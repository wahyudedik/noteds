<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Sale;
use App\Models\User;
use App\Models\Note;
use App\Models\Commission;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminReportController extends Controller
{
    /**
     * Show revenue report
     *
     * @param Request $request
     * @return View
     */
    public function revenue(Request $request): View
    {
        $this->authorize('view-reports');

        // Get date range
        $fromDate = $request->from_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $toDate = $request->to_date ?? Carbon::now()->format('Y-m-d');

        $stats = $this->getRevenueStats($fromDate, $toDate);
        $dailyBreakdown = $this->getDailyRevenue($fromDate, $toDate);
        $topNotes = $this->getTopNotes($fromDate, $toDate);
        $chartData = $this->getRevenueChartData($fromDate, $toDate);

        return view('admin.reports.revenue-report', [
            'stats' => $stats,
            'dailyBreakdown' => $dailyBreakdown,
            'topNotes' => $topNotes,
            'chartData' => $chartData,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    /**
     * Show user report
     *
     * @param Request $request
     * @return View
     */
    public function users(Request $request): View
    {
        $this->authorize('view-reports');

        $fromDate = $request->from_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $toDate = $request->to_date ?? Carbon::now()->format('Y-m-d');

        $stats = [
            'total_users' => User::count(),
            'new_users' => User::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->count(),
            'active_users' => User::where('last_activity_at', '>=', Carbon::parse($fromDate))
                ->count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
        ];

        $usersByRole = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get();

        $dailyNewUsers = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->groupBy('date')
            ->get();

        return view('admin.reports.user-report', [
            'stats' => $stats,
            'usersByRole' => $usersByRole,
            'dailyNewUsers' => $dailyNewUsers,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    /**
     * Show note performance report
     *
     * @param Request $request
     * @return View
     */
    public function notePerformance(Request $request): View
    {
        $this->authorize('view-reports');

        $fromDate = $request->from_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $toDate = $request->to_date ?? Carbon::now()->format('Y-m-d');

        $stats = [
            'total_notes' => Note::count(),
            'published_notes' => Note::where('status', 'published')->count(),
            'new_notes' => Note::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->count(),
            'total_sales' => Sale::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->sum('quantity'),
        ];

        $topNotes = $this->getTopNotesByCategory($fromDate, $toDate);
        $notesByCategory = Note::selectRaw('category, COUNT(*) as count, SUM(price) as total_revenue')
            ->groupBy('category')
            ->get();

        return view('admin.reports.note-report', [
            'stats' => $stats,
            'topNotes' => $topNotes,
            'notesByCategory' => $notesByCategory,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    /**
     * Show affiliate report
     *
     * @param Request $request
     * @return View
     */
    public function affiliate(Request $request): View
    {
        $this->authorize('view-reports');

        $fromDate = $request->from_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $toDate = $request->to_date ?? Carbon::now()->format('Y-m-d');

        $stats = [
            'total_affiliates' => User::where('is_affiliate', true)->count(),
            'active_affiliates' => Commission::distinct('user_id')
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->count(),
            'total_commission' => Commission::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->sum('amount'),
            'total_referrals' => Commission::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->count(),
        ];

        $topAffiliates = Commission::selectRaw('user_id, COUNT(*) as referral_count, SUM(amount) as total_commission')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->groupBy('user_id')
            ->orderByDesc('total_commission')
            ->limit(20)
            ->get();

        return view('admin.reports.affiliate-report', [
            'stats' => $stats,
            'topAffiliates' => $topAffiliates,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    /**
     * Export report as PDF
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $this->authorize('export-reports');

        $reportType = $request->type ?? 'revenue';
        $fromDate = $request->from_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $toDate = $request->to_date ?? Carbon::now()->format('Y-m-d');

        if ($reportType === 'revenue') {
            $data = [
                'stats' => $this->getRevenueStats($fromDate, $toDate),
                'dailyBreakdown' => $this->getDailyRevenue($fromDate, $toDate),
                'topNotes' => $this->getTopNotes($fromDate, $toDate),
            ];
            $pdf = Pdf::loadView('admin.reports.pdf.revenue', $data);
        } elseif ($reportType === 'users') {
            $data = [
                'stats' => ['total_users' => User::count()],
                'usersByRole' => User::selectRaw('role, COUNT(*) as count')->groupBy('role')->get(),
            ];
            $pdf = Pdf::loadView('admin.reports.pdf.users', $data);
        } else {
            return redirect()->back()->with('error', 'Tipe report tidak valid');
        }

        return $pdf->download("report-$reportType-" . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Get revenue statistics
     *
     * @param string $fromDate
     * @param string $toDate
     * @return array
     */
    private function getRevenueStats(string $fromDate, string $toDate): array
    {
        $transactions = Transaction::where('status', 'completed')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate);

        $totalRevenue = $transactions->sum('amount');
        $totalCommission = $transactions->sum('platform_commission');
        $totalTransactions = $transactions->count();

        return [
            'total_revenue' => $totalRevenue,
            'platform_commission' => $totalCommission,
            'seller_earnings' => $totalRevenue - $totalCommission,
            'total_transactions' => $totalTransactions,
            'average_transaction' => $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0,
        ];
    }

    /**
     * Get daily revenue breakdown
     *
     * @param string $fromDate
     * @param string $toDate
     * @return \Illuminate\Support\Collection
     */
    private function getDailyRevenue(string $fromDate, string $toDate)
    {
        return Transaction::selectRaw('
            DATE(created_at) as date,
            COUNT(*) as transactions,
            COUNT(DISTINCT note_id) as unique_notes,
            SUM(amount) as total_revenue,
            SUM(platform_commission) as commission,
            SUM(amount) - SUM(platform_commission) as net_revenue,
            AVG(amount) as avg_sale
        ')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();
    }

    /**
     * Get top performing notes
     *
     * @param string $fromDate
     * @param string $toDate
     * @return \Illuminate\Support\Collection
     */
    private function getTopNotes(string $fromDate, string $toDate)
    {
        return Transaction::selectRaw('
            note_id,
            COUNT(*) as sales,
            SUM(amount) as total_revenue,
            AVG(amount) as avg_price
        ')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->groupBy('note_id')
            ->orderByDesc('total_revenue')
            ->limit(20)
            ->with('note')
            ->get();
    }

    /**
     * Get revenue chart data
     *
     * @param string $fromDate
     * @param string $toDate
     * @return array
     */
    private function getRevenueChartData(string $fromDate, string $toDate): array
    {
        $daily = $this->getDailyRevenue($fromDate, $toDate);

        return [
            'labels' => $daily->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray(),
            'revenue' => $daily->pluck('total_revenue')->toArray(),
            'transactions' => $daily->pluck('transactions')->toArray(),
        ];
    }

    /**
     * Get top notes by category
     *
     * @param string $fromDate
     * @param string $toDate
     * @return \Illuminate\Support\Collection
     */
    private function getTopNotesByCategory(string $fromDate, string $toDate)
    {
        return Note::selectRaw('
            category_id,
            COUNT(*) as note_count,
            SUM((SELECT COUNT(*) FROM sales WHERE sales.note_id = notes.id AND sales.created_at >= ? AND sales.created_at <= ?)) as total_sales
        ', [$fromDate, $toDate])
            ->groupBy('category_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();
    }
}
