<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Services\ContestService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ContestBuyerController extends Controller
{
    public function __construct(
        private ContestService $contestService
    ) {
        $this->middleware(['auth', 'verified', 'username.setup', 'buyer']);
    }

    /**
     * Show my contests
     */
    public function myContests(): View
    {
        $contests = Contest::where('created_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('contests.my-contests', [
            'contests' => $contests,
        ]);
    }

    /**
     * Show create contest form
     */
    public function create(): View
    {
        return view('contests.create');
    }

    /**
     * Store new contest
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:contests,slug',
            'description' => 'required|string',
            'type' => 'required|in:monthly,themed,custom',
            'theme' => 'nullable|string|max:255',
            'status' => 'required|in:draft,open,voting,closed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'voting_start_date' => 'nullable|date',
            'voting_end_date' => 'nullable|date|after:voting_start_date',
            'max_entries_per_user' => 'required|integer|min:1|max:20',
            'prizes_json' => 'nullable|string',
            'rules_text' => 'nullable|string',
            'banner_image' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Parse prizes JSON if provided
        $prizes = [];
        $totalPrizeAmount = 0;
        if ($request->has('prizes_json') && $request->prizes_json) {
            try {
                $prizes = json_decode($request->prizes_json, true);
                // Calculate total prize amount (assuming simple numeric array or array of amounts)
                foreach ($prizes as $prize) {
                    if (is_numeric($prize)) {
                        $totalPrizeAmount += (float) $prize;
                    } elseif (is_array($prize) && isset($prize['amount'])) {
                        $totalPrizeAmount += (float) $prize['amount'];
                    }
                }
                $validated['prizes'] = $prizes;
            } catch (\Exception $e) {
                return back()->with('error', 'Invalid JSON format for prizes.');
            }
        } else {
            $validated['prizes'] = [];
        }

        // Parse rules text if provided
        if ($request->has('rules_text') && $request->rules_text) {
            $validated['rules'] = array_values(array_filter(
                array_map('trim', explode("\n", $request->rules_text))
            ));
        } else {
            $validated['rules'] = [];
        }

        unset($validated['prizes_json'], $validated['rules_text']);
        $validated['created_by'] = auth()->id();

        $buyer = auth()->user();

        // Validate contest creation eligibility (including balance check)
        $validation = $this->contestService->validateContestCreation($buyer, $totalPrizeAmount);
        if (!$validation['valid']) {
            return back()->with('error', $validation['message']);
        }

        // Create the contest first
        $contest = Contest::create($validated);

        // If prizes are set and amount > 0, freeze the prizes
        if ($totalPrizeAmount > 0) {
            $freezeResult = $this->contestService->freezePrizes($contest, $buyer, $totalPrizeAmount);
            if (!$freezeResult['success']) {
                // Delete contest if freezing failed
                $contest->delete();
                return back()->with('error', $freezeResult['message']);
            }
        }

        return redirect()->route('contests.show', $contest)
            ->with('success', 'Contest created successfully! Prize amount has been frozen in your account.');
    }

    /**
     * Show edit contest form
     */
    public function edit(Contest $contest): View
    {
        // Check if user owns this contest
        if ($contest->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to edit this contest.');
        }

        return view('contests.edit', [
            'contest' => $contest,
        ]);
    }

    /**
     * Update contest
     */
    public function update(Request $request, Contest $contest): RedirectResponse
    {
        // Check if user owns this contest
        if ($contest->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to update this contest.');
        }

        // Only allow editing draft contests
        if ($contest->status !== 'draft') {
            return back()->with('error', 'You can only edit draft contests.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:contests,slug,' . $contest->id,
            'description' => 'required|string',
            'type' => 'required|in:monthly,themed,custom',
            'theme' => 'nullable|string|max:255',
            'status' => 'required|in:draft,open,voting,closed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'voting_start_date' => 'nullable|date',
            'voting_end_date' => 'nullable|date|after:voting_start_date',
            'max_entries_per_user' => 'required|integer|min:1|max:20',
            'prizes_json' => 'nullable|string',
            'rules_text' => 'nullable|string',
            'banner_image' => 'nullable|string',
        ]);

        // Parse prizes JSON if provided
        if ($request->has('prizes_json') && $request->prizes_json) {
            try {
                $validated['prizes'] = json_decode($request->prizes_json, true);
            } catch (\Exception $e) {
                return back()->with('error', 'Invalid JSON format for prizes.');
            }
        }

        // Parse rules text if provided
        if ($request->has('rules_text') && $request->rules_text) {
            $validated['rules'] = array_values(array_filter(
                array_map('trim', explode("\n", $request->rules_text))
            ));
        }

        unset($validated['prizes_json'], $validated['rules_text']);

        $contest->update($validated);

        return redirect()->route('contests.show', $contest)
            ->with('success', 'Contest updated successfully!');
    }

    /**
     * Delete contest
     */
    public function destroy(Contest $contest): RedirectResponse
    {
        // Check if user owns this contest
        if ($contest->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to delete this contest.');
        }

        // Only allow deleting draft contests
        if ($contest->status !== 'draft') {
            return back()->with('error', 'You can only delete draft contests.');
        }

        $buyer = auth()->user();
        $frozenAmount = $contest->frozen_amount ?? 0;

        // Refund frozen amount if contest has frozen prizes
        if ($frozenAmount > 0 && $buyer->wallet) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($buyer, $contest, $frozenAmount) {
                    // Add back to wallet
                    $buyer->wallet->increment('balance', $frozenAmount);

                    // Record refund transaction
                    \App\Models\WalletTransaction::create([
                        'user_id' => $buyer->id,
                        'type' => 'contest_refund',
                        'amount' => $frozenAmount,
                        'description' => "Prize refunded - contest deleted: {$contest->title}",
                        'reference_id' => $contest->id,
                        'reference_type' => Contest::class,
                        'status' => 'completed',
                    ]);
                });
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Contest refund failed', [
                    'contest_id' => $contest->id,
                    'buyer_id' => $buyer->id,
                    'amount' => $frozenAmount,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $contest->delete();

        return redirect()->route('contests.my-contests')
            ->with('success', 'Contest deleted successfully! Frozen prize amount has been refunded to your wallet.');
    }
}
