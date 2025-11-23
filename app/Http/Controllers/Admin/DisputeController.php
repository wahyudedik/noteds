<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Services\BuyerProtectionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function __construct(
        private BuyerProtectionService $buyerProtectionService
    ) {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * List all disputes
     */
    public function index(Request $request): View
    {
        $disputes = Dispute::with(['buyer', 'seller', 'note', 'transaction', 'refund'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'open' => Dispute::where('status', 'open')->count(),
            'in_review' => Dispute::where('status', 'in_review')->count(),
            'resolved' => Dispute::where('status', 'resolved')->count(),
            'closed' => Dispute::where('status', 'closed')->count(),
        ];

        return view('admin.disputes.index', compact('disputes', 'stats'));
    }

    /**
     * Show dispute
     */
    public function show(Dispute $dispute): View
    {
        $dispute->load(['buyer', 'seller', 'note', 'transaction', 'refund', 'resolver']);

        return view('admin.disputes.show', compact('dispute'));
    }

    /**
     * Resolve dispute
     */
    public function resolve(Request $request, Dispute $dispute): RedirectResponse
    {
        if ($dispute->isResolved()) {
            return back()->with('error', 'Dispute is already resolved.');
        }

        $validated = $request->validate([
            'resolution' => 'required|string|min:20|max:2000',
            'action' => 'nullable|in:approve_refund,reject_refund,partial_refund,other',
        ]);

        $this->buyerProtectionService->resolveDispute(
            $dispute,
            auth()->user(),
            $validated['resolution'],
            $validated['action'] ?? null
        );

        return redirect()->route('admin.disputes.show', $dispute)
            ->with('success', 'Dispute resolved successfully.');
    }
}

