<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('premium'); // Workspaces are a premium feature
    }

    /**
     * Display a listing of workspaces.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get owned and member workspaces
        $ownedWorkspaces = $user->ownedWorkspaces()->withCount('notes')->get();
        $memberWorkspaces = $user->workspaces()->withCount('notes')->get();

        return view('workspaces.index', compact('ownedWorkspaces', 'memberWorkspaces'));
    }

    /**
     * Show the form for creating a new workspace.
     */
    public function create(): View
    {
        return view('workspaces.create');
    }

    /**
     * Store a newly created workspace.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:personal,team,organization',
            'description' => 'nullable|string|max:1000',
        ]);

        $workspace = Workspace::create([
            'owner_id' => $request->user()->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        // Add owner as member with admin role
        $workspace->members()->attach($request->user()->id, [
            'role' => 'admin',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace created successfully.');
    }

    /**
     * Display the specified workspace.
     */
    public function show(Workspace $workspace): View
    {
        $user = request()->user();
        
        // Check if user can access this workspace
        if ($workspace->owner_id !== $user->id && !$workspace->hasMember($user)) {
            abort(403);
        }

        $workspace->load(['notes', 'members', 'folders']);
        
        return view('workspaces.show', compact('workspace'));
    }

    /**
     * Show the form for editing the specified workspace.
     */
    public function edit(Workspace $workspace): View
    {
        $user = request()->user();
        
        // Only owner or admin can edit
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        return view('workspaces.edit', compact('workspace'));
    }

    /**
     * Update the specified workspace.
     */
    public function update(Request $request, Workspace $workspace): RedirectResponse
    {
        $user = $request->user();
        
        // Only owner or admin can update
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:personal,team,organization',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $workspace->update($validated);

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace updated successfully.');
    }

    /**
     * Remove the specified workspace.
     */
    public function destroy(Workspace $workspace): RedirectResponse
    {
        $user = request()->user();
        
        // Only owner can delete
        if ($workspace->owner_id !== $user->id) {
            abort(403);
        }

        // Move notes to personal (no workspace)
        $workspace->notes()->update(['workspace_id' => null]);
        
        // Move folders to personal
        $workspace->folders()->update(['workspace_id' => null]);

        $workspace->delete();

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace deleted successfully.');
    }
}
