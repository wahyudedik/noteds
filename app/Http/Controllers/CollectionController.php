<?php

namespace App\Http\Controllers;

use App\Models\BuyerCollection;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CollectionController extends Controller
{
    /**
     * Display a listing of collections.
     */
    public function index(): View
    {
        $collections = auth()->user()->collections()
            ->orderBy('order')
            ->get();
        
        // Load notes count for each collection using subquery
        $collectionIds = $collections->pluck('id');
        $notesCounts = DB::table('buyer_collection_notes')
            ->whereIn('collection_id', $collectionIds)
            ->select('collection_id', DB::raw('COUNT(*) as count'))
            ->groupBy('collection_id')
            ->pluck('count', 'collection_id');
        
        foreach ($collections as $collection) {
            $collection->notes_count = $notesCounts[$collection->id] ?? 0;
        }

        return view('buyer.collections.index', compact('collections'));
    }

    /**
     * Show the form for creating a new collection.
     */
    public function create(): View
    {
        return view('buyer.collections.create');
    }

    /**
     * Store a newly created collection.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['color'] = $validated['color'] ?? '#3B82F6';
        $validated['order'] = auth()->user()->collections()->max('order') + 1 ?? 0;

        BuyerCollection::create($validated);

        return redirect()->route('collections.index')
            ->with('success', 'Collection created successfully.');
    }

    /**
     * Display the specified collection.
     */
    public function show(BuyerCollection $collection): View
    {
        $this->authorize('view', $collection);

        $collection->load(['notes' => function($query) {
            $query->with('tags', 'user', 'reviews');
        }]);

        // Get purchased notes that are NOT already in this collection
        $user = auth()->user();
        $purchasedNotes = $user->purchasedNotes()
            ->with(['note' => function($query) {
                $query->with('tags', 'user', 'reviews');
            }])
            ->whereHas('note', function($query) {
                $query->where('status', 'active');
            })
            ->get()
            ->map(function($purchasedNote) {
                return $purchasedNote->note;
            })
            ->filter(function($note) use ($collection) {
                // Filter out notes that are already in collection
                return $note && !$collection->notes->contains('id', $note->id);
            })
            ->values();

        return view('buyer.collections.show', compact('collection', 'purchasedNotes'));
    }

    /**
     * Show the form for editing the specified collection.
     */
    public function edit(BuyerCollection $collection): View
    {
        $this->authorize('update', $collection);

        return view('buyer.collections.create', compact('collection'));
    }

    /**
     * Update the specified collection.
     */
    public function update(Request $request, BuyerCollection $collection): RedirectResponse
    {
        $this->authorize('update', $collection);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $collection->update($validated);

        return redirect()->route('collections.index')
            ->with('success', 'Collection updated successfully.');
    }

    /**
     * Remove the specified collection.
     */
    public function destroy(BuyerCollection $collection): RedirectResponse
    {
        $this->authorize('delete', $collection);

        $collection->delete();

        return redirect()->route('collections.index')
            ->with('success', 'Collection deleted successfully.');
    }

    /**
     * Add note to collection.
     */
    public function addNote(Request $request, BuyerCollection $collection): RedirectResponse
    {
        $this->authorize('update', $collection);

        $validated = $request->validate([
            'note_id' => 'required|exists:notes,id',
        ]);

        $user = auth()->user();
        $note = Note::findOrFail($validated['note_id']);

        // Check if user has purchased this note (for buyer collections)
        if ($user->role === 'buyer' && !$user->hasPurchasedNote($validated['note_id'])) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda harus membeli note ini terlebih dahulu sebelum menambahkannya ke collection.'], 403);
            }
            return redirect()->back()->with('error', 'Anda harus membeli note ini terlebih dahulu sebelum menambahkannya ke collection.');
        }

        // Check if note already in collection
        if ($collection->notes()->where('notes.id', $validated['note_id'])->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Note sudah ada di collection ini.'], 400);
            }
            return redirect()->back()->with('error', 'Note sudah ada di collection ini.');
        }

        // Get max order from pivot table
        $maxOrder = DB::table('buyer_collection_notes')
            ->where('collection_id', $collection->id)
            ->max('order') ?? 0;
        
        $collection->notes()->attach($validated['note_id'], ['order' => $maxOrder + 1]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Note added to collection.']);
        }

        return redirect()->back()->with('success', 'Note added to collection.');
    }

    /**
     * Remove note from collection.
     */
    public function removeNote(BuyerCollection $collection, Note $note): RedirectResponse
    {
        $this->authorize('update', $collection);

        $collection->notes()->detach($note->id);

        return redirect()->back()->with('success', 'Note removed from collection.');
    }
}
