<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\ContestEntry;
use App\Services\ContestService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ContestController extends Controller
{
    public function __construct(
        private ContestService $contestService
    ) {
        $this->middleware(['auth', 'verified', 'role:admin']);
    }

    /**
     * List all contests
     */
    public function index(): View
    {
        $contests = Contest::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.contests.index', [
            'contests' => $contests,
        ]);
    }

    /**
     * Show contest form
     */
    public function create(): View
    {
        return view('admin.contests.create');
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
            'status' => 'required|in:draft,open,voting,closed,winners_announced',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'voting_start_date' => 'nullable|date',
            'voting_end_date' => 'nullable|date|after:voting_start_date',
            'max_entries_per_user' => 'required|integer|min:1',
            'prizes' => 'nullable|array',
            'prizes_json' => 'nullable|string',
            'rules' => 'nullable|array',
            'rules_text' => 'nullable|string',
            'banner_image' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

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

        $validated['created_by'] = auth()->id();

        Contest::create($validated);

        return redirect()->route('admin.contests.index')
            ->with('success', 'Contest created successfully.');
    }

    /**
     * Show contest edit form
     */
    public function edit(Contest $contest): View
    {
        return view('admin.contests.edit', [
            'contest' => $contest,
        ]);
    }

    /**
     * Update contest
     */
    public function update(Request $request, Contest $contest): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:contests,slug,' . $contest->id,
            'description' => 'required|string',
            'type' => 'required|in:monthly,themed,custom',
            'theme' => 'nullable|string|max:255',
            'status' => 'required|in:draft,open,voting,closed,winners_announced',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'voting_start_date' => 'nullable|date',
            'voting_end_date' => 'nullable|date|after:voting_start_date',
            'max_entries_per_user' => 'required|integer|min:1',
            'prizes' => 'nullable|array',
            'prizes_json' => 'nullable|string',
            'rules' => 'nullable|array',
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

        return redirect()->route('admin.contests.index')
            ->with('success', 'Contest updated successfully.');
    }

    /**
     * Delete contest
     */
    public function destroy(Contest $contest): RedirectResponse
    {
        $contest->delete();

        return redirect()->route('admin.contests.index')
            ->with('success', 'Contest deleted successfully.');
    }

    /**
     * List contest entries
     */
    public function entries(Contest $contest): View
    {
        $entries = $contest->entries()
            ->with(['user', 'note', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.contests.entries', [
            'contest' => $contest,
            'entries' => $entries,
        ]);
    }

    /**
     * Show entry details
     */
    public function showEntry(ContestEntry $entry): View
    {
        $entry->load(['contest', 'user', 'note', 'reviewer', 'votes.user']);

        return view('admin.contests.entry-show', [
            'entry' => $entry,
        ]);
    }

    /**
     * Approve entry
     */
    public function approveEntry(ContestEntry $entry): RedirectResponse
    {
        $this->contestService->approveEntry($entry, auth()->user());

        return redirect()->back()
            ->with('success', 'Entry approved successfully.');
    }

    /**
     * Reject entry
     */
    public function rejectEntry(Request $request, ContestEntry $entry): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $this->contestService->rejectEntry($entry, auth()->user(), $validated['reason']);

        return redirect()->back()
            ->with('success', 'Entry rejected.');
    }

    /**
     * Select winners
     */
    public function selectWinners(Contest $contest): RedirectResponse
    {
        try {
            $winners = $this->contestService->selectWinners($contest);

            return redirect()->route('admin.contests.show', $contest)
                ->with('success', count($winners) . ' winners selected successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Distribute prizes
     */
    public function distributePrizes(Contest $contest): RedirectResponse
    {
        try {
            $distributed = $this->contestService->distributePrizes($contest);

            return redirect()->route('admin.contests.show', $contest)
                ->with('success', 'Prizes distributed to ' . count($distributed) . ' winners.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show contest details (admin)
     */
    public function show(Contest $contest): View
    {
        $contest->load(['entries.note', 'entries.user', 'winners.entry.note', 'winners.user']);

        return view('admin.contests.show', [
            'contest' => $contest,
        ]);
    }
}

