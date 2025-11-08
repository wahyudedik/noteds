<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaxRuleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'country_code' => 'required|string|max:3',
            'country_name' => 'required|string|max:120',
            'note_category' => 'nullable|string|max:120',
            'tax_percent' => 'required|numeric|min:0|max:100',
            'is_inclusive' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        TaxRule::updateOrCreate(
            [
                'country_code' => strtoupper($data['country_code']),
                'note_category' => $data['note_category'] ?: null,
            ],
            [
                'country_name' => $data['country_name'],
                'tax_percent' => $data['tax_percent'],
                'is_inclusive' => $request->boolean('is_inclusive', true),
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return back()->with('success', 'Tax rule saved successfully.');
    }

    public function update(TaxRule $taxRule, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'country_name' => 'required|string|max:120',
            'tax_percent' => 'required|numeric|min:0|max:100',
            'note_category' => 'nullable|string|max:120',
            'is_inclusive' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $taxRule->update([
            'country_name' => $data['country_name'],
            'tax_percent' => $data['tax_percent'],
            'note_category' => $data['note_category'] ?: null,
            'is_inclusive' => $request->boolean('is_inclusive', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Tax rule updated successfully.');
    }

    public function destroy(TaxRule $taxRule): RedirectResponse
    {
        $taxRule->delete();

        return back()->with('success', 'Tax rule removed.');
    }
}

