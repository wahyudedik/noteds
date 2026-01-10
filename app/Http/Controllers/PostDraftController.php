<?php

namespace App\Http\Controllers;

use App\Models\PostDraft;
use App\Services\DraftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostDraftController extends Controller
{
    public function __construct(
        private DraftService $draftService
    ) {}

    /**
     * Display a listing of drafts.
     */
    public function index(Request $request): Response
    {
        $drafts = PostDraft::where('user_id', $request->user()->id)
            ->orderByDesc('auto_saved_at')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return Inertia::render('Drafts/Index', [
            'drafts' => $drafts,
        ]);
    }

    /**
     * Store a newly created draft.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'purpose_type' => 'nullable|in:idea_business,ask_question,share_experience,find_partner,find_tools,validate_idea',
            'images' => 'nullable|array',
            'link_url' => 'nullable|url',
            'link_preview_title' => 'nullable|string|max:255',
            'link_preview_description' => 'nullable|string',
            'link_preview_image' => 'nullable|url',
            'link_preview_site_name' => 'nullable|string|max:255',
        ]);

        $draft = $this->draftService->autoSave($validated);

        return response()->json([
            'draft' => $draft,
            'message' => 'Draft saved successfully',
        ]);
    }

    /**
     * Update the specified draft.
     */
    public function update(Request $request, PostDraft $draft): RedirectResponse
    {
        $this->authorize('update', $draft);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'purpose_type' => 'nullable|in:idea_business,ask_question,share_experience,find_partner,find_tools,validate_idea',
            'images' => 'nullable|array',
            'link_url' => 'nullable|url',
            'link_preview_title' => 'nullable|string|max:255',
            'link_preview_description' => 'nullable|string',
            'link_preview_image' => 'nullable|url',
            'link_preview_site_name' => 'nullable|string|max:255',
        ]);

        $this->draftService->autoSave($validated, $draft->id);

        return redirect()->route('drafts.index')
            ->with('success', 'Draft updated successfully');
    }

    /**
     * Remove the specified draft.
     */
    public function destroy(PostDraft $draft): RedirectResponse
    {
        $this->authorize('delete', $draft);

        $draft->delete();

        return redirect()->route('drafts.index')
            ->with('success', 'Draft deleted successfully');
    }

    /**
     * Publish a draft as a post.
     */
    public function publish(Request $request, PostDraft $draft): RedirectResponse
    {
        $this->authorize('update', $draft);

        $post = $this->draftService->publish($draft);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Draft published successfully');
    }
}
