<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class BadgeController extends Controller
{
    public function __construct(
        private AchievementService $achievementService
    ) {
        $this->middleware(['auth', 'verified', 'role:admin']);
    }

    /**
     * List all badges
     */
    public function index(): View
    {
        $badges = Badge::orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('admin.badges.index', [
            'badges' => $badges,
        ]);
    }

    /**
     * Show badge form
     */
    public function create(): View
    {
        return view('admin.badges.create');
    }

    /**
     * Store new badge
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:badges,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'required|string|max:255',
            'category' => 'required|in:milestone,quality,community',
            'criteria_type' => 'nullable|string|max:255',
            'criteria_value' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_custom' => 'boolean',
            'custom_criteria' => 'nullable|array',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // If custom badge, set created_by
        if ($validated['is_custom'] ?? false) {
            $validated['created_by'] = auth()->id();
        }

        Badge::create($validated);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge created successfully.');
    }

    /**
     * Show badge edit form
     */
    public function edit(Badge $badge): View
    {
        return view('admin.badges.edit', [
            'badge' => $badge,
        ]);
    }

    /**
     * Update badge
     */
    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:badges,slug,' . $badge->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'required|string|max:255',
            'category' => 'required|in:milestone,quality,community',
            'criteria_type' => 'nullable|string|max:255',
            'criteria_value' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_custom' => 'boolean',
            'custom_criteria' => 'nullable|array',
        ]);

        // Don't allow changing is_custom for system badges
        if (!$badge->is_custom) {
            unset($validated['is_custom']);
        }

        $badge->update($validated);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge updated successfully.');
    }

    /**
     * Delete badge
     */
    public function destroy(Badge $badge): RedirectResponse
    {
        // Don't allow deleting system badges
        if (!$badge->is_custom) {
            return redirect()->route('admin.badges.index')
                ->with('error', 'Cannot delete system badges. Only custom badges can be deleted.');
        }

        $badge->delete();

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge deleted successfully.');
    }

    /**
     * Show users with a specific badge
     */
    public function showUsers(Badge $badge): View
    {
        $users = $badge->users()->paginate(20);

        return view('admin.badges.users', [
            'badge' => $badge,
            'users' => $users,
        ]);
    }

    /**
     * Manually award badge to user
     */
    public function awardToUser(Request $request, Badge $badge): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $this->achievementService->manuallyAwardBadge(
            $user,
            $badge,
            $validated['notes'] ?? null
        );

        return redirect()->back()
            ->with('success', "Badge '{$badge->name}' has been awarded to {$user->name}.");
    }
}

