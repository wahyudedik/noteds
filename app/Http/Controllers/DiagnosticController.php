<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class DiagnosticController extends Controller
{
    public function checkAuth(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Not authenticated']);
        }

        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'column_role' => $user->role,
            'spatie_roles' => $user->getRoleNames()->toArray(),
            'has_seller_role' => $user->hasRole('seller'),
            'has_buyer_role' => $user->hasRole('buyer'),
            'has_admin_role' => $user->hasRole('admin'),
            'verification_status' => $user->verification_status,
        ]);
    }
}
