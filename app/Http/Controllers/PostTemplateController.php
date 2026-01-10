<?php

namespace App\Http\Controllers;

use App\Models\PostTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostTemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $query = PostTemplate::with('user')
            ->where(function ($q) use ($request) {
                $q->where('is_public', true)
                  ->orWhere('user_id', $request->user()->id);
            });

        if ($request->has('purpose_type') && $request->purpose_type !== 'all') {
            $query->where('purpose_type', $request->purpose_type);
        }

        $templates = $query->orderByDesc('usage_count')
            ->orderByDesc('created_at')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($templates);
        }

        return Inertia::render('PostTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purpose_type' => 'nullable|in:idea_business,ask_question,share_experience,find_partner,find_tools,validate_idea',
            'title_template' => 'nullable|string|max:255',
            'content_template' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $template = PostTemplate::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return redirect()->route('post-templates.index')
            ->with('success', 'Template created successfully');
    }

    /**
     * Display the specified template.
     */
    public function show(PostTemplate $postTemplate): JsonResponse
    {
        return response()->json($postTemplate);
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, PostTemplate $postTemplate): RedirectResponse
    {
        $this->authorize('update', $postTemplate);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purpose_type' => 'nullable|in:idea_business,ask_question,share_experience,find_partner,find_tools,validate_idea',
            'title_template' => 'nullable|string|max:255',
            'content_template' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $postTemplate->update($validated);

        return redirect()->route('post-templates.index')
            ->with('success', 'Template updated successfully');
    }

    /**
     * Remove the specified template.
     */
    public function destroy(PostTemplate $postTemplate): RedirectResponse
    {
        $this->authorize('delete', $postTemplate);

        $postTemplate->delete();

        return redirect()->route('post-templates.index')
            ->with('success', 'Template deleted successfully');
    }
}
