<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminActionLog;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $users = User::with('wallet')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            })
            ->when($request->role, function ($query) use ($request) {
                return $query->where('role', $request->role);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $user->load(['wallet', 'notes', 'withdraws', 'transactionsAsBuyer', 'transactionsAsSeller']);
        $actionLogs = AdminActionLog::with('admin')
            ->where('target_user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        return view('admin.users.show', compact('user', 'actionLogs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $roles = ['admin', 'seller', 'buyer'];

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,seller,buyer'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        // Sync role with Spatie Permission
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        if ($message = $this->guardAgainstSelfOrLastAdmin($user)) {
            return back()->with('error', $message);
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $reason = $request->input('reason');
        $oldStatus = [
            'is_active' => $user->is_active,
            'suspended_at' => $user->suspended_at,
        ];

        $user->update([
            'is_active' => false,
            'suspended_at' => null,
        ]);

        $this->logAdminAction($user, 'deactivate', $reason, $oldStatus);

        $this->notificationService->notifyAccountSuspended($user, $reason, Auth::user());

        return back()->with('success', 'User dinonaktifkan.');
    }

    public function activate(User $user): RedirectResponse
    {
        $oldStatus = [
            'is_active' => $user->is_active,
            'suspended_at' => $user->suspended_at,
        ];

        $user->update([
            'is_active' => true,
            'suspended_at' => null,
        ]);

        $this->logAdminAction($user, 'activate', null, $oldStatus);
        $this->notificationService->notifyAccountReactivated($user, Auth::user());

        return back()->with('success', 'User diaktifkan.');
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        if ($message = $this->guardAgainstSelfOrLastAdmin($user)) {
            return back()->with('error', $message);
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $reason = $request->input('reason');
        $oldStatus = [
            'is_active' => $user->is_active,
            'suspended_at' => $user->suspended_at,
        ];

        $user->update([
            'is_active' => false,
            'suspended_at' => now(),
        ]);

        $this->logAdminAction($user, 'suspend', $reason, $oldStatus);
        $this->notificationService->notifyAccountSuspended($user, $reason, Auth::user());

        return back()->with('success', 'User disuspend.');
    }

    public function release(User $user): RedirectResponse
    {
        $oldStatus = [
            'is_active' => $user->is_active,
            'suspended_at' => $user->suspended_at,
        ];

        $user->update([
            'is_active' => true,
            'suspended_at' => null,
        ]);

        $this->logAdminAction($user, 'release_suspend', null, $oldStatus);
        $this->notificationService->notifyAccountReactivated($user, Auth::user());

        return back()->with('success', 'Suspend akun dicabut.');
    }

    private function guardAgainstSelfOrLastAdmin(User $user): ?string
    {
        if ($user->id === Auth::id()) {
            return 'Anda tidak dapat melakukan tindakan ini pada akun Anda sendiri.';
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->where('id', '!=', $user->id)->count() === 0) {
            return 'Tidak dapat menonaktifkan atau mensuspend admin terakhir.';
        }

        return null;
    }

    private function logAdminAction(User $target, string $action, ?string $reason = null, array $previousStatus = []): void
    {
        $admin = Auth::user();

        if (! $admin) {
            return;
        }

        AdminActionLog::create([
            'admin_id' => $admin->id,
            'target_user_id' => $target->id,
            'action' => $action,
            'reason' => $reason,
            'metadata' => [
                'previous_status' => $previousStatus,
                'current_status' => [
                    'is_active' => $target->fresh()->is_active,
                    'suspended_at' => $target->suspended_at,
                ],
            ],
        ]);
    }
}
