<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
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
}
