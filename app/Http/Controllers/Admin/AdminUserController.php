<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Display list of all users with filters
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorize('manage-users');

        $query = User::query();

        // Filter by role
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status === 'unverified') {
                $query->where('is_verified', false);
            } elseif ($request->status === 'banned') {
                $query->where('is_banned', true);
            }
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $users = $query->latest('created_at')->paginate(15);

        $stats = [
            'total' => User::count(),
            'verified' => User::where('is_verified', true)->count(),
            'unverified' => User::where('is_verified', false)->count(),
            'banned' => User::where('is_banned', true)->count(),
        ];

        return view('admin.data-management.users', [
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Show user details
     *
     * @param User $user
     * @return View
     */
    public function show(User $user): View
    {
        $this->authorize('manage-users');

        return view('admin.data-management.user-detail', [
            'user' => $user,
        ]);
    }

    /**
     * Update user verification status
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function verify(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        $user->update([
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        activity('admin')
            ->performedOn($user)
            ->withProperties(['action' => 'verified'])
            ->log('User verified');

        return redirect()->back()->with('success', 'User berhasil diverifikasi');
    }

    /**
     * Reject user verification
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function rejectVerification(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user->update([
            'is_verified' => false,
        ]);

        activity('admin')
            ->performedOn($user)
            ->withProperties(['reason' => $request->reason])
            ->log('User verification rejected');

        return redirect()->back()->with('success', 'Verifikasi pengguna ditolak');
    }

    /**
     * Ban user
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function ban(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        $request->validate([
            'reason' => 'required|string|max:500',
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $banned_until = $request->days
            ? Carbon::now()->addDays($request->days)
            : null;

        $user->update([
            'is_banned' => true,
            'ban_reason' => $request->reason,
            'banned_until' => $banned_until,
        ]);

        activity('admin')
            ->performedOn($user)
            ->withProperties([
                'reason' => $request->reason,
                'days' => $request->days,
            ])
            ->log('User banned');

        return redirect()->back()->with('success', 'Pengguna berhasil di-ban');
    }

    /**
     * Unban user
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function unban(User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        $user->update([
            'is_banned' => false,
            'ban_reason' => null,
            'banned_until' => null,
        ]);

        activity('admin')
            ->performedOn($user)
            ->log('User unbanned');

        return redirect()->back()->with('success', 'Pengguna berhasil di-unban');
    }

    /**
     * Delete user
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete-users');

        activity('admin')
            ->performedOn($user)
            ->log('User deleted');

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus');
    }

    /**
     * Update user kyc verification
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function verifyKyc(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:500',
        ]);

        $user->update([
            'kyc_verified' => $request->status === 'approved',
            'kyc_notes' => $request->notes,
            'kyc_verified_at' => $request->status === 'approved' ? now() : null,
        ]);

        activity('admin')
            ->performedOn($user)
            ->withProperties([
                'kyc_status' => $request->status,
                'notes' => $request->notes,
            ])
            ->log('User KYC verified');

        return redirect()->back()->with('success', 'KYC pengguna berhasil diverifikasi');
    }

    /**
     * Promote user to seller
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function promoteToSeller(User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        if ($user->role !== 'buyer') {
            return redirect()->back()->with('error', 'Hanya pembeli yang dapat dipromosikan');
        }

        $user->update([
            'role' => 'seller',
        ]);

        activity('admin')
            ->performedOn($user)
            ->log('User promoted to seller');

        return redirect()->back()->with('success', 'Pengguna berhasil dipromosikan menjadi penjual');
    }

    /**
     * Demote seller to buyer
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function demoteTobuyer(User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        if ($user->role !== 'seller') {
            return redirect()->back()->with('error', 'Hanya penjual yang dapat diturunkan');
        }

        $user->update([
            'role' => 'buyer',
        ]);

        activity('admin')
            ->performedOn($user)
            ->log('User demoted to buyer');

        return redirect()->back()->with('success', 'Pengguna berhasil diturunkan menjadi pembeli');
    }
}
