<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PriceRuleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tag_id' => 'required|exists:tags,id',
            'min_price' => 'required|numeric|min:0',
        ]);

        $tag = Tag::findOrFail($data['tag_id']);
        $rules = Setting::getCategoryMinPriceList();

        $rules = collect($rules)
            ->reject(fn ($rule) => ($rule['tag_slug'] ?? null) === $tag->slug)
            ->values()
            ->toArray();

        $rules[] = [
            'tag_slug' => $tag->slug,
            'tag_name' => $tag->name,
            'min_price' => (float) $data['min_price'],
        ];

        Setting::setCategoryMinPriceList($rules);

        return back()->with('success', 'Category minimum price saved.');
    }

    public function update(string $tagSlug, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'min_price' => 'required|numeric|min:0',
        ]);

        $rules = Setting::getCategoryMinPriceList();

        $updated = false;
        foreach ($rules as &$rule) {
            if (($rule['tag_slug'] ?? null) === $tagSlug) {
                $rule['min_price'] = (float) $data['min_price'];
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            return back()->with('error', 'Category rule not found.');
        }

        Setting::setCategoryMinPriceList($rules);

        return back()->with('success', 'Category minimum price updated.');
    }

    public function destroy(string $tagSlug): RedirectResponse
    {
        $rules = collect(Setting::getCategoryMinPriceList())
            ->reject(fn ($rule) => ($rule['tag_slug'] ?? null) === $tagSlug)
            ->values()
            ->toArray();

        Setting::setCategoryMinPriceList($rules);

        return back()->with('success', 'Category minimum price removed.');
    }
}


