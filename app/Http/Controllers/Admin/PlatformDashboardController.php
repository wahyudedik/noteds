<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PlatformDashboardController extends Controller
{
    /**
     * Display platform health dashboard
     */
    public function index()
    {
        $healthMetrics = $this->getHealthMetrics();
        $businessMetrics = $this->getBusinessMetrics();
        $userGrowth = $this->getUserGrowthData();
        $revenueMetrics = $this->getRevenueMetrics();
        $systemStatus = $this->getSystemStatus();

        return view('admin.platform-dashboard', compact(
            'healthMetrics',
            'businessMetrics',
            'userGrowth',
            'revenueMetrics',
            'systemStatus'
        ));
    }

    /**
     * Get platform health metrics
     */
    private function getHealthMetrics()
    {
        return Cache::remember('platform:health:metrics', 300, function () {
            return [
                'total_users' => DB::table('users')->count(),
                'active_users_today' => $this->getActiveUsersToday(),
                'active_users_week' => $this->getActiveUsersThisWeek(),
                'content_creators' => DB::table('users')
                    ->whereExists(fn($q) => $q->select('id')->from('notes')->whereColumn('notes.user_id', 'users.id'))
                    ->count(),
                'total_notes' => DB::table('notes')->count(),
                'published_notes' => DB::table('notes')->where('status', 'published')->count(),
                'total_transactions' => DB::table('transactions')->count(),
                'total_revenue' => DB::table('transactions')
                    ->where('status', 'success')
                    ->sum('amount') ?? 0,
            ];
        });
    }

    /**
     * Get business metrics
     */
    private function getBusinessMetrics()
    {
        return Cache::remember('platform:business:metrics', 300, function () {
            $today = Carbon::today();
            $yesterday = $today->copy()->subDay();
            $thisMonth = $today->copy()->startOfMonth();

            return [
                'daily_signups' => DB::table('users')
                    ->whereDate('created_at', $today)
                    ->count(),
                'daily_signups_yesterday' => DB::table('users')
                    ->whereDate('created_at', $yesterday)
                    ->count(),
                'monthly_signups' => DB::table('users')
                    ->where('created_at', '>=', $thisMonth)
                    ->count(),
                'daily_gmv' => DB::table('transactions')
                    ->where('status', 'success')
                    ->whereDate('created_at', $today)
                    ->sum('amount') ?? 0,
                'daily_gmv_yesterday' => DB::table('transactions')
                    ->where('status', 'success')
                    ->whereDate('created_at', $yesterday)
                    ->sum('amount') ?? 0,
                'monthly_gmv' => DB::table('transactions')
                    ->where('status', 'success')
                    ->where('created_at', '>=', $thisMonth)
                    ->sum('amount') ?? 0,
                'avg_order_value' => DB::table('transactions')
                    ->where('status', 'success')
                    ->whereDate('created_at', $today)
                    ->avg('amount') ?? 0,
                'platform_commission_today' => DB::table('transactions')
                    ->where('status', 'success')
                    ->whereDate('created_at', $today)
                    ->sum('commission') ?? 0,
            ];
        });
    }

    /**
     * Get user growth data for charts
     */
    private function getUserGrowthData()
    {
        return Cache::remember('platform:user:growth', 600, function () {
            $last30Days = collect();

            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $count = DB::table('users')
                    ->where('created_at', '<=', $date->endOfDay())
                    ->count();

                $last30Days->push([
                    'date' => $date->format('Y-m-d'),
                    'total' => $count,
                ]);
            }

            return $last30Days;
        });
    }

    /**
     * Get revenue metrics
     */
    private function getRevenueMetrics()
    {
        return Cache::remember('platform:revenue:metrics', 300, function () {
            $thisMonth = Carbon::today()->startOfMonth();

            return [
                'total_sales' => DB::table('purchased_notes')->count(),
                'repeat_customer_rate' => $this->getRepeatCustomerRate(),
                'avg_customer_ltv' => $this->getAverageCustomerLTV(),
                'top_categories' => collect([
                    ['name' => 'Technology', 'count' => DB::table('notes')->where('ecosystem_category', 'technology')->count()],
                    ['name' => 'Design', 'count' => DB::table('notes')->where('ecosystem_category', 'design')->count()],
                    ['name' => 'Business', 'count' => DB::table('notes')->where('ecosystem_category', 'business')->count()],
                ])->sortByDesc('count')->take(5),
                'payment_methods' => DB::table('transactions')
                    ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                    ->groupBy('payment_method')
                    ->get(),
                'affiliate_earnings' => DB::table('affiliate_commissions')
                    ->where('created_at', '>=', $thisMonth)
                    ->sum('commission_amount') ?? 0,
            ];
        });
    }

    /**
     * Get system status
     */
    private function getSystemStatus()
    {
        return [
            'database_connection' => $this->checkDatabaseConnection(),
            'cache_status' => $this->checkCache(),
            'queue_status' => $this->checkQueueStatus(),
            'payment_gateway' => $this->checkPaymentGateway(),
            'storage_usage' => $this->getStorageUsage(),
            'last_backup' => $this->getLastBackupTime(),
        ];
    }

    /**
     * Get active users today
     */
    private function getActiveUsersToday()
    {
        // Count distinct users who made transactions today
        return DB::table('transactions')
            ->whereDate('created_at', Carbon::today())
            ->select('user_id')
            ->distinct()
            ->count() ?? 0;
    }

    /**
     * Get active users this week
     */
    private function getActiveUsersThisWeek()
    {
        // Count distinct users who made transactions this week
        return DB::table('transactions')
            ->where('created_at', '>=', Carbon::today()->subDays(7))
            ->select('user_id')
            ->distinct()
            ->count() ?? 0;
    }

    /**
     * Get repeat customer rate
     */
    private function getRepeatCustomerRate()
    {
        $totalCustomers = DB::table('purchased_notes')
            ->distinct('user_id')
            ->count('user_id');

        $repeatCustomers = DB::table('purchased_notes')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        return $totalCustomers > 0
            ? round(($repeatCustomers / $totalCustomers) * 100, 2)
            : 0;
    }

    /**
     * Get average customer lifetime value
     */
    private function getAverageCustomerLTV()
    {
        // Calculate average spending per customer using subquery
        $subquery = DB::table('purchased_notes')
            ->select('user_id', DB::raw('SUM(purchase_price) as total_spent'))
            ->groupBy('user_id');

        return DB::table(DB::raw("({$subquery->toSql()}) as subquery"))
            ->mergeBindings($subquery)
            ->avg('total_spent') ?? 0;
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache status
     */
    private function checkCache(): bool
    {
        try {
            Cache::put('health_check', 'ok', 60);
            return Cache::get('health_check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check queue status
     */
    private function checkQueueStatus(): array
    {
        return [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];
    }

    /**
     * Check payment gateway
     */
    private function checkPaymentGateway(): bool
    {
        // TODO: Implement Midtrans health check
        return true;
    }

    /**
     * Get storage usage
     */
    private function getStorageUsage(): array
    {
        $storagePath = storage_path('app');
        $size = 0;

        if (is_dir($storagePath)) {
            $size = $this->dirSize($storagePath);
        }

        return [
            'used' => $size,
            'used_readable' => $this->formatBytes($size),
            'percentage' => 45, // Placeholder - configure based on quota
        ];
    }

    /**
     * Get last backup time
     */
    private function getLastBackupTime(): ?string
    {
        // TODO: Implement backup tracking
        return now()->subDay()->format('Y-m-d H:i:s');
    }

    /**
     * Calculate directory size recursively
     */
    private function dirSize($dir): int
    {
        $size = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->dirSize($each);
        }
        return $size;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * API endpoint for real-time metrics
     */
    public function metrics()
    {
        return response()->json([
            'health' => $this->getHealthMetrics(),
            'business' => $this->getBusinessMetrics(),
            'revenue' => $this->getRevenueMetrics(),
            'system' => $this->getSystemStatus(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Export metrics as CSV
     */
    public function export()
    {
        $filename = 'platform-metrics-' . now()->format('Y-m-d-H-i-s') . '.csv';
        $healthMetrics = $this->getHealthMetrics();
        $businessMetrics = $this->getBusinessMetrics();
        $revenueMetrics = $this->getRevenueMetrics();

        $metrics = [
            ['Metric', 'Value', 'Date'],
            ['Total Users', $healthMetrics['total_users'], now()],
            ['Active Users Today', $healthMetrics['active_users_today'], now()],
            ['Total Notes', $healthMetrics['total_notes'], now()],
            ['Daily Revenue', $businessMetrics['daily_gmv'], now()],
            ['Monthly Revenue', $businessMetrics['monthly_gmv'], now()],
            ['Avg Order Value', $businessMetrics['avg_order_value'], now()],
            ['', '', ''],
            ['Top Categories', '', ''],
            ['Category', 'Count', 'Date'],
        ];

        // Add category data
        foreach ($revenueMetrics['top_categories'] as $category) {
            $metrics[] = [$category['name'], $category['count'], now()];
        }

        // Generate CSV content
        $csv = "";
        foreach ($metrics as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
