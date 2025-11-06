<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of workspaces.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Check if user can access workspaces
        if ($user->role !== 'user_workspaces' && !$user->hasPremium() && !$user->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'Fitur Workspace hanya tersedia untuk Premium users atau Workspace users.');
        }
        
        // Get owned and member workspaces
        $ownedWorkspaces = $user->ownedWorkspaces()->withCount('notes')->get();
        $memberWorkspaces = $user->workspaces()->withCount('notes')->get();

        return view('workspaces.index', compact('ownedWorkspaces', 'memberWorkspaces'));
    }

    /**
     * Show the form for creating a new workspace.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        
        // Check if user can access workspaces
        if ($user->role !== 'user_workspaces' && !$user->hasPremium() && !$user->hasRole('admin')) {
            abort(403, 'Fitur Workspace hanya tersedia untuk Premium users atau Workspace users.');
        }
        
        return view('workspaces.create');
    }

    /**
     * Store a newly created workspace.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Check if user can access workspaces
        if ($user->role !== 'user_workspaces' && !$user->hasPremium() && !$user->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'Fitur Workspace hanya tersedia untuk Premium users atau Workspace users.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:personal,team,organization',
            'description' => 'nullable|string|max:1000',
        ]);

        $workspace = Workspace::create([
            'owner_id' => $user->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        // Add owner as member with admin role
        $workspace->members()->attach($user->id, [
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
    public function show(Workspace $workspace, Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        
        // Check if user can access this workspace
        if ($workspace->owner_id !== $user->id && !$workspace->hasMember($user)) {
            abort(403);
        }

        // Load folders with children recursively (for sidebar tree)
        // Get all folders in workspace, then build tree structure recursively
        $allFolders = \App\Models\Folder::where('workspace_id', $workspace->id)
            ->orderBy('order')
            ->get()
            ->keyBy('id');
        
        // Build tree structure with all children loaded
        foreach ($allFolders as $folder) {
            if ($folder->parent_id && isset($allFolders[$folder->parent_id])) {
                $parent = $allFolders[$folder->parent_id];
                if (!$parent->relationLoaded('children')) {
                    $parent->setRelation('children', collect());
                }
                $parent->children->push($folder);
            }
        }
        
        $rootFolders = $allFolders->whereNull('parent_id')->values();
        
        // Load notes
        $workspace->load([
            'notes' => function($query) use ($workspace) {
                $query->whereNull('folder_id')
                    ->where('workspace_id', $workspace->id)
                    ->latest();
            },
            'members'
        ]);
        
        // Attach root folders to workspace for view
        $workspace->setRelation('folders', $rootFolders);
        
        // Get current folder if specified
        $currentFolder = null;
        if ($request->has('folder')) {
            $currentFolder = \App\Models\Folder::where('id', $request->folder)
                ->where('workspace_id', $workspace->id)
                ->with(['children', 'notes'])
                ->first();
            
            if (!$currentFolder) {
                return redirect()->route('workspaces.show', $workspace);
            }
        }
        
        return view('workspaces.show', compact('workspace', 'currentFolder'));
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
     * List workspace for sale.
     */
    public function sell(Request $request, Workspace $workspace): RedirectResponse
    {
        $user = $request->user();
        
        // Only owner can sell
        if ($workspace->owner_id !== $user->id) {
            abort(403);
        }

        if ($workspace->isSold()) {
            return redirect()->route('workspaces.show', $workspace)
                ->with('error', __('messages.workspace_already_sold'));
        }

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'marketplace_description' => 'nullable|string|max:1000',
        ]);

        $workspace->update([
            'price' => $validated['price'],
            'is_for_sale' => true,
            'marketplace_description' => $validated['marketplace_description'] ?? null,
        ]);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', __('messages.workspace_listed_for_sale'));
    }

    /**
     * Purchase workspace.
     */
    public function purchase(Request $request, Workspace $workspace): RedirectResponse
    {
        $user = $request->user();
        
        if (!$workspace->isForSale()) {
            return redirect()->route('workspaces.show', $workspace)
                ->with('error', __('messages.workspace_not_for_sale'));
        }

        if ($workspace->owner_id === $user->id) {
            return redirect()->route('workspaces.show', $workspace)
                ->with('error', __('messages.cannot_purchase_own_workspace'));
        }

        // Check wallet balance
        $wallet = \App\Models\Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        if ($wallet->balance < $workspace->price) {
            return redirect()->route('workspaces.show', $workspace)
                ->with('error', __('messages.insufficient_balance_to_purchase'))
                ->with('redirect_to_wallet', true);
        }

        // Purchase transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($workspace, $user, $wallet) {
            // Deduct from buyer wallet
            $wallet->balance -= $workspace->price;
            $wallet->save();
            $user->wallet_balance = $wallet->balance;
            $user->save();

            // Add to seller wallet
            $sellerWallet = \App\Models\Wallet::firstOrCreate(['user_id' => $workspace->owner_id], ['balance' => 0]);
            $commission = $workspace->price * 0.20; // 20% commission
            $sellerAmount = $workspace->price - $commission;
            $sellerWallet->balance += $sellerAmount;
            $sellerWallet->save();
            
            $seller = $workspace->owner;
            $seller->wallet_balance = $sellerWallet->balance;
            $seller->save();

            // Create transaction record
            \App\Models\Transaction::create([
                'buyer_id' => $user->id,
                'seller_id' => $workspace->owner_id,
                'note_id' => null,
                'amount' => $workspace->price,
                'commission' => $commission,
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => "Workspace purchase: {$workspace->name}",
            ]);

            // Transfer workspace ownership
            $workspace->update([
                'owner_id' => $user->id,
                'is_for_sale' => false,
                'sold_at' => now(),
                'sold_to_user_id' => $user->id,
            ]);
        });

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', __('messages.workspace_purchased_successfully'));
    }

    /**
     * Show the form for inviting a user to workspace.
     */
    public function invite(Workspace $workspace): View
    {
        $user = request()->user();
        
        // Only owner or admin can invite
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        // Get pending invitations
        $pendingInvitations = $workspace->pendingInvitations()
            ->with('inviter')
            ->latest()
            ->get();

        return view('workspaces.invite', compact('workspace', 'pendingInvitations'));
    }

    /**
     * Store a new workspace invitation.
     */
    public function storeInvite(Request $request, Workspace $workspace): RedirectResponse
    {
        $user = $request->user();
        
        // Only owner or admin can invite
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,member'],
        ]);

        // Check if user already exists
        $existingUser = \App\Models\User::where('email', $validated['email'])->first();
        
        // If user exists and is already a member, don't create invitation
        if ($existingUser && $workspace->hasMember($existingUser)) {
            return redirect()->route('workspaces.invite', $workspace)
                ->with('error', 'User dengan email ini sudah menjadi member workspace.');
        }

        // Check if there's already a pending invitation for this email
        $existingInvitation = \App\Models\WorkspaceInvitation::where('workspace_id', $workspace->id)
            ->where('email', $validated['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return redirect()->route('workspaces.invite', $workspace)
                ->with('error', 'Invitation untuk email ini sudah ada dan masih aktif.');
        }

        // Create invitation
        $invitation = \App\Models\WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'invited_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);

        // Generate invitation link
        $inviteLink = route('register', ['invite' => $invitation->token]);

        // Send email notification with invite link (if email is configured)
        try {
            \Illuminate\Support\Facades\Mail::to($validated['email'])->send(
                new \App\Mail\WorkspaceInvitationMail($invitation, $inviteLink)
            );
        } catch (\Exception $e) {
            // Log error but don't fail the invitation creation
            logger()->error('Failed to send workspace invitation email', [
                'invitation_id' => $invitation->id,
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('workspaces.invite', $workspace)
            ->with('success', 'Invitation berhasil dikirim ke ' . $validated['email'] . '!')
            ->with('invite_link', $inviteLink)
            ->with('invited_email', $validated['email']);
    }

    /**
     * Cancel/Delete a workspace invitation.
     */
    public function cancelInvite(Workspace $workspace, \App\Models\WorkspaceInvitation $invitation): RedirectResponse
    {
        $user = request()->user();
        
        // Only owner or admin can cancel invitation
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        // Check if invitation belongs to this workspace
        if ($invitation->workspace_id !== $workspace->id) {
            abort(403);
        }

        // Only cancel if not accepted
        if (!$invitation->isAccepted()) {
            $invitation->delete();
        }

        return redirect()->route('workspaces.invite', $workspace)
            ->with('success', 'Invitation berhasil dibatalkan.');
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
