<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    /**
     * Display a listing of folders.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get root folders (no parent)
        $folders = $user->folders()
            ->withCount('notes')
            ->withCount('children')
            ->orderBy('order')
            ->get();

        return view('folders.index', compact('folders'));
    }

    /**
     * Show the form for creating a new folder.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $folders = $user->allFolders()->get();
        
        return view('folders.create', compact('folders'));
    }

    /**
     * Store a newly created folder.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        // Ensure parent folder belongs to user
        if ($validated['parent_id']) {
            $parent = Folder::findOrFail($validated['parent_id']);
            if ($parent->user_id !== $request->user()->id) {
                return back()->withErrors(['parent_id' => 'Invalid parent folder.']);
            }
        }

        $folder = $request->user()->allFolders()->create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? null,
            'order' => $request->user()->allFolders()->where('parent_id', $validated['parent_id'] ?? null)->max('order') + 1 ?? 0,
        ]);

        return redirect()->route('folders.index')
            ->with('success', 'Folder created successfully.');
    }

    /**
     * Display the specified folder.
     */
    public function show(Request $request, Folder $folder): View
    {
        // Ensure folder belongs to user
        if ($folder->user_id !== $request->user()->id) {
            abort(403);
        }

        $folder->load(['notes', 'children']);

        return view('folders.show', compact('folder'));
    }

    /**
     * Show the form for editing the specified folder.
     */
    public function edit(Request $request, Folder $folder): View
    {
        // Ensure folder belongs to user
        if ($folder->user_id !== $request->user()->id) {
            abort(403);
        }

        $user = $request->user();
        $folders = $user->allFolders()
            ->where('id', '!=', $folder->id)
            ->get();

        return view('folders.edit', compact('folder', 'folders'));
    }

    /**
     * Update the specified folder.
     */
    public function update(Request $request, Folder $folder): RedirectResponse
    {
        // Ensure folder belongs to user
        if ($folder->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        // Prevent moving folder into itself or its descendants
        if ($validated['parent_id']) {
            $parent = Folder::findOrFail($validated['parent_id']);
            if ($parent->user_id !== $request->user()->id) {
                return back()->withErrors(['parent_id' => 'Invalid parent folder.']);
            }

            // Check if trying to move into descendant
            $descendants = $folder->descendants->pluck('id')->toArray();
            if (in_array($validated['parent_id'], $descendants)) {
                return back()->withErrors(['parent_id' => 'Cannot move folder into its own subfolder.']);
            }
        }

        $folder->update($validated);

        return redirect()->route('folders.index')
            ->with('success', 'Folder updated successfully.');
    }

    /**
     * Remove the specified folder.
     */
    public function destroy(Request $request, Folder $folder): RedirectResponse
    {
        // Ensure folder belongs to user
        if ($folder->user_id !== $request->user()->id) {
            abort(403);
        }

        // Move notes to root (no folder) before deleting
        $folder->notes()->update(['folder_id' => null]);

        // Move child folders to parent or root
        foreach ($folder->children as $child) {
            $child->update(['parent_id' => $folder->parent_id]);
        }

        $folder->delete();

        return redirect()->route('folders.index')
            ->with('success', 'Folder deleted successfully.');
    }

    /**
     * Update folder order (for drag & drop sorting).
     */
    public function updateOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'folders' => 'required|array',
            'folders.*.id' => 'required|exists:folders,id',
            'folders.*.order' => 'required|integer',
        ]);

        $user = $request->user();

        foreach ($validated['folders'] as $item) {
            $folder = Folder::findOrFail($item['id']);
            
            // Ensure folder belongs to user
            if ($folder->user_id !== $user->id) {
                continue;
            }

            $folder->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
