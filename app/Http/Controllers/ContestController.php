<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Note;
use App\Services\ContestService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ContestController extends Controller
{
    public function __construct(
        private ContestService $contestService
    ) {
    }

    /**
     * List all contests
     */
    public function index(): View
    {
        $contests = Contest::whereIn('status', ['open', 'voting', 'closed'])
            ->orderBy('start_date', 'desc')
            ->paginate(12);

        return view('contests.index', [
            'contests' => $contests,
        ]);
    }

    /**
     * Show contest details
     */
    public function show(Contest $contest): View
    {
        $contest->load(['entries.note', 'entries.user', 'winners.entry.note', 'winners.user']);

        $userVote = null;
        $userEntry = null;
        $canSubmit = ['can_submit' => false, 'reasons' => []];

        if (auth()->check()) {
            $userVote = $this->contestService->getUserVote($contest, auth()->user());
            $userEntry = $contest->entries()->where('user_id', auth()->id())->first();
            $canSubmit = $this->contestService->canUserSubmitEntry($contest, auth()->user());
        }

        // Get top entries for display
        $topEntries = $contest->getTopEntries(10);

        return view('contests.show', [
            'contest' => $contest,
            'userVote' => $userVote,
            'userEntry' => $userEntry,
            'canSubmit' => $canSubmit,
            'topEntries' => $topEntries,
        ]);
    }

    /**
     * Show submit entry form
     */
    public function showSubmitForm(Contest $contest): View
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $canSubmit = $this->contestService->canUserSubmitEntry($contest, auth()->user());
        if (!$canSubmit['can_submit']) {
            return redirect()->route('contests.show', $contest)
                ->with('error', implode(' ', $canSubmit['reasons']));
        }

        // Get user's notes that can be submitted
        $userNotes = auth()->user()->notes()
            ->where('is_public', true)
            ->where('status', 'active')
            ->whereNotIn('id', function($query) use ($contest) {
                $query->select('note_id')
                    ->from('contest_entries')
                    ->where('contest_id', $contest->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('contests.submit', [
            'contest' => $contest,
            'userNotes' => $userNotes,
        ]);
    }

    /**
     * Submit entry
     */
    public function submitEntry(Request $request, Contest $contest): RedirectResponse
    {
        $request->validate([
            'note_id' => 'required|exists:notes,id',
            'submission_notes' => 'nullable|string|max:1000',
        ]);

        $note = Note::findOrFail($request->note_id);

        try {
            $this->contestService->submitEntry(
                $contest,
                auth()->user(),
                $note,
                $request->submission_notes
            );

            return redirect()->route('contests.show', $contest)
                ->with('success', 'Your entry has been submitted successfully and is pending review.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Vote for an entry
     */
    public function vote(Request $request, Contest $contest): RedirectResponse
    {
        $request->validate([
            'entry_id' => 'required|exists:contest_entries,id',
        ]);

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $entry = \App\Models\ContestEntry::findOrFail($request->entry_id);

        try {
            $this->contestService->voteForEntry($entry, auth()->user());

            return redirect()->route('contests.show', $contest)
                ->with('success', 'Your vote has been recorded!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

