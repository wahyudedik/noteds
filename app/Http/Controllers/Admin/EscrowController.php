<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EscrowController extends Controller
{
    public function __construct(
        private EscrowService $escrowService
    ) {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * List all escrows
     */
    public function index(Request $request): View
    {
        $escrows = Escrow::with(['buyer', 'seller', 'note', 'transaction', 'dispute'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending' => Escrow::where('status', 'pending')->count(),
            'funded' => Escrow::where('status', 'funded')->count(),
            'released' => Escrow::where('status', 'released')->count(),
            'refunded' => Escrow::where('status', 'refunded')->count(),
            'disputed' => Escrow::where('status', 'disputed')->count(),
            'total_amount_funded' => Escrow::where('status', 'funded')->sum('amount'),
        ];

        return view('admin.escrows.index', compact('escrows', 'stats'));
    }

    /**
     * Show escrow details
     */
    public function show(Escrow $escrow): View
    {
        $escrow->load(['buyer', 'seller', 'note', 'transaction', 'dispute', 'releaser', 'refunder']);

        return view('admin.escrows.show', compact('escrow'));
    }

    /**
     * Manually release escrow (admin)
     */
    public function release(Request $request, Escrow $escrow): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->escrowService->releaseEscrow(
                $escrow,
                auth()->user(),
                $validated['notes'] ?? 'Manually released by admin'
            );

            return redirect()->route('admin.escrows.show', $escrow)
                ->with('success', 'Escrow released successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Manually refund escrow (admin)
     */
    public function refund(Request $request, Escrow $escrow): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        try {
            $this->escrowService->refundEscrow(
                $escrow,
                auth()->user(),
                $validated['reason']
            );

            return redirect()->route('admin.escrows.show', $escrow)
                ->with('success', 'Escrow refunded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

