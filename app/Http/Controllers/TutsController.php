<?php

namespace App\Http\Controllers;

use App\Models\Tutorial;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutsController extends Controller
{
    /**
     * Display a listing of tutorials.
     */
    public function index(Request $request): View
    {
        $tutorials = Tutorial::with('author')
            ->published()
            ->when($request->search, function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            })
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->when($request->featured === '1', function ($query) {
                return $query->where('featured', true);
            })
            ->orderBy('order')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = [
            'design' => 'Desain grafis, UI/UX',
            'web' => 'Web dev & backend',
            'photo' => 'Fotografi & video editing',
            'business' => 'Productivity & creative business',
        ];

        $featuredCount = Tutorial::published()->featured()->count();

        return view('tuts.index', compact('tutorials', 'categories', 'featuredCount'));
    }

    /**
     * Display the specified tutorial.
     */
    public function show(Tutorial $tutorial): View
    {
        // Check if tutorial is published
        if ($tutorial->status !== 'published') {
            abort(404);
        }

        $tutorial->load('author');

        // Increment views count
        $tutorial->incrementViews();

        // Get related tutorials (same category)
        $relatedTutorials = Tutorial::with('author')
            ->published()
            ->where('category', $tutorial->category)
            ->where('id', '!=', $tutorial->id)
            ->latest()
            ->limit(4)
            ->get();

        return view('tuts.show', compact('tutorial', 'relatedTutorials'));
    }
}
