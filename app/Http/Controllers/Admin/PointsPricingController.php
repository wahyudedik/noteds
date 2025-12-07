<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointsPricingConfig;
use App\Models\PointRedemption;
use Illuminate\Http\Request;

class PointsPricingController extends Controller
{
    /**
     * Display a listing of pricing configurations
     */
    public function index()
    {
        $configs = PointsPricingConfig::orderBy('type')->orderBy('points_required')->paginate(15);
        $stats = [
            'total_configs' => PointsPricingConfig::count(),
            'active_configs' => PointsPricingConfig::where('is_active', true)->count(),
            'discount_configs' => PointsPricingConfig::where('type', 'discount')->count(),
            'premium_configs' => PointsPricingConfig::where('type', 'premium_feature')->count(),
            'total_redemptions' => PointRedemption::count(),
            'active_redemptions' => PointRedemption::where('status', 'active')->count(),
        ];

        return view('admin.points-pricing.index', compact('configs', 'stats'));
    }

    /**
     * Show the form for creating a new resource
     */
    public function create()
    {
        return view('admin.points-pricing.create');
    }

    /**
     * Store a newly created resource in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:discount,premium_feature',
            'points_required' => 'required|integer|min:1',
            'discount_amount' => 'nullable|numeric|min:0|required_if:type,discount',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'premium_days' => 'nullable|integer|min:1|required_if:type,premium_feature',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'daily_limit' => 'nullable|integer|min:1',
            'user_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
        ]);

        try {
            PointsPricingConfig::create($validated);

            return redirect()
                ->route('admin.points-pricing.index')
                ->with('success', 'Points pricing configuration created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource
     */
    public function show(PointsPricingConfig $pointsPricingConfig)
    {
        $redemptions = PointRedemption::latest()
            ->limit(10)
            ->get();

        return view('admin.points-pricing.show', compact('pointsPricingConfig', 'redemptions'));
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit(PointsPricingConfig $pointsPricingConfig)
    {
        return view('admin.points-pricing.edit', compact('pointsPricingConfig'));
    }

    /**
     * Update the specified resource in storage
     */
    public function update(Request $request, PointsPricingConfig $pointsPricingConfig)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:discount,premium_feature',
            'points_required' => 'required|integer|min:1',
            'discount_amount' => 'nullable|numeric|min:0|required_if:type,discount',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'premium_days' => 'nullable|integer|min:1|required_if:type,premium_feature',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'daily_limit' => 'nullable|integer|min:1',
            'user_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
        ]);

        try {
            $pointsPricingConfig->update($validated);

            return redirect()
                ->route('admin.points-pricing.index')
                ->with('success', 'Points pricing configuration updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete the specified resource
     */
    public function destroy(PointsPricingConfig $pointsPricingConfig)
    {
        try {
            $pointsPricingConfig->delete();

            return redirect()
                ->route('admin.points-pricing.index')
                ->with('success', 'Points pricing configuration deleted successfully');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * Show redemption monitoring dashboard
     */
    public function monitoring()
    {
        $today_redemptions = PointRedemption::whereDate('created_at', today())
            ->with('user:id,name,email')
            ->latest()
            ->paginate(15);

        $stats = [
            'today_count' => PointRedemption::whereDate('created_at', today())->count(),
            'today_value' => PointRedemption::whereDate('created_at', today())
                ->sum('points_used'),
            'week_count' => PointRedemption::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'active_count' => PointRedemption::where('status', 'active')->count(),
        ];

        return view('admin.points-pricing.monitoring', compact('today_redemptions', 'stats'));
    }

    /**
     * Export redemption report
     */
    public function exportReport(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $redemptions = PointRedemption::when($from, function ($query) use ($from) {
            return $query->whereDate('created_at', '>=', $from);
        })
            ->when($to, function ($query) use ($to) {
                return $query->whereDate('created_at', '<=', $to);
            })
            ->with('user:id,name,email')
            ->get();

        $filename = "points_redemptions_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($redemptions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'User', 'Email', 'Type', 'Points Used', 'Amount', 'Status']);

            foreach ($redemptions as $redemption) {
                fputcsv($file, [
                    $redemption->created_at->format('Y-m-d H:i'),
                    $redemption->user->name,
                    $redemption->user->email,
                    $redemption->redemption_type,
                    $redemption->points_used,
                    $redemption->discount_amount ?? 'N/A',
                    $redemption->status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
