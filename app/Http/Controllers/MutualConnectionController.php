<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FollowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MutualConnectionController extends Controller
{
    public function __construct(
        private FollowService $followService
    ) {}

    /**
     * Get mutual connections between current user and target user.
     */
    public function index(Request $request, User $user): Response|JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            abort(400, 'Cannot get mutual connections with yourself.');
        }

        $mutualConnections = $this->followService->getMutualConnections($currentUser, $user);

        // Get paginated results
        $perPage = (int) $request->query('per_page', 20);
        $page = (int) $request->query('page', 1);

        $total = $mutualConnections->count();
        $offset = ($page - 1) * $perPage;
        $paginated = $mutualConnections->slice($offset, $perPage)->values();

        $formattedConnections = $paginated->map(function ($connection) {
            return [
                'id' => $connection->id,
                'name' => $connection->business_name ?? $connection->name,
                'business_name' => $connection->business_name,
                'avatar_url' => $connection->avatar_url,
                'business_field' => $connection->business_field,
                'is_verified_mentor' => $connection->is_verified_mentor ?? false,
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'mutual_connections' => $formattedConnections,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
            ]);
        }

        return Inertia::render('Profile/MutualConnections', [
            'targetUser' => $user->only(['id', 'name', 'business_name', 'avatar_url']),
            'mutual_connections' => $formattedConnections,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ]);
    }
}
