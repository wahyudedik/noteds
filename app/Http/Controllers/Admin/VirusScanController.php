<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VirusScan;
use App\Services\ClamAVService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VirusScanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of virus scans
     */
    public function index(Request $request): View
    {
        $query = VirusScan::with(['scannedByUser', 'note'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('scan_status', $request->status);
        }

        // Filter by infected files
        if ($request->boolean('infected_only')) {
            $query->where('scan_status', 'infected');
        }

        // Filter by quarantined files
        if ($request->boolean('quarantined_only')) {
            $query->where('is_quarantined', true);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhere('threat_name', 'like', "%{$search}%")
                  ->orWhere('file_path', 'like', "%{$search}%");
            });
        }

        $scans = $query->paginate(50);
        $clamAVService = app(ClamAVService::class);
        $statistics = $clamAVService->getStatistics([
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ]);

        return view('admin.virus-scans.index', compact('scans', 'statistics'));
    }

    /**
     * Show scan details
     */
    public function show(VirusScan $virusScan): View
    {
        $virusScan->load(['scannedByUser', 'note']);
        
        return view('admin.virus-scans.show', compact('virusScan'));
    }

    /**
     * Quarantine an infected file
     */
    public function quarantine(VirusScan $virusScan): RedirectResponse
    {
        if (!$virusScan->isInfected()) {
            return redirect()->route('admin.virus-scans.show', $virusScan)
                ->with('error', 'File tidak terinfeksi virus.');
        }

        $clamAVService = app(ClamAVService::class);
        $result = $clamAVService->quarantineFile($virusScan);

        if ($result) {
            return redirect()->route('admin.virus-scans.show', $virusScan)
                ->with('success', 'File berhasil di-quarantine.');
        }

        return redirect()->route('admin.virus-scans.show', $virusScan)
            ->with('error', 'Gagal meng-quarantine file.');
    }

    /**
     * Restore a quarantined file
     */
    public function restore(Request $request, VirusScan $virusScan): RedirectResponse
    {
        if (!$virusScan->isQuarantined()) {
            return redirect()->route('admin.virus-scans.show', $virusScan)
                ->with('error', 'File tidak dalam status quarantine.');
        }

        $request->validate([
            'restore_path' => 'required|string',
        ]);

        $clamAVService = app(ClamAVService::class);
        $result = $clamAVService->restoreFile($virusScan, $request->restore_path);

        if ($result) {
            return redirect()->route('admin.virus-scans.show', $virusScan)
                ->with('success', 'File berhasil di-restore.');
        }

        return redirect()->route('admin.virus-scans.show', $virusScan)
            ->with('error', 'Gagal me-restore file.');
    }

    /**
     * Delete a quarantined file permanently
     */
    public function destroy(VirusScan $virusScan): RedirectResponse
    {
        if (!$virusScan->isQuarantined()) {
            return redirect()->route('admin.virus-scans.index')
                ->with('error', 'File tidak dalam status quarantine.');
        }

        $clamAVService = app(ClamAVService::class);
        $result = $clamAVService->deleteQuarantinedFile($virusScan);

        if ($result) {
            return redirect()->route('admin.virus-scans.index')
                ->with('success', 'File berhasil dihapus permanen.');
        }

        return redirect()->route('admin.virus-scans.index')
            ->with('error', 'Gagal menghapus file.');
    }

    /**
     * Manually scan a file
     */
    public function scan(Request $request): RedirectResponse
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        $clamAVService = app(ClamAVService::class);
        
        if (!$clamAVService->isAvailable()) {
            return redirect()->route('admin.virus-scans.index')
                ->with('error', 'ClamAV tidak tersedia. Pastikan ClamAV sudah terinstall dan berjalan.');
        }

        if (!file_exists($request->file_path)) {
            return redirect()->route('admin.virus-scans.index')
                ->with('error', 'File tidak ditemukan: ' . $request->file_path);
        }

        $scan = $clamAVService->scanFile(
            $request->file_path,
            basename($request->file_path),
            auth()->user(),
            'manual'
        );

        return redirect()->route('admin.virus-scans.show', $scan)
            ->with('success', 'Scan selesai. Status: ' . $scan->scan_status);
    }

    /**
     * Get scan statistics (AJAX)
     */
    public function statistics(Request $request)
    {
        $clamAVService = app(ClamAVService::class);
        $statistics = $clamAVService->getStatistics([
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ]);

        return response()->json($statistics);
    }
}

