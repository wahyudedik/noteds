<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\NoteApproval;
use App\Models\NoteCategory;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminNoteController extends Controller
{
    /**
     * Display list of all notes with filters
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorize('manage-notes');

        $query = Note::with('author', 'category');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            $query->where('category_id', $request->category);
        }

        // Search by title
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('title', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%");
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $notes = $query->latest('created_at')->paginate(15);

        $stats = [
            'total' => Note::count(),
            'published' => Note::where('status', 'published')->count(),
            'pending' => Note::where('status', 'pending')->count(),
            'blocked' => Note::where('status', 'blocked')->count(),
        ];

        $categories = NoteCategory::all(['id', 'name']);

        return view('admin.data-management.notes', [
            'notes' => $notes,
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }

    /**
     * Show note details
     *
     * @param Note $note
     * @return View
     */
    public function show(Note $note): View
    {
        $this->authorize('manage-notes');

        return view('admin.data-management.note-detail', [
            'note' => $note->load('author', 'category'),
        ]);
    }

    /**
     * Approve note
     *
     * @param Request $request
     * @param Note $note
     * @return RedirectResponse
     */
    public function approve(Request $request, Note $note): RedirectResponse
    {
        $this->authorize('manage-notes');

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        // Create approval record
        NoteApproval::create([
            'note_id' => $note->id,
            'admin_id' => auth()->id(),
            'status' => 'approved',
            'notes' => $request->notes,
        ]);

        // Update note status
        $note->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        activity('admin')
            ->performedOn($note)
            ->withProperties(['action' => 'approved'])
            ->log('Note approved');

        return redirect()->back()->with('success', 'Catatan berhasil disetujui');
    }

    /**
     * Reject note
     *
     * @param Request $request
     * @param Note $note
     * @return RedirectResponse
     */
    public function reject(Request $request, Note $note): RedirectResponse
    {
        $this->authorize('manage-notes');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Create rejection record
        NoteApproval::create([
            'note_id' => $note->id,
            'admin_id' => auth()->id(),
            'status' => 'rejected',
            'notes' => $request->reason,
        ]);

        // Update note status
        $note->update([
            'status' => 'rejected',
        ]);

        activity('admin')
            ->performedOn($note)
            ->withProperties(['reason' => $request->reason])
            ->log('Note rejected');

        return redirect()->back()->with('success', 'Catatan ditolak');
    }

    /**
     * Block note
     *
     * @param Request $request
     * @param Note $note
     * @return RedirectResponse
     */
    public function block(Request $request, Note $note): RedirectResponse
    {
        $this->authorize('manage-notes');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $note->update([
            'status' => 'blocked',
            'block_reason' => $request->reason,
            'blocked_at' => now(),
        ]);

        activity('admin')
            ->performedOn($note)
            ->withProperties(['reason' => $request->reason])
            ->log('Note blocked');

        return redirect()->back()->with('success', 'Catatan berhasil diblokir');
    }

    /**
     * Unblock note
     *
     * @param Note $note
     * @return RedirectResponse
     */
    public function unblock(Note $note): RedirectResponse
    {
        $this->authorize('manage-notes');

        $note->update([
            'status' => 'published',
            'block_reason' => null,
            'blocked_at' => null,
        ]);

        activity('admin')
            ->performedOn($note)
            ->log('Note unblocked');

        return redirect()->back()->with('success', 'Catatan berhasil dibuka');
    }

    /**
     * Delete note
     *
     * @param Note $note
     * @return RedirectResponse
     */
    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete-notes');

        activity('admin')
            ->performedOn($note)
            ->log('Note deleted');

        $note->delete();

        return redirect()->route('admin.notes.index')->with('success', 'Catatan berhasil dihapus');
    }

    /**
     * Get note statistics
     *
     * @return array
     */
    private function getStats(): array
    {
        return [
            'total' => Note::count(),
            'published' => Note::where('status', 'published')->count(),
            'pending' => Note::where('status', 'pending')->count(),
            'blocked' => Note::where('status', 'blocked')->count(),
            'total_sales' => DB::table('sales')->sum('quantity'),
            'total_revenue' => DB::table('sales')->sum('amount'),
        ];
    }

    /**
     * Feature note (make it featured)
     *
     * @param Note $note
     * @return RedirectResponse
     */
    public function feature(Note $note): RedirectResponse
    {
        $this->authorize('manage-notes');

        $note->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);

        activity('admin')
            ->performedOn($note)
            ->log('Note featured');

        return redirect()->back()->with('success', 'Catatan berhasil ditampilkan di featured');
    }

    /**
     * Remove feature from note
     *
     * @param Note $note
     * @return RedirectResponse
     */
    public function unfeature(Note $note): RedirectResponse
    {
        $this->authorize('manage-notes');

        $note->update([
            'is_featured' => false,
            'featured_at' => null,
        ]);

        activity('admin')
            ->performedOn($note)
            ->log('Note unfeatured');

        return redirect()->back()->with('success', 'Catatan dihapus dari featured');
    }
}
