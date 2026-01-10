<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserCategory;
use App\Services\CategoryInferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserCategoryController extends Controller
{
    public function __construct(
        private CategoryInferenceService $categoryInferenceService
    ) {}

    /**
     * Get user categories.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $categories = $user->categories()
            ->withPivot('source', 'confidence')
            ->orderByPivot('source', 'desc') // Manual first
            ->orderByPivot('confidence', 'desc')
            ->get();

        return response()->json([
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'source' => $category->pivot->source,
                    'confidence' => $category->pivot->confidence,
                ];
            }),
        ]);
    }

    /**
     * Add category to user (manual).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $user = $request->user();
        $category = Category::findOrFail($request->category_id);

        // Check if already exists
        $exists = UserCategory::where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->exists();

        if ($exists) {
            // Update to manual if it was inferred
            UserCategory::where('user_id', $user->id)
                ->where('category_id', $category->id)
                ->update([
                    'source' => 'manual',
                    'confidence' => null, // Manual categories don't need confidence
                ]);
        } else {
            // Create new manual category
            UserCategory::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'source' => 'manual',
                'confidence' => null,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category added successfully.',
                'category' => $category,
            ]);
        }

        return back()->with('success', 'Category added successfully.');
    }

    /**
     * Remove category from user.
     */
    public function destroy(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Only allow removing manual categories
        UserCategory::where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->where('source', 'manual')
            ->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category removed successfully.',
            ]);
        }

        return back()->with('success', 'Category removed successfully.');
    }

    /**
     * Sync multiple categories (manual only).
     */
    public function sync(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            // Remove all manual categories
            UserCategory::where('user_id', $user->id)
                ->where('source', 'manual')
                ->delete();

            // Add new manual categories
            foreach ($request->category_ids as $categoryId) {
                // Check if inferred, if yes update to manual, otherwise create
                $existing = UserCategory::where('user_id', $user->id)
                    ->where('category_id', $categoryId)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'source' => 'manual',
                        'confidence' => null,
                    ]);
                } else {
                    UserCategory::create([
                        'user_id' => $user->id,
                        'category_id' => $categoryId,
                        'source' => 'manual',
                        'confidence' => null,
                    ]);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Categories synced successfully.',
            ]);
        }

        return back()->with('success', 'Categories synced successfully.');
    }

    /**
     * Refresh inferred categories.
     */
    public function refresh(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $this->categoryInferenceService->updateUserCategories($user);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Categories refreshed successfully.',
            ]);
        }

        return back()->with('success', 'Categories refreshed successfully.');
    }
}
