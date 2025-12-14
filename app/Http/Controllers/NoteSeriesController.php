<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteSeries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteSeriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of series.
     */
    public function index(Request $request): View
    {
        $series = NoteSeries::where('user_id', auth()->id())
            ->with('notes')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('40-shared/series/index', compact('series'));
    }

    /**
     * Show the form for creating a new series.
     */
    public function create(): View
    {
        return view('40-shared/series/create');
    }

    /**
     * Store a newly created series.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $series = NoteSeries::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        // Handle cover image upload if provided
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('series-covers', 'public');
            $series->update(['cover_image' => $path]);
        }

        return redirect()->route('series.show', $series)
            ->with('success', 'Series created successfully.');
    }

    /**
     * Display the specified series.
     */
    public function show(NoteSeries $series): View
    {
        // Ensure user owns this series
        if ($series->user_id !== auth()->id()) {
            abort(403);
        }

        $series->load(['notes.tags', 'notes.reviews', 'user']);

        return view('40-shared/series/show', compact('series'));
    }

    /**
     * Update the specified series.
     */
    public function update(Request $request, NoteSeries $series): RedirectResponse
    {
        // Ensure user owns this series
        if ($series->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $series->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        // Handle cover image upload if provided
        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($series->cover_image) {
                \Storage::disk('public')->delete($series->cover_image);
            }
            $path = $request->file('cover_image')->store('series-covers', 'public');
            $series->update(['cover_image' => $path]);
        }

        return redirect()->route('series.show', $series)
            ->with('success', 'Series updated successfully.');
    }

    /**
     * Remove the specified series.
     */
    public function destroy(NoteSeries $series): RedirectResponse
    {
        // Ensure user owns this series
        if ($series->user_id !== auth()->id()) {
            abort(403);
        }

        // Remove series_id from notes
        Note::where('series_id', $series->id)->update(['series_id' => null]);

        // Delete cover image if exists
        if ($series->cover_image) {
            \Storage::disk('public')->delete($series->cover_image);
        }

        $series->delete();

        return redirect()->route('series.index')
            ->with('success', 'Series deleted successfully.');
    }
}
