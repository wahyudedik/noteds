<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StreamingProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StreamingProviderController extends Controller
{
    public function index()
    {
        $providers = StreamingProvider::orderBy('name')->get();
        return Inertia::render('Admin/StreamingProviders', [
            'providers' => $providers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:custom_hls,aws_ivs,livepeer',
            'config' => 'nullable|array',
            'active' => 'boolean',
        ]);
        StreamingProvider::create($validated);
        return back()->with('success', 'Provider created');
    }

    public function update(Request $request, StreamingProvider $streamingProvider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:custom_hls,aws_ivs,livepeer',
            'config' => 'nullable|array',
            'active' => 'boolean',
        ]);
        $streamingProvider->update($validated);
        return back()->with('success', 'Provider updated');
    }

    public function destroy(StreamingProvider $streamingProvider)
    {
        $streamingProvider->delete();
        return back()->with('success', 'Provider deleted');
    }
}
