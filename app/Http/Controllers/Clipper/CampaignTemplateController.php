<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Models\CampaignTemplate;
use App\Services\CampaignTemplateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignTemplateController extends Controller
{
    public function __construct(
        private CampaignTemplateService $templateService
    ) {}

    /**
     * Display a listing of templates.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $query = CampaignTemplate::where('user_id', $user->id);
        
        if ($request->has('public') && $request->boolean('public')) {
            $query = CampaignTemplate::where('is_public', true);
        }

        $templates = $query->latest()->paginate(15);

        return Inertia::render('Clipper/Campaigns/Templates/Index', [
            'templates' => $templates,
            'filters' => $request->only('public'),
        ]);
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(): Response
    {
        return Inertia::render('Clipper/Campaigns/Templates/Create');
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_references' => 'nullable|array',
            'video_references.*.url' => 'required|url',
            'video_references.*.title' => 'nullable|string|max:255',
            'cpm' => 'required|numeric|min:1000',
            'max_budget' => 'required|numeric|min:10000',
            'max_reward_per_clipper' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1|max:365',
            'is_public' => 'boolean',
        ]);

        $template = $this->templateService->createTemplate($request->user(), $validated);

        return redirect()->route('clipper.campaign-templates.show', $template)
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified template.
     */
    public function show(CampaignTemplate $campaignTemplate): Response
    {
        if ($campaignTemplate->user_id !== auth()->id() && !$campaignTemplate->is_public) {
            abort(403);
        }

        return Inertia::render('Clipper/Campaigns/Templates/Show', [
            'template' => $campaignTemplate,
        ]);
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(CampaignTemplate $campaignTemplate): Response
    {
        if ($campaignTemplate->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Clipper/Campaigns/Templates/Edit', [
            'template' => $campaignTemplate,
        ]);
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, CampaignTemplate $campaignTemplate)
    {
        if ($campaignTemplate->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_references' => 'nullable|array',
            'cpm' => 'required|numeric|min:1000',
            'max_budget' => 'required|numeric|min:10000',
            'max_reward_per_clipper' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1|max:365',
            'is_public' => 'boolean',
        ]);

        $campaignTemplate->update($validated);

        return redirect()->route('clipper.campaign-templates.show', $campaignTemplate)
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template.
     */
    public function destroy(CampaignTemplate $campaignTemplate)
    {
        if ($campaignTemplate->user_id !== auth()->id()) {
            abort(403);
        }

        $campaignTemplate->delete();

        return redirect()->route('clipper.campaign-templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Duplicate a template.
     */
    public function duplicate(CampaignTemplate $campaignTemplate)
    {
        if ($campaignTemplate->user_id !== auth()->id() && !$campaignTemplate->is_public) {
            abort(403);
        }

        $duplicate = $this->templateService->duplicateTemplate($campaignTemplate, auth()->user());

        return redirect()->route('clipper.campaign-templates.show', $duplicate)
            ->with('success', 'Template duplicated successfully.');
    }
}
