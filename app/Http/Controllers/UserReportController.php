<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    /**
     * Report a user account.
     */
    public function store(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $reporter = $request->user();

        if ($user->id === $reporter->id) {
            return $this->respondWithError($request, 'You cannot report your own account.');
        }

        $existingReport = UserReport::where('reported_user_id', $user->id)
            ->where('user_id', $reporter->id)
            ->first();

        if ($existingReport) {
            return $this->respondWithError($request, 'You have already reported this account.');
        }

        $validated = $request->validate([
            'reason' => 'required|in:spam,harassment,inappropriate,fraud,impersonation,other',
            'description' => 'nullable|string|max:1000',
        ]);

        UserReport::create([
            'reported_user_id' => $user->id,
            'user_id' => $reporter->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        return $this->respondWithSuccess($request, 'Account reported successfully. Our team will review it shortly.');
    }

    protected function respondWithError(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->with('error', $message);
    }

    protected function respondWithSuccess(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}


