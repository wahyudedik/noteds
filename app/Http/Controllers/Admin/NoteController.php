<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        $notes = Note::with(['user', 'tags'])
            ->when($request->search, function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            })
            ->when($request->is_public !== null, function ($query) use ($request) {
                return $query->where('is_public', $request->is_public);
            })
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->sale_mode, function ($query) use ($request) {
                return $query->where('sale_mode', $request->sale_mode);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.notes.index', compact('notes'));
    }

    /**
     * Approve monetization for a free note.
     */
    public function approveMonetization(Request $request, Note $note): RedirectResponse
    {
        if ($note->price > 0) {
            return redirect()->route('admin.notes.index')
                ->with('error', 'Hanya note gratis yang bisa di-approve untuk monetization.');
        }

        if ($note->monetization_approved) {
            return redirect()->route('admin.notes.index')
                ->with('error', 'Monetization untuk note ini sudah di-approve sebelumnya.');
        }

        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $note->update([
            'monetization_approved' => true,
            'monetization_approved_by' => auth()->id(),
            'monetization_approved_at' => now(),
        ]);

        return redirect()->route('admin.notes.index')
            ->with('success', 'Monetization untuk note "' . $note->title . '" berhasil di-approve.');
    }

    /**
     * Reject monetization for a free note.
     */
    public function rejectMonetization(Request $request, Note $note): RedirectResponse
    {
        if ($note->price > 0) {
            return redirect()->route('admin.notes.index')
                ->with('error', 'Hanya note gratis yang bisa di-reject untuk monetization.');
        }

        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $note->update([
            'monetization_approved' => false,
            'monetization_auto_approved' => false,
            'monetization_approved_by' => null,
            'monetization_approved_at' => null,
        ]);

        return redirect()->route('admin.notes.index')
            ->with('success', 'Monetization untuk note "' . $note->title . '" telah di-reject.');
    }
}
