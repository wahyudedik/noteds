<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentationController extends Controller
{
    /**
     * Display a listing of published documentations grouped by category.
     */
    public function index()
    {
        $documentations = Documentation::published()
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('category');

        return Inertia::render('Documentations/Index', [
            'documentations' => $documentations,
        ]);
    }

    /**
     * Display the specified documentation.
     */
    public function show(string $slug)
    {
        $documentation = Documentation::published()
            ->where('slug', $slug)
            ->firstOrFail();
        
        // Increment views
        $documentation->incrementViews();

        // Get related documentations by category
        $relatedDocumentations = Documentation::published()
            ->where('category', $documentation->category)
            ->where('id', '!=', $documentation->id)
            ->orderBy('order')
            ->limit(5)
            ->get();

        return Inertia::render('Documentations/Show', [
            'documentation' => $documentation,
            'relatedDocumentations' => $relatedDocumentations,
        ]);
    }

    /**
     * Search documentations.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $documentations = Documentation::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('content', 'like', '%' . $query . '%')
                  ->orWhere('excerpt', 'like', '%' . $query . '%');
            })
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Documentations/Index', [
            'documentations' => $documentations->groupBy('category'),
            'searchQuery' => $query,
        ]);
    }
}
