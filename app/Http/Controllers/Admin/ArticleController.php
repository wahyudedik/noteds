<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function create()
    {
        $categories = array_values(config('articles.category_mapping', []));
        return Inertia::render('Admin/Articles/Create', [
            'categories' => $categories,
            'defaults' => [
                'source' => 'Noteds Editorial',
                'author' => Auth::user()?->business_name ?? Auth::user()?->name,
                'language' => 'id',
                'published_at' => now(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url|max:2048',
            'source' => 'nullable|string|max:255',
            'image' => 'nullable|url|max:2048',
            'category' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'language' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:50',
        ]);

        $data = $validated;
        $data['source'] = $data['source'] ?? 'Noteds Editorial';
        $data['author'] = $data['author'] ?? (Auth::user()?->business_name ?? Auth::user()?->name);
        $data['language'] = $data['language'] ?? 'id';
        $data['published_at'] = $data['published_at'] ?? now();
        $data['fetched_at'] = now();
        $data['url_hash'] = !empty($data['url']) ? md5($data['url']) : Str::uuid()->toString();

        Article::create($data);

        return redirect()->route('explorer.index')->with('success', 'Article created successfully.');
    }
}
