<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteViewHistory;
use App\Models\Transaction;
use App\Models\PurchasedNote;
use App\Models\NoteAbTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class SellerAnalyticsController extends Controller
{
    /**
     * Display seller analytics dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        // Time range selection (default: last 30 days)
        $timeRange = $request->get('time_range', '30days');
        $startDate = $this->getStartDate($timeRange);
        $endDate = now();

        // Get seller notes
        $sellerNotes = Note::where('user_id', $user->id)
            ->where('is_public', true)
            ->pluck('id')
            ->toArray();

        // Revenue statistics
        $revenueData = $this->getRevenueData($sellerNotes, $startDate, $endDate, $request->get('group_by', 'day'));
        
        // Conversion rate tracking (views → purchases)
        $conversionData = $this->getConversionData($sellerNotes, $startDate, $endDate);
        
        // Traffic sources
        $trafficSources = $this->getTrafficSources($sellerNotes, $startDate, $endDate);
        
        // Geographic analytics
        $geographicData = $this->getGeographicData($sellerNotes, $startDate, $endDate);
        
        // Peak hours analysis
        $peakHours = $this->getPeakHours($sellerNotes, $startDate, $endDate);
        
        // A/B tests
        $abTests = NoteAbTest::where('user_id', $user->id)
            ->with('note:id,title')
            ->latest()
            ->limit(10)
            ->get();

        // Overall statistics
        $stats = $this->getOverallStats($sellerNotes, $startDate, $endDate);

        return view('40-shared/seller/analytics/index', compact(
            'revenueData',
            'conversionData',
            'trafficSources',
            'geographicData',
            'peakHours',
            'abTests',
            'stats',
            'timeRange',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get revenue data for charts.
     */
    private function getRevenueData(array $noteIds, Carbon $startDate, Carbon $endDate, string $groupBy): array
    {
        $format = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $revenue = Transaction::whereIn('note_id', $noteIds)
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period, SUM(amount - platform_fee - COALESCE(creator_commission, 0)) as revenue")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'labels' => $revenue->pluck('period')->toArray(),
            'data' => $revenue->pluck('revenue')->toArray(),
        ];
    }

    /**
     * Get conversion rate data (views → purchases).
     */
    private function getConversionData(array $noteIds, Carbon $startDate, Carbon $endDate): array
    {
        // Get views count
        $views = NoteViewHistory::whereIn('note_id', $noteIds)
            ->whereBetween('viewed_at', [$startDate, $endDate])
            ->count();

        // Get purchases count
        $purchases = PurchasedNote::whereIn('note_id', $noteIds)
            ->whereBetween('purchased_at', [$startDate, $endDate])
            ->count();

        // Get conversion rate by note
        $noteConversions = NoteViewHistory::whereIn('note_id', $noteIds)
            ->whereBetween('viewed_at', [$startDate, $endDate])
            ->select('note_id', DB::raw('COUNT(*) as views'))
            ->groupBy('note_id')
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $purchases = PurchasedNote::where('note_id', $item->note_id)
                    ->whereBetween('purchased_at', [$startDate, $endDate])
                    ->count();
                
                return [
                    'note_id' => $item->note_id,
                    'note_title' => Note::find($item->note_id)->title ?? 'Unknown',
                    'views' => $item->views,
                    'purchases' => $purchases,
                    'conversion_rate' => $item->views > 0 ? ($purchases / $item->views) * 100 : 0,
                ];
            })
            ->sortByDesc('conversion_rate')
            ->take(10);

        return [
            'total_views' => $views,
            'total_purchases' => $purchases,
            'overall_conversion_rate' => $views > 0 ? ($purchases / $views) * 100 : 0,
            'by_note' => $noteConversions,
        ];
    }

    /**
     * Get traffic sources data.
     */
    private function getTrafficSources(array $noteIds, Carbon $startDate, Carbon $endDate): array
    {
        $sources = NoteViewHistory::whereIn('note_id', $noteIds)
            ->whereBetween('viewed_at', [$startDate, $endDate])
            ->select('traffic_source', DB::raw('COUNT(*) as views'))
            ->groupBy('traffic_source')
            ->get();

        $purchasesBySource = DB::table('note_view_history')
            ->join('purchased_notes', function($join) use ($startDate, $endDate) {
                $join->on('note_view_history.note_id', '=', 'purchased_notes.note_id')
                     ->whereBetween('purchased_notes.purchased_at', [$startDate, $endDate]);
            })
            ->whereIn('note_view_history.note_id', $noteIds)
            ->whereBetween('note_view_history.viewed_at', [$startDate, $endDate])
            ->select('note_view_history.traffic_source', DB::raw('COUNT(DISTINCT purchased_notes.id) as purchases'))
            ->groupBy('note_view_history.traffic_source')
            ->get()
            ->keyBy('traffic_source');

        return $sources->map(function ($source) use ($purchasesBySource) {
            $purchases = $purchasesBySource->get($source->traffic_source)->purchases ?? 0;
            return [
                'source' => $source->traffic_source ?: 'unknown',
                'views' => $source->views,
                'purchases' => $purchases,
                'conversion_rate' => $source->views > 0 ? ($purchases / $source->views) * 100 : 0,
            ];
        })->toArray();
    }

    /**
     * Get geographic analytics data.
     */
    private function getGeographicData(array $noteIds, Carbon $startDate, Carbon $endDate): array
    {
        $geographic = NoteViewHistory::whereIn('note_id', $noteIds)
            ->whereBetween('viewed_at', [$startDate, $endDate])
            ->select('country_code', 'country_name', DB::raw('COUNT(*) as views'))
            ->groupBy('country_code', 'country_name')
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        $purchasesByCountry = DB::table('note_view_history')
            ->join('purchased_notes', function($join) use ($startDate, $endDate) {
                $join->on('note_view_history.note_id', '=', 'purchased_notes.note_id')
                     ->whereBetween('purchased_notes.purchased_at', [$startDate, $endDate]);
            })
            ->whereIn('note_view_history.note_id', $noteIds)
            ->whereBetween('note_view_history.viewed_at', [$startDate, $endDate])
            ->select('note_view_history.country_code', DB::raw('COUNT(DISTINCT purchased_notes.id) as purchases'))
            ->groupBy('note_view_history.country_code')
            ->get()
            ->keyBy('country_code');

        return $geographic->map(function ($item) use ($purchasesByCountry) {
            $purchases = $purchasesByCountry->get($item->country_code)->purchases ?? 0;
            return [
                'country_code' => $item->country_code ?: 'unknown',
                'country_name' => $item->country_name ?: 'Unknown',
                'views' => $item->views,
                'purchases' => $purchases,
                'conversion_rate' => $item->views > 0 ? ($purchases / $item->views) * 100 : 0,
            ];
        })->toArray();
    }

    /**
     * Get peak hours analysis.
     */
    private function getPeakHours(array $noteIds, Carbon $startDate, Carbon $endDate): array
    {
        $hours = NoteViewHistory::whereIn('note_id', $noteIds)
            ->whereBetween('viewed_at', [$startDate, $endDate])
            ->select('hour', DB::raw('COUNT(*) as views'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $purchasesByHour = DB::table('note_view_history')
            ->join('purchased_notes', function($join) use ($startDate, $endDate) {
                $join->on('note_view_history.note_id', '=', 'purchased_notes.note_id')
                     ->whereRaw('HOUR(purchased_notes.purchased_at) = note_view_history.hour')
                     ->whereBetween('purchased_notes.purchased_at', [$startDate, $endDate]);
            })
            ->whereIn('note_view_history.note_id', $noteIds)
            ->whereBetween('note_view_history.viewed_at', [$startDate, $endDate])
            ->select('note_view_history.hour', DB::raw('COUNT(DISTINCT purchased_notes.id) as purchases'))
            ->groupBy('note_view_history.hour')
            ->get()
            ->keyBy('hour');

        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourData = $hours->where('hour', $i)->first();
            $purchases = $purchasesByHour->get($i)->purchases ?? 0;
            $views = $hourData ? $hourData->views : 0;
            
            $hourlyData[] = [
                'hour' => $i,
                'views' => $views,
                'purchases' => $purchases,
                'conversion_rate' => $views > 0 ? ($purchases / $views) * 100 : 0,
            ];
        }

        return $hourlyData;
    }

    /**
     * Get overall statistics.
     */
    private function getOverallStats(array $noteIds, Carbon $startDate, Carbon $endDate): array
    {
        $views = NoteViewHistory::whereIn('note_id', $noteIds)
            ->whereBetween('viewed_at', [$startDate, $endDate])
            ->count();

        $purchases = PurchasedNote::whereIn('note_id', $noteIds)
            ->whereBetween('purchased_at', [$startDate, $endDate])
            ->count();

        $revenue = Transaction::whereIn('note_id', $noteIds)
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('amount - platform_fee - COALESCE(creator_commission, 0)'));

        return [
            'total_views' => $views,
            'total_purchases' => $purchases,
            'total_revenue' => $revenue,
            'conversion_rate' => $views > 0 ? ($purchases / $views) * 100 : 0,
            'average_order_value' => $purchases > 0 ? $revenue / $purchases : 0,
        ];
    }

    /**
     * Get start date based on time range.
     */
    private function getStartDate(string $timeRange): Carbon
    {
        return match($timeRange) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            '6months' => now()->subMonths(6),
            '1year' => now()->subYear(),
            'all' => Carbon::create(2020, 1, 1), // Very old date
            default => now()->subDays(30),
        };
    }

    /**
     * Get API data for charts (AJAX).
     */
    public function apiRevenue(Request $request)
    {
        $user = auth()->user();
        $timeRange = $request->get('time_range', '30days');
        $groupBy = $request->get('group_by', 'day');
        $startDate = $this->getStartDate($timeRange);
        $endDate = now();

        $sellerNotes = Note::where('user_id', $user->id)
            ->where('is_public', true)
            ->pluck('id')
            ->toArray();

        return response()->json($this->getRevenueData($sellerNotes, $startDate, $endDate, $groupBy));
    }
}
