<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): View
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Display notes in a category.
     */
    public function show(Category $category, Request $request): View
    {
        $notes = Note::publicOnly()
            ->whereHas('categories', function ($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->with(['tags', 'user', 'reviews'])
            ->when($request->search, function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $category->load('parent', 'children');

        return view('categories.show', compact('category', 'notes'));
    }
}
