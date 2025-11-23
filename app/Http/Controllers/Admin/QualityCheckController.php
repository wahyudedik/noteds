<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\QualityCheck;
use App\Services\BuyerProtectionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QualityCheckController extends Controller
{
    public function __construct(
        private BuyerProtectionService $buyerProtectionService
    ) {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * List quality checks
     */
    public function index(Request $request): View
    {
        $checks = QualityCheck::with(['note', 'transaction', 'checker'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->type, function ($query) use ($request) {
                return $query->where('check_type', $request->type);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending' => QualityCheck::where('status', 'pending')->count(),
            'passed' => QualityCheck::where('status', 'passed')->count(),
            'failed' => QualityCheck::where('status', 'failed')->count(),
            'needs_review' => QualityCheck::where('status', 'needs_review')->count(),
        ];

        return view('admin.quality-checks.index', compact('checks', 'stats'));
    }

    /**
     * Show quality check
     */
    public function show(QualityCheck $qualityCheck): View
    {
        $qualityCheck->load(['note', 'transaction', 'checker']);

        return view('admin.quality-checks.show', compact('qualityCheck'));
    }

    /**
     * Perform quality check on note
     */
    public function checkNote(Request $request, Note $note): RedirectResponse
    {
        $validated = $request->validate([
            'check_type' => 'required|in:pre_publish,post_purchase,random,complaint',
        ]);

        try {
            $transaction = null;
            if ($request->has('transaction_id')) {
                $transaction = \App\Models\Transaction::findOrFail($request->transaction_id);
            }

            $qualityCheck = $this->buyerProtectionService->performQualityCheck(
                $note,
                $transaction,
                $validated['check_type'],
                auth()->user()
            );

            return redirect()->route('admin.quality-checks.show', $qualityCheck)
                ->with('success', 'Quality check completed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

