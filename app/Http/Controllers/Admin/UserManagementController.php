<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ContentReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('business_name', 'like', '%' . $search . '%');
            });
        }

        // Role filter
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Banned filter
        if ($request->has('banned')) {
            if ($request->banned === 'true') {
                $query->where('is_banned', true);
            } else {
                $query->where('is_banned', false);
            }
        }

        $users = $query->withCount(['posts', 'comments', 'products'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'banned']),
        ]);
    }

    /**
     * Display the specified user.
     *
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response
    {
        $user->loadCount(['posts', 'comments', 'products', 'orders']);

        // Get user's recent posts
        $recentPosts = $user->posts()
            ->latest()
            ->limit(10)
            ->get();

        // Get user's recent comments
        $recentComments = $user->comments()
            ->with('post')
            ->latest()
            ->limit(10)
            ->get();

        // Get reports received by this user
        $reportsReceived = ContentReport::where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->with(['user', 'admin'])
            ->latest()
            ->get();

        // Get reports made by this user
        $reportsMade = ContentReport::where('user_id', $user->id)
            ->with('reportable')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'recentPosts' => $recentPosts,
            'recentComments' => $recentComments,
            'reportsReceived' => $reportsReceived,
            'reportsMade' => $reportsMade,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param User $user
     * @return Response
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:user,admin'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_field' => ['nullable', 'string', 'max:255'],
            'is_verified_mentor' => ['nullable', 'boolean'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Ban a user.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ban(Request $request, User $user)
    {
        // Prevent banning admins
        if ($user->isAdmin()) {
            return back()->withErrors([
                'ban' => 'Cannot ban admin users.',
            ]);
        }

        $validated = $request->validate([
            'ban_reason' => ['required', 'string', 'max:1000'],
        ]);

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $validated['ban_reason'],
            'banned_by' => $request->user()->id,
        ]);

        return back()->with('success', 'User banned successfully.');
    }

    /**
     * Unban a user.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unban(Request $request, User $user)
    {
        $user->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null,
            'banned_by' => null,
        ]);

        return back()->with('success', 'User unbanned successfully.');
    }
}

