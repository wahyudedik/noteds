<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of all workspaces.
     */
    public function index(Request $request): View
    {
        $query = Workspace::with(['owner', 'members', 'folders'])
            ->withCount(['notes', 'members']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        $workspaces = $query->latest()->paginate(20)->withQueryString();

        return view('admin.workspaces.index', compact('workspaces'));
    }

    /**
     * Display the specified workspace.
     */
    public function show(Workspace $workspace): View
    {
        $workspace->load([
            'owner',
            'members', // members() already returns User models directly
            'memberRecords.user', // Use memberRecords to get WorkspaceMember with user relationship
            'folders',
            'notes' => function($query) {
                $query->latest()->limit(10);
            }
        ]);

        $stats = [
            'total_notes' => $workspace->notes()->count(),
            'total_members' => $workspace->members()->count(),
            'total_folders' => $workspace->folders()->count(),
            'public_notes' => $workspace->notes()->where('is_public', true)->count(),
        ];

        return view('admin.workspaces.show', compact('workspace', 'stats'));
    }
}

