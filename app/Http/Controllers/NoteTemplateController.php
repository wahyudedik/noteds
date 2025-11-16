<?php

namespace App\Http\Controllers;

use App\Models\NoteTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of templates.
     */
    public function index(Request $request): View
    {
        $myTemplates = NoteTemplate::where('user_id', auth()->id())
            ->with('user')
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->latest()
            ->paginate(12, ['*'], 'my');

        $publicTemplates = NoteTemplate::where('is_public', true)
            ->where('user_id', '!=', auth()->id())
            ->with('user')
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->latest()
            ->paginate(12, ['*'], 'public');

        return view('templates.index', compact('myTemplates', 'publicTemplates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(): View
    {
        return view('templates.create');
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content_template' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $template = NoteTemplate::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'content_template' => $validated['content_template'],
            'category' => $validated['category'] ?? null,
            'is_public' => $request->has('is_public') ? (bool)$request->is_public : false,
        ]);

        return redirect()->route('templates.show', $template)
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified template.
     */
    public function show(NoteTemplate $template): View
    {
        // Check access
        if (!$template->is_public && $template->user_id !== auth()->id()) {
            abort(403);
        }

        $template->load('user');

        return view('templates.show', compact('template'));
    }

    /**
     * Use template to create a new note.
     */
    public function use(NoteTemplate $template): RedirectResponse
    {
        // Check access
        if (!$template->is_public && $template->user_id !== auth()->id()) {
            abort(403);
        }

        // Increment usage count
        $template->incrementUsage();

        // Redirect to note creation with template data
        return redirect()->route('notes.create', [
            'template_id' => $template->id,
        ])->with('template', $template);
    }
}
