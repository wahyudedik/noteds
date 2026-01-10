<?php

namespace App\Http\Controllers;

use App\Models\BookmarkCollection;
use App\Models\BookmarkCollectionShare;
use App\Models\User;
use App\Notifications\CollectionInvitationNotification;
use App\Notifications\CollectionSharedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkCollectionShareController extends Controller
{
    /**
     * Toggle public/private collection.
     */
    public function togglePublic(Request $request, BookmarkCollection $collection): JsonResponse
    {
        $this->authorize('share', $collection);

        $collection->is_public = !$collection->is_public;

        if ($collection->is_public && !$collection->public_slug) {
            $collection->public_slug = $collection->generatePublicSlug();
        } elseif (!$collection->is_public) {
            $collection->public_slug = null;
        }

        $collection->save();

        // Notify collection owner
        $collection->user->notify(new CollectionSharedNotification($collection, $collection->is_public));

        return response()->json([
            'message' => $collection->is_public ? 'Collection is now public.' : 'Collection is now private.',
            'is_public' => $collection->is_public,
            'public_url' => $collection->public_url,
        ]);
    }

    /**
     * Generate/regenerate public link.
     */
    public function generatePublicLink(Request $request, BookmarkCollection $collection): JsonResponse
    {
        $this->authorize('share', $collection);

        $collection->public_slug = $collection->generatePublicSlug();
        $collection->is_public = true;
        $collection->save();

        // Notify collection owner
        $collection->user->notify(new CollectionSharedNotification($collection, true));

        return response()->json([
            'message' => 'Public link generated successfully.',
            'public_url' => $collection->public_url,
        ]);
    }

    /**
     * Invite user to collection.
     */
    public function invite(Request $request, BookmarkCollection $collection): JsonResponse
    {
        $this->authorize('share', $collection);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|in:view,edit',
        ]);

        $user = User::findOrFail($request->user_id);

        // Check if already shared
        $existing = BookmarkCollectionShare::where('collection_id', $collection->id)
            ->where('shared_with_user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'User already has access to this collection.',
            ], 400);
        }

        $share = BookmarkCollectionShare::create([
            'collection_id' => $collection->id,
            'shared_with_user_id' => $user->id,
            'shared_by_user_id' => $request->user()->id,
            'permission' => $request->permission,
        ]);

        $user->notify(new CollectionInvitationNotification($collection, $request->user()));

        return response()->json([
            'message' => 'Invitation sent successfully.',
            'share' => $share,
        ]);
    }

    /**
     * Accept collection invitation.
     */
    public function accept(Request $request, BookmarkCollection $collection): JsonResponse
    {
        $share = BookmarkCollectionShare::where('collection_id', $collection->id)
            ->where('shared_with_user_id', $request->user()->id)
            ->firstOrFail();

        $share->accept();

        return response()->json([
            'message' => 'Invitation accepted successfully.',
        ]);
    }

    /**
     * Reject collection invitation.
     */
    public function reject(Request $request, BookmarkCollection $collection): JsonResponse
    {
        $share = BookmarkCollectionShare::where('collection_id', $collection->id)
            ->where('shared_with_user_id', $request->user()->id)
            ->firstOrFail();

        $share->delete();

        return response()->json([
            'message' => 'Invitation rejected successfully.',
        ]);
    }

    /**
     * Revoke access (owner only).
     */
    public function revoke(Request $request, BookmarkCollection $collection, User $user): JsonResponse
    {
        $this->authorize('share', $collection);

        BookmarkCollectionShare::where('collection_id', $collection->id)
            ->where('shared_with_user_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'Access revoked successfully.',
        ]);
    }

    /**
     * Update user permission.
     */
    public function updatePermission(Request $request, BookmarkCollection $collection, User $user): JsonResponse
    {
        $this->authorize('share', $collection);

        $request->validate([
            'permission' => 'required|in:view,edit',
        ]);

        $share = BookmarkCollectionShare::where('collection_id', $collection->id)
            ->where('shared_with_user_id', $user->id)
            ->firstOrFail();

        $share->update(['permission' => $request->permission]);

        return response()->json([
            'message' => 'Permission updated successfully.',
        ]);
    }

    /**
     * Get collections shared with user.
     */
    public function sharedWithMe(Request $request): Response
    {
        $user = $request->user();

        $shares = BookmarkCollectionShare::where('shared_with_user_id', $user->id)
            ->with(['collection.user', 'sharedBy'])
            ->latest()
            ->get();

        return Inertia::render('Bookmarks/Shared', [
            'shares' => $shares,
        ]);
    }
}
