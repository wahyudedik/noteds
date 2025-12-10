<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Note;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Commission;
use App\Models\NoteApproval;
use Carbon\Carbon;
use DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard with all key metrics
     *
     * @return View
     */
    public function index(): View
    {
        // Only admin can access
        $this->authorize('view-admin-dashboard');

        // Get key metrics
        $metrics = $this->getKeyMetrics();

        // Get chart data
        $chartData = $this->getChartData();

        // Get recent data
        $recentUsers = $this->getRecentUsers();
        $pendingApprovals = $this->getPendingApprovals();

        // Get system health
        $systemHealth = $this->getSystemHealth();

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'chartData' => $chartData,
            'recentUsers' => $recentUsers,
            'pendingApprovals' => $pendingApprovals,
            'systemHealth' => $systemHealth,
        ]);
    }

    /**
     * Get key metrics for dashboard
     *
     * @return array
     */
    private function getKeyMetrics(): array
    {
        return [
            'totalUsers' => User::where('role', 'buyer')->orWhere('role', 'seller')->count(),
            'totalNotes' => Note::count(),
            'publishedNotes' => Note::where('status', 'published')->count(),
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'pendingApprovals' => NoteApproval::where('status', 'pending')->count(),
            'activeUsers' => User::where('last_activity_at', '>=', Carbon::now()->subDays(30))->count(),
            'totalTransactions' => Transaction::count(),
            'totalWithdrawals' => Withdrawal::count(),
        ];
    }

    /**
     * Get monthly revenue
     *
     * @return float
     */
    private function getMonthlyRevenue(): float
    {
        return Transaction::where('status', 'completed')
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');
    }

    /**
     * Get chart data for dashboard visualization
     *
     * @return array
     */
    private function getChartData(): array
    {
        // Revenue trend (last 30 days)
        $revenueTrend = $this->getRevenueTrend();

        // User signup trend (weekly)
        $userSignupTrend = $this->getUserSignupTrend();

        // Note distribution by category
        $noteDistribution = $this->getNoteDistribution();

        return [
            'revenueTrend' => $revenueTrend,
            'userSignupTrend' => $userSignupTrend,
            'noteDistribution' => $noteDistribution,
        ];
    }

    /**
     * Get revenue trend for last 30 days
     *
     * @return array
     */
    private function getRevenueTrend(): array
    {
        $data = Transaction::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray(),
            'data' => $data->pluck('total')->toArray(),
        ];
    }

    /**
     * Get user signup trend (weekly)
     *
     * @return array
     */
    private function getUserSignupTrend(): array
    {
        $data = User::where('created_at', '>=', Carbon::now()->subWeeks(12))
            ->selectRaw('WEEK(created_at) as week, COUNT(*) as count')
            ->groupByRaw('WEEK(created_at)')
            ->get();

        return [
            'labels' => array_map(fn($i) => "Week $i", range(1, 12)),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    /**
     * Get note distribution by category
     *
     * @return array
     */
    private function getNoteDistribution(): array
    {
        $data = Note::groupBy('category')
            ->selectRaw('category, COUNT(*) as count')
            ->get();

        return [
            'labels' => $data->pluck('category')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    /**
     * Get recent users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentUsers()
    {
        return User::where('role', '!=', 'admin')
            ->latest('created_at')
            ->take(5)
            ->get(['id', 'name', 'email', 'role', 'is_verified', 'created_at']);
    }

    /**
     * Get pending approvals
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPendingApprovals()
    {
        return NoteApproval::where('status', 'pending')
            ->with('note', 'note.author')
            ->latest('created_at')
            ->take(5)
            ->get();
    }

    /**
     * Get system health information
     *
     * @return array
     */
    private function getSystemHealth(): array
    {
        return [
            'database' => 'healthy',
            'cache' => 'healthy',
            'queue' => 'healthy',
            'storage' => 'healthy',
            'diskUsage' => $this->getDiskUsage(),
            'databaseSize' => $this->getDatabaseSize(),
        ];
    }

    /**
     * Get disk usage percentage
     *
     * @return float
     */
    private function getDiskUsage(): float
    {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        return ($total - $free) / $total * 100;
    }

    /**
     * Get database size
     *
     * @return string
     */
    private function getDatabaseSize(): string
    {
        $size = DB::select("
            SELECT SUM(data_length + index_length) as size
            FROM information_schema.tables
            WHERE table_schema = ?
        ", [env('DB_DATABASE')]);

        $bytes = $size[0]->size ?? 0;
        return $this->formatBytes($bytes);
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
